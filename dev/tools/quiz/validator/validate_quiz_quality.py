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

            answers = []
            answer_map = {}
            if answers_path.exists():
                with open(answers_path, 'r', encoding='utf-8') as f:
                    answers_data = json.load(f)
                    answers = answers_data.get('quiz', {}).get('answers', [])
                    answer_map = self._build_answer_map(answers)
            else:
                issue = QualityIssue(
                    quiz_id=quiz_id,
                    level=level,
                    subject=subject,
                    severity='medium',
                    issue_type='missing_answers_file',
                    description='Fichier de corrections absent pour ce quiz'
                )
                self.report.add_issue(issue)

            # Validation des questions
            for index, question in enumerate(questions, start=1):
                self._validate_question(quiz_id, level, subject, question, answer_map, index)

            # Vérifier la correspondance questions <-> corrections
            if answers_path.exists():
                self._validate_answer_coverage(quiz_id, level, subject, questions, answers)

            # Validation des notions
            self._validate_notions(quiz_id, level, subject, notions)

            # Validation des indices pédagogiques du quiz (optionnel)
            if 'course_hint' in quiz_data:
                self._validate_course_hint(quiz_id, level, subject, quiz_data['course_hint'])

            # Validation des corrections (si fichier answers existe)
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

    def _build_answer_map(self, answers: List[Dict]) -> Dict[int, str]:
        """Construit une map question_id -> correction à partir du fichier answers."""
        answer_map = {}
        for answer in answers:
            raw_question_id = answer.get('question_id')
            try:
                question_id = int(raw_question_id)
            except (TypeError, ValueError):
                continue
            correction = str(answer.get('correction', '')).strip()
            answer_map[question_id] = correction
        return answer_map

    def _normalize_question_id(self, question_id, index: int) -> int:
        if isinstance(question_id, int):
            return question_id
        if isinstance(question_id, str):
            if question_id.isdigit():
                return int(question_id)
            if '_' in question_id:
                suffix = question_id.split('_')[-1]
                if suffix.isdigit():
                    return int(suffix)
        return index

    def _validate_answer_coverage(self, quiz_id: int, level: str, subject: str, questions: List[Dict], answers: List[Dict]):
        question_ids = set()
        for index, question in enumerate(questions, start=1):
            question_ids.add(self._normalize_question_id(question.get('id'), index))

        answer_ids = set()
        for answer in answers:
            raw_question_id = answer.get('question_id')
            try:
                answer_ids.add(int(raw_question_id))
            except (TypeError, ValueError):
                continue

        missing_answers = sorted(question_ids - answer_ids)
        extra_answers = sorted(answer_ids - question_ids)

        if missing_answers:
            issue = QualityIssue(
                quiz_id=quiz_id,
                level=level,
                subject=subject,
                severity='high',
                issue_type='missing_answers',
                description=f"Pas de correction trouvée pour les questions: {', '.join(map(str, missing_answers))}",
                example=', '.join(map(str, missing_answers[:5]))
            )
            self.report.add_issue(issue)

        if extra_answers:
            issue = QualityIssue(
                quiz_id=quiz_id,
                level=level,
                subject=subject,
                severity='medium',
                issue_type='extra_answers',
                description=f"Le fichier de corrections contient des questions inconnues: {', '.join(map(str, extra_answers))}",
                example=', '.join(map(str, extra_answers[:5]))
            )
            self.report.add_issue(issue)

    def _validate_question(self, quiz_id: int, level: str, subject: str, question: Dict, answer_map: Dict[int, str], index: int):
        """Valide la qualité d'une question"""
        question_text = str(question.get('question', '')).strip()
        question_id = question.get('id')
        normalized_question_id = self._normalize_question_id(question_id, index)

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

        # Validation de l'explication de la question ou de la correction associée
        explanation_text = str(question.get('explanation', '')).strip()
        correction_text = answer_map.get(normalized_question_id, '')

        if explanation_text:
            if len(explanation_text) < self.MIN_CORRECTION_LENGTH:
                issue = QualityIssue(
                    quiz_id=quiz_id,
                    level=level,
                    subject=subject,
                    severity='high',
                    issue_type='short_explanation',
                    description=f"Explication trop courte ({len(explanation_text)} caractères, min recommandé: {self.MIN_CORRECTION_LENGTH})",
                    question_id=question_id,
                    example=explanation_text[:80]
                )
                self.report.add_issue(issue)
        elif correction_text:
            # Correction de la réponse existe, elle sera validée séparément.
            pass
        else:
            issue = QualityIssue(
                quiz_id=quiz_id,
                level=level,
                subject=subject,
                severity='high',
                issue_type='missing_explanation',
                description='Question sans explication pédagogique ni correction associée',
                question_id=question_id,
                example=question_text[:80]
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

        if len(notions) < 2:
            issue = QualityIssue(
                quiz_id=quiz_id,
                level=level,
                subject=subject,
                severity='medium',
                issue_type='few_notions',
                description=f"Nombre de notions insuffisant ({len(notions)} seulement). Au moins 2 notions recommandées."
            )
            self.report.add_issue(issue)

        # Vérification que les notions ont des descriptions
        for notion in notions:
            notion_name = str(notion.get('notion', '')).strip()
            notion_description = str(notion.get('description', '')).strip()

            if notion_name == '':
                issue = QualityIssue(
                    quiz_id=quiz_id,
                    level=level,
                    subject=subject,
                    severity='medium',
                    issue_type='missing_notion_name',
                    description='Notion sans nom ou titre'
                )
                self.report.add_issue(issue)

            if notion_description == '':
                issue = QualityIssue(
                    quiz_id=quiz_id,
                    level=level,
                    subject=subject,
                    severity='high',
                    issue_type='undescribed_notion',
                    description=f"Notion '{notion_name or 'unknown'}' sans description"
                )
                self.report.add_issue(issue)
            elif len(notion_description) < 40:
                issue = QualityIssue(
                    quiz_id=quiz_id,
                    level=level,
                    subject=subject,
                    severity='medium',
                    issue_type='short_notion_description',
                    description=f"Description de notion trop courte ({len(notion_description)} caractères) pour '{notion_name or 'unknown'}'",
                    example=notion_description[:80]
                )
                self.report.add_issue(issue)

    def _validate_course_hint(self, quiz_id: int, level: str, subject: str, course_hint: str):
        """Valide la présence d'un indice pédagogique ou d'une aide contextualisée."""
        if course_hint is None:
            return

        course_hint_text = str(course_hint).strip()
        if course_hint_text == '':
            issue = QualityIssue(
                quiz_id=quiz_id,
                level=level,
                subject=subject,
                severity='medium',
                issue_type='empty_course_hint',
                description='Le champ course_hint est vide'
            )
            self.report.add_issue(issue)
            return

        if len(course_hint_text) < 40:
            issue = QualityIssue(
                quiz_id=quiz_id,
                level=level,
                subject=subject,
                severity='medium',
                issue_type='short_course_hint',
                description=f"Indice pédagogique trop court ({len(course_hint_text)} caractères)",
                example=course_hint_text
            )
            self.report.add_issue(issue)

        if any(placeholder in course_hint_text.lower() for placeholder in ['à remplir', 'placeholder', 'todo', 'temporaire']):
            issue = QualityIssue(
                quiz_id=quiz_id,
                level=level,
                subject=subject,
                severity='high',
                issue_type='placeholder_course_hint',
                description='Le champ course_hint contient un placeholder ou une valeur de test'
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

    def generate_json_report(self, output_path: Path = None):
        """Génère un rapport JSON pour des workflows fiables."""
        data = {
            'generated_at': Path(__file__).stem,
            'total_quiz': self.report.total_quiz,
            'total_issues': self.report.total_issues,
            'issues_by_severity': self.report.issues_by_severity,
            'issues': [
                {
                    'quiz_id': issue.quiz_id,
                    'level': issue.level,
                    'subject': issue.subject,
                    'severity': issue.severity,
                    'issue_type': issue.issue_type,
                    'description': issue.description,
                    'question_id': issue.question_id,
                    'example': issue.example,
                }
                for issue in self.report.issues
            ],
        }

        json_text = json.dumps(data, ensure_ascii=False, indent=2)
        if output_path:
            with open(output_path, 'w', encoding='utf-8') as f:
                f.write(json_text)
            print(f"[report] Rapport JSON généré: {output_path}")

        return json_text


def main():
    parser = argparse.ArgumentParser(description='Validation qualité pédagogique des quiz')
    parser.add_argument('--quiz-dir', type=str, default='src/data/quiz',
                        help='Répertoire des quiz (défaut: src/data/quiz)')
    parser.add_argument('--answers-dir', type=str, default='src/data/quiz_answers',
                        help='Répertoire des réponses (défaut: src/data/quiz_answers)')
    parser.add_argument('--report', type=str, default=None,
                        help='Chemin du fichier rapport Markdown (optionnel)')
    parser.add_argument('--report-json', type=str, default=None,
                        help='Chemin du fichier rapport JSON (optionnel)')
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

    if args.report_json:
        json_path = Path(args.report_json)
        json_path.parent.mkdir(parents=True, exist_ok=True)
        validator.generate_json_report(json_path)

    if not args.report and not args.report_json:
        # Afficher rapport sur stdout
        print("\n" + validator.generate_markdown_report())

    return 0


if __name__ == '__main__':
    exit(main())
