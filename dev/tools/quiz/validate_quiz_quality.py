#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script de validation de la qualité pédagogique des quiz
Détecte les questions télégraphiques et corrections insuffisantes

Usage:
    python validate_quiz_quality.py --quiz-dir src/data/quiz --answers-dir src/data/quiz_answers
    python validate_quiz_quality.py --quiz-dir src/data/quiz --answers-dir src/data/quiz_answers --report dev/reports/quiz_quality_report.md
"""

import json
import argparse
import re
from pathlib import Path
from typing import Dict, List, Tuple
from dataclasses import dataclass, field


@dataclass
class QualityIssue:
    """Représente un problème de qualité détecté"""
    quiz_id: int
    level: str
    subject: str
    severity: str  # 'high', 'medium', 'low'
    issue_type: str
    description: str
    question_id: int = None
    example: str = None


@dataclass
class QualityReport:
    """Rapport de qualité global"""
    total_quiz: int = 0
    total_issues: int = 0
    issues_by_severity: Dict[str, int] = field(default_factory=lambda: {'high': 0, 'medium': 0, 'low': 0})
    issues: List[QualityIssue] = field(default_factory=list)

    def add_issue(self, issue: QualityIssue):
        """Ajoute un problème au rapport"""
        self.issues.append(issue)
        self.total_issues += 1
        self.issues_by_severity[issue.severity] += 1


class QuizQualityValidator:
    """Validateur de qualité pédagogique des quiz"""

    # Patterns de détection de problèmes
    TELEGRAPHIC_PATTERNS = [
        r'\.{3,}',  # Points de suspension (ex: "solution...?")
        r'=\s*\.{2,}\?',  # Égal + points (ex: "genre = ...?")
        r'≈\s*\w+\s*\.{2,}',  # Approximation (ex: "Lit ≈ catharsie...?")
        r'^[^a-zA-Z]{0,5}\w+\s*[=≈]\s*\.{2,}',  # Formulation raccourcie
    ]

    MIN_QUESTION_LENGTH = 25  # Caractères minimum pour une question complète
    MIN_CORRECTION_LENGTH = 80  # Caractères minimum pour une correction pédagogique
    IDEAL_CORRECTION_LENGTH = 150  # Longueur idéale pour une bonne explication

    def __init__(self, quiz_dir: Path, answers_dir: Path):
        self.quiz_dir = quiz_dir
        self.answers_dir = answers_dir
        self.report = QualityReport()

    def validate_all(self) -> QualityReport:
        """Valide tous les quiz du répertoire"""
        quiz_files = sorted(self.quiz_dir.glob("*.json"))

        for quiz_file in quiz_files:
            quiz_id = int(quiz_file.stem)
            self.validate_quiz(quiz_id)

        self.report.total_quiz = len(quiz_files)
        return self.report

    def validate_quiz(self, quiz_id: int):
        """Valide un quiz spécifique"""
        quiz_path = self.quiz_dir / f"{quiz_id}.json"
        answers_path = self.answers_dir / f"{quiz_id}.json"

        if not quiz_path.exists():
            return

        try:
            with open(quiz_path, 'r', encoding='utf-8') as f:
                quiz_data = json.load(f)

            # Extraction métadonnées
            level = quiz_data.get('contents', {}).get('level', 'unknown')
            subject = quiz_data.get('contents', {}).get('subject', 'unknown')
            questions = quiz_data.get('quiz', {}).get('questions', [])
            notions = quiz_data.get('exercisenotion', [])

            # Validation des questions
            for question in questions:
                self._validate_question(quiz_id, level, subject, question)

            # Validation des notions
            self._validate_notions(quiz_id, level, subject, notions)

            # Validation des corrections (si fichier answers existe)
            if answers_path.exists():
                with open(answers_path, 'r', encoding='utf-8') as f:
                    answers_data = json.load(f)
                    answers = answers_data.get('quiz', {}).get('answers', [])

                    for answer in answers:
                        self._validate_correction(quiz_id, level, subject, answer)

        except Exception as e:
            issue = QualityIssue(
                quiz_id=quiz_id,
                level='unknown',
                subject='unknown',
                severity='high',
                issue_type='parsing_error',
                description=f"Erreur de lecture du fichier: {str(e)}"
            )
            self.report.add_issue(issue)

    def _validate_question(self, quiz_id: int, level: str, subject: str, question: Dict):
        """Valide la qualité d'une question"""
        question_text = question.get('question', '')
        question_id = question.get('id')

        # Détection de formulations télégraphiques
        for pattern in self.TELEGRAPHIC_PATTERNS:
            if re.search(pattern, question_text):
                issue = QualityIssue(
                    quiz_id=quiz_id,
                    level=level,
                    subject=subject,
                    severity='high',
                    issue_type='telegraphic_question',
                    description="Question formulée de manière télégraphique (abréviations, points de suspension)",
                    question_id=question_id,
                    example=question_text[:80]
                )
                self.report.add_issue(issue)
                break

        # Détection de questions trop courtes
        if len(question_text) < self.MIN_QUESTION_LENGTH:
            issue = QualityIssue(
                quiz_id=quiz_id,
                level=level,
                subject=subject,
                severity='medium',
                issue_type='short_question',
                description=f"Question trop courte ({len(question_text)} caractères, min recommandé: {self.MIN_QUESTION_LENGTH})",
                question_id=question_id,
                example=question_text
            )
            self.report.add_issue(issue)

        # Détection de symboles mathématiques non verbalisés
        math_symbols = ['²', '³', '≈', '≠', '≤', '≥', '×', '÷']
        if any(symbol in question_text for symbol in math_symbols):
            issue = QualityIssue(
                quiz_id=quiz_id,
                level=level,
                subject=subject,
                severity='low',
                issue_type='math_symbols',
                description="Question contient des symboles mathématiques (préférer la verbalisation)",
                question_id=question_id,
                example=question_text[:80]
            )
            self.report.add_issue(issue)

    def _validate_correction(self, quiz_id: int, level: str, subject: str, answer: Dict):
        """Valide la qualité d'une correction"""
        correction = answer.get('correction', '')
        question_id = answer.get('question_id')

        # Détection de corrections trop courtes
        if len(correction) < self.MIN_CORRECTION_LENGTH:
            issue = QualityIssue(
                quiz_id=quiz_id,
                level=level,
                subject=subject,
                severity='high',
                issue_type='short_correction',
                description=f"Correction trop brève ({len(correction)} caractères, min recommandé: {self.MIN_CORRECTION_LENGTH})",
                question_id=question_id,
                example=correction[:60]
            )
            self.report.add_issue(issue)

        # Détection de corrections sans explication
        if correction and len(correction.split('.')) < 2:
            issue = QualityIssue(
                quiz_id=quiz_id,
                level=level,
                subject=subject,
                severity='medium',
                issue_type='unexplained_correction',
                description="Correction sans explication pédagogique (une seule phrase)",
                question_id=question_id,
                example=correction[:60]
            )
            self.report.add_issue(issue)

        # Validation du ton (détection de formulations négatives)
        negative_patterns = ['faux', 'erreur', 'incorrect', 'mauvais']
        if any(word in correction.lower()[:50] for word in negative_patterns) and 'FAUX' not in correction[:10]:
            issue = QualityIssue(
                quiz_id=quiz_id,
                level=level,
                subject=subject,
                severity='low',
                issue_type='negative_tone',
                description="Correction commence par une formulation négative",
                question_id=question_id,
                example=correction[:60]
            )
            self.report.add_issue(issue)

    def _validate_notions(self, quiz_id: int, level: str, subject: str, notions: List[Dict]):
        """Valide la documentation des notions"""
        if not notions:
            issue = QualityIssue(
                quiz_id=quiz_id,
                level=level,
                subject=subject,
                severity='medium',
                issue_type='missing_notions',
                description="Aucune notion documentée (bloc exercisenotion vide)"
            )
            self.report.add_issue(issue)
            return

        # Vérification que les notions ont des descriptions
        for notion in notions:
            if not notion.get('description'):
                issue = QualityIssue(
                    quiz_id=quiz_id,
                    level=level,
                    subject=subject,
                    severity='low',
                    issue_type='undescribed_notion',
                    description=f"Notion sans description: {notion.get('notion', 'unknown')}"
                )
                self.report.add_issue(issue)

    def generate_markdown_report(self, output_path: Path = None):
        """Génère un rapport Markdown"""
        output = []
        output.append("# Rapport de Qualité Pédagogique des Quiz\n")
        output.append(f"**Date génération** : {Path(__file__).stem}\n")
        output.append("---\n")

        # Résumé
        output.append("## 📊 Résumé\n")
        output.append(f"- **Quiz analysés** : {self.report.total_quiz}")
        output.append(f"- **Problèmes détectés** : {self.report.total_issues}")
        output.append(f"  - 🔴 Haute priorité : {self.report.issues_by_severity['high']}")
        output.append(f"  - 🟡 Priorité moyenne : {self.report.issues_by_severity['medium']}")
        output.append(f"  - 🟢 Priorité basse : {self.report.issues_by_severity['low']}\n")

        # Problèmes par type
        issues_by_type = {}
        for issue in self.report.issues:
            issues_by_type.setdefault(issue.issue_type, []).append(issue)

        output.append("## 📋 Problèmes par type\n")
        for issue_type, issues in sorted(issues_by_type.items(), key=lambda x: len(x[1]), reverse=True):
            output.append(f"### {issue_type.replace('_', ' ').title()} ({len(issues)})\n")

            # Échantillon des 5 premiers
            for issue in issues[:5]:
                severity_icon = {'high': '🔴', 'medium': '🟡', 'low': '🟢'}[issue.severity]
                output.append(f"- {severity_icon} **Quiz {issue.quiz_id}** ({issue.level} - {issue.subject})")
                if issue.question_id:
                    output.append(f" [Q{issue.question_id}]")
                output.append(f": {issue.description}")
                if issue.example:
                    output.append(f"\n  > _{issue.example}_")
                output.append("\n")

            if len(issues) > 5:
                output.append(f"  _(... et {len(issues) - 5} autres)_\n")
            output.append("\n")

        # Quiz à améliorer en priorité (plus de 3 problèmes haute priorité)
        quiz_high_issues = {}
        for issue in self.report.issues:
            if issue.severity == 'high':
                quiz_high_issues.setdefault(issue.quiz_id, []).append(issue)

        priority_quiz = {qid: issues for qid, issues in quiz_high_issues.items() if len(issues) >= 3}

        if priority_quiz:
            output.append("## 🎯 Quiz à améliorer en priorité\n")
            output.append("_(Quiz avec 3+ problèmes haute priorité)_\n\n")
            for quiz_id, issues in sorted(priority_quiz.items(), key=lambda x: len(x[1]), reverse=True):
                first_issue = issues[0]
                output.append(f"- **Quiz {quiz_id}** ({first_issue.level} - {first_issue.subject}): {len(issues)} problèmes\n")

        # Recommandations
        output.append("\n## 💡 Recommandations\n")
        output.append("1. Commencer par les quiz à haute priorité (🔴)\n")
        output.append("2. Utiliser les exemples dans `dev/docs/quiz_exemples_qualite/`\n")
        output.append("3. Appliquer le template `TEMPLATE_QUIZ_QUALITE_PEDAGOGIQUE.md`\n")
        output.append("4. Relancer ce script après corrections pour valider\n")

        report_text = "\n".join(output)

        if output_path:
            with open(output_path, 'w', encoding='utf-8') as f:
                f.write(report_text)
            print(f"[report] Rapport généré: {output_path}")

        return report_text


def main():
    parser = argparse.ArgumentParser(description='Validation qualité pédagogique des quiz')
    parser.add_argument('--quiz-dir', type=str, default='src/data/quiz',
                        help='Répertoire des quiz (défaut: src/data/quiz)')
    parser.add_argument('--answers-dir', type=str, default='src/data/quiz_answers',
                        help='Répertoire des réponses (défaut: src/data/quiz_answers)')
    parser.add_argument('--report', type=str, default=None,
                        help='Chemin du fichier rapport Markdown (optionnel)')
    parser.add_argument('--min-correction-length', type=int, default=80,
                        help='Longueur minimum des corrections (défaut: 80)')

    args = parser.parse_args()

    quiz_dir = Path(args.quiz_dir)
    answers_dir = Path(args.answers_dir)

    if not quiz_dir.exists():
        print(f"[error] Répertoire quiz introuvable: {quiz_dir}")
        return 1

    if not answers_dir.exists():
        print(f"[error] Répertoire answers introuvable: {answers_dir}")
        return 1

    print(f"[validate] Quiz dir: {quiz_dir}")
    print(f"[validate] Answers dir: {answers_dir}")

    validator = QuizQualityValidator(quiz_dir, answers_dir)

    # Personnalisation des seuils si demandé
    if args.min_correction_length:
        validator.MIN_CORRECTION_LENGTH = args.min_correction_length

    report = validator.validate_all()

    print(f"\n[summary] Quiz analysés: {report.total_quiz}")
    print(f"[summary] Problèmes détectés: {report.total_issues}")
    print(f"  - Haute priorite (HIGH): {report.issues_by_severity['high']}")
    print(f"  - Priorite moyenne (MEDIUM): {report.issues_by_severity['medium']}")
    print(f"  - Priorite basse (LOW): {report.issues_by_severity['low']}")

    # Génération du rapport
    if args.report:
        report_path = Path(args.report)
        report_path.parent.mkdir(parents=True, exist_ok=True)
        validator.generate_markdown_report(report_path)
    else:
        # Afficher rapport sur stdout
        print("\n" + validator.generate_markdown_report())

    return 0


if __name__ == '__main__':
    exit(main())
