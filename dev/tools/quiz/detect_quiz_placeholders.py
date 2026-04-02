#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script de détection avancée des placeholders dans quiz et quiz_answers
Détecte le contenu générique non pédagogique nécessitant enrichissement

Usage:
    python detect_quiz_placeholders.py
    python detect_quiz_placeholders.py --quiz-dir src/data/quiz --answers-dir src/data/quiz_answers
    python detect_quiz_placeholders.py --output dev/reports/placeholders_detected.json
    python detect_quiz_placeholders.py --min-id 1 --max-id 100
"""

import json
import argparse
import re
from pathlib import Path
from typing import Dict, List, Optional, Set
from dataclasses import dataclass, field
from datetime import datetime


@dataclass
class PlaceholderDetection:
    """Représente un placeholder détecté"""
    quiz_id: int
    level: str
    subject: str
    location: str  # 'question', 'answer', 'correction', 'choice', 'notion'
    category: str  # Type de placeholder détecté
    severity: str  # 'critical', 'high', 'medium', 'low'
    matched_pattern: str
    context: str  # Extrait du texte problématique
    question_index: int = None
    answer_index: int = None


@dataclass
class PlaceholderReport:
    """Rapport de détection des placeholders"""
    scan_date: str = field(default_factory=lambda: datetime.now().isoformat())
    total_quiz_scanned: int = 0
    total_placeholders_found: int = 0
    quiz_with_placeholders: Set[int] = field(default_factory=set)
    detections: List[PlaceholderDetection] = field(default_factory=list)
    stats_by_category: Dict[str, int] = field(default_factory=dict)
    stats_by_severity: Dict[str, int] = field(default_factory=lambda: {
        'critical': 0, 'high': 0, 'medium': 0, 'low': 0
    })

    def add_detection(self, detection: PlaceholderDetection):
        """Ajoute une détection au rapport"""
        self.detections.append(detection)
        self.total_placeholders_found += 1
        self.quiz_with_placeholders.add(detection.quiz_id)

        # Statistiques par catégorie
        if detection.category not in self.stats_by_category:
            self.stats_by_category[detection.category] = 0
        self.stats_by_category[detection.category] += 1

        # Statistiques par sévérité
        self.stats_by_severity[detection.severity] += 1


class PlaceholderDetector:
    """Détecteur avancé de placeholders dans quiz et réponses"""

    # Patterns de placeholders CRITIQUES (contenu inutilisable)
    CRITICAL_PATTERNS = {
        'placeholder_text': [
            r'question\s+(de\s+)?diagnostic\s+pour',  # "question de diagnostic pour..."
            r'(exercice|quiz)\s+\d+\s+(niveau|pour)',  # "exercice 1 niveau..."
            r'TODO|FIXME|À\s+COMPLÉTER|PLACEHOLDER',  # Marqueurs développeur
            r'exemple\s+de\s+(question|réponse)',  # "exemple de question..."
            r'\[.*?\](?!\()',  # Texte entre crochets (hors markdown links)
        ],
        'generic_template': [
            r'\bconcept\s*[a-d]\b',  # "concept a", "concept b"
            r'notion\s+[0-9]+',  # "notion 1", "notion 2"
            r'(chapitre|leçon|thème)\s+\d+\s*(?!:)',  # "chapitre 1" sans titre
            r'question\s+\d+\s*(:|\?)',  # "Question 1:" sans contexte
        ],
        'lorem_ipsum': [
            r'\blorem\s+ipsum\b',
            r'\bdolor\s+sit\s+amet\b',
            r'ipsum\s+dolor',
        ],
    }

    # Patterns HAUTE priorité (contenu incomplet)
    HIGH_PRIORITY_PATTERNS = {
        'incomplete_sentence': [
            r'\.\.\.\s*[?\.]?\s*$',  # Fin par "..."
            r'^[^\.!?]{0,20}$',  # Phrase trop courte (< 20 car)
            r'etc\.\s*$',  # Fin par "etc."
            r'\bet\s+cetera\b',
        ],
        'missing_context': [
            r'^(Le|La|Les|Un|Une)\s+\w+\s*[=≈]\s*\.{2,}',  # "Le mot = ..."
            r'^\w+\s+\?+\s*$',  # Un seul mot + points d'interrogation
            r'^(Vrai|Faux)\s*\?*\s*$',  # Juste "Vrai ?" sans contexte
        ],
        'variable_placeholder': [
            r'\{[a-zA-Z_]+\}',  # {variable}
            r'\$[a-zA-Z_]+',  # $variable
            r'%%\w+%%',  # %%placeholder%%
        ],
    }

    # Patterns MOYENS (formulation à améliorer)
    MEDIUM_PRIORITY_PATTERNS = {
        'telegraphic': [
            r'^[^a-zA-Z]{0,5}\w+\s*[=≈]\s*\.{2,}',  # "mot = ...?"
            r'≈\s*\w+\s*\.{2,}',  # "≈ concept..."
        ],
        'generic_feedback': [
            r'^(Correct|Incorrect|Bravo|Dommage)\.?\s*$',  # Feedback générique seul
            r'^(Oui|Non|Vrai|Faux)\.?\s*$',
            r'^Bonne?\s+réponse\.?\s*$',
        ],
    }

    # Patterns FAIBLES (améliorations cosmétiques)
    LOW_PRIORITY_PATTERNS = {
        'formatting_issue': [
            r'\s{3,}',  # Espaces multiples
            r'[\.!?]{2,}',  # Ponctuation excessive
            r'\n{3,}',  # Sauts de ligne multiples
        ],
        'typo_indicators': [
            r'\b[A-Z]{4,}\b',  # TOUT EN MAJUSCULES
            r'[a-z][A-Z]',  # MauvaiseCapitalisation
        ],
    }

    def __init__(self, quiz_dir: Path, answers_dir: Path):
        self.quiz_dir = Path(quiz_dir)
        self.answers_dir = Path(answers_dir)
        self.report = PlaceholderReport()

    def scan_all(self, min_id: int = None, max_id: int = None) -> PlaceholderReport:
        """Scanne tous les quiz du répertoire"""
        quiz_files = sorted(self.quiz_dir.glob("*.json"))

        for quiz_file in quiz_files:
            quiz_id = int(quiz_file.stem)

            # Filtrage par range d'IDs
            if min_id and quiz_id < min_id:
                continue
            if max_id and quiz_id > max_id:
                continue

            self.scan_quiz(quiz_id)

        self.report.total_quiz_scanned = len([
            f for f in quiz_files
            if (not min_id or int(f.stem) >= min_id) and
               (not max_id or int(f.stem) <= max_id)
        ])

        return self.report

    def scan_quiz(self, quiz_id: int):
        """Scanne un quiz spécifique pour détecter les placeholders"""
        quiz_path = self.quiz_dir / f"{quiz_id}.json"
        answers_path = self.answers_dir / f"{quiz_id}.json"

        if not quiz_path.exists():
            return

        try:
            # Scan du fichier quiz
            with open(quiz_path, 'r', encoding='utf-8') as f:
                quiz_data = json.load(f)

            level = quiz_data.get('contents', {}).get('level', 'unknown')
            subject = quiz_data.get('contents', {}).get('subject', 'unknown')

            # Scan des questions
            questions = quiz_data.get('quiz', {}).get('questions', [])
            question_type_map = self._build_question_type_map(questions)
            for idx, question in enumerate(questions):
                self._scan_question(quiz_id, level, subject, question, idx)

            # Scan des notions
            notions = quiz_data.get('exercisenotion', [])
            self._scan_notions(quiz_id, level, subject, notions)

            # Scan du fichier answers
            if answers_path.exists():
                with open(answers_path, 'r', encoding='utf-8') as f:
                    answers_data = json.load(f)

                # Supporte les deux formats rencontrés dans le repo:
                # 1) {"answers": [...]} (format récent)
                # 2) {"quiz": {"answers": [...]}} (format legacy)
                answers = answers_data.get('answers')
                if not isinstance(answers, list):
                    answers = answers_data.get('quiz', {}).get('answers', [])

                for answer in answers:
                    self._scan_answer(quiz_id, level, subject, answer, question_type_map)

        except Exception as e:
            detection = PlaceholderDetection(
                quiz_id=quiz_id,
                level='unknown',
                subject='unknown',
                location='file',
                category='parsing_error',
                severity='critical',
                matched_pattern='',
                context=f"Erreur: {str(e)}"
            )
            self.report.add_detection(detection)

    def _scan_question(self, quiz_id: int, level: str, subject: str,
                       question: Dict, question_idx: int):
        """Scanne une question pour détecter les placeholders"""
        question_text = question.get('question', '')
        choices = question.get('choices', [])

        # Scan du texte de la question
        self._check_text_patterns(
            quiz_id, level, subject, question_text,
            location='question', question_index=question_idx
        )

        # Scan des choix de réponse
        for choice_idx, choice in enumerate(choices):
            if isinstance(choice, str):
                self._check_text_patterns(
                    quiz_id, level, subject, choice,
                    location=f'choice_{choice_idx}', question_index=question_idx
                )

    def _scan_answer(
        self,
        quiz_id: int,
        level: str,
        subject: str,
        answer: Dict,
        question_type_map: Dict[str, str],
    ):
        """Scanne une réponse/correction pour détecter les placeholders"""
        answer_idx = answer.get('index', answer.get('id', -1))
        correction = answer.get('correction', '')

        question_type = self._infer_answer_question_type(answer, answer_idx, question_type_map)

        # Scan de la correction
        self._check_text_patterns(
            quiz_id, level, subject, correction,
            location='correction', answer_index=answer_idx
        )

        # Vérification spécifique: correction trop courte
        # Seuils adaptés au type: les explications QCM/VF peuvent être plus concises.
        min_len_by_type = {
            'qcm': 50,
            'vrai-faux': 45,
            'open': 80,
            'texte': 80,
        }
        min_len = min_len_by_type.get(question_type, 60)

        if correction and len(correction.strip()) < min_len:
            detection = PlaceholderDetection(
                quiz_id=quiz_id,
                level=level,
                subject=subject,
                location='correction',
                category='too_short_correction',
                severity='medium',
                matched_pattern=f'length={len(correction)}<{min_len}({question_type})',
                context=correction[:100],
                answer_index=answer_idx
            )
            self.report.add_detection(detection)

    def _build_question_type_map(self, questions: List[Dict]) -> Dict[str, str]:
        """Construit un mapping robuste question_id/index -> type de question."""
        mapping: Dict[str, str] = {}
        for idx, question in enumerate(questions):
            qtype = str(question.get('type', '')).strip().lower()
            if not qtype:
                continue

            qid = question.get('id')
            if qid is not None:
                mapping[str(qid)] = qtype

            # Compat index 0-based et 1-based selon fichiers legacy
            mapping[str(idx)] = qtype
            mapping[str(idx + 1)] = qtype
        return mapping

    def _infer_answer_question_type(
        self,
        answer: Dict,
        answer_idx,
        question_type_map: Dict[str, str],
    ) -> str:
        """Infère le type de question de la réponse (qcm/vrai-faux/open)."""
        qid = answer.get('question_id')
        if qid is not None:
            qtype = question_type_map.get(str(qid))
            if qtype:
                return qtype

        qtype = question_type_map.get(str(answer_idx))
        if qtype:
            return qtype

        if 'correct_option' in answer:
            return 'qcm'
        if 'correct' in answer:
            return 'vrai-faux'
        if 'correct_answer' in answer:
            return 'open'
        return 'unknown'

    def _scan_notions(self, quiz_id: int, level: str, subject: str, notions: List):
        """Scanne les notions pour détecter les placeholders"""
        for notion_idx, notion in enumerate(notions):
            notion_text = notion if isinstance(notion, str) else str(notion)
            self._check_text_patterns(
                quiz_id, level, subject, notion_text,
                location=f'notion_{notion_idx}'
            )

    def _check_text_patterns(self, quiz_id: int, level: str, subject: str,
                            text: str, location: str,
                            question_index: int = None, answer_index: int = None):
        """Vérifie un texte contre tous les patterns de placeholders"""
        if not text or not isinstance(text, str):
            return

        text_lower = text.lower()

        # Check CRITICAL patterns
        for category, patterns in self.CRITICAL_PATTERNS.items():
            for pattern in patterns:
                if re.search(pattern, text, re.IGNORECASE):
                    match = re.search(pattern, text, re.IGNORECASE)
                    detection = PlaceholderDetection(
                        quiz_id=quiz_id,
                        level=level,
                        subject=subject,
                        location=location,
                        category=category,
                        severity='critical',
                        matched_pattern=pattern,
                        context=self._extract_context(text, match),
                        question_index=question_index,
                        answer_index=answer_index
                    )
                    self.report.add_detection(detection)
                    return  # Un seul critical suffit

        # Check HIGH patterns
        for category, patterns in self.HIGH_PRIORITY_PATTERNS.items():
            for pattern in patterns:
                if re.search(pattern, text, re.IGNORECASE):
                    match = re.search(pattern, text, re.IGNORECASE)
                    detection = PlaceholderDetection(
                        quiz_id=quiz_id,
                        level=level,
                        subject=subject,
                        location=location,
                        category=category,
                        severity='high',
                        matched_pattern=pattern,
                        context=self._extract_context(text, match),
                        question_index=question_index,
                        answer_index=answer_index
                    )
                    self.report.add_detection(detection)

        # Check MEDIUM patterns
        for category, patterns in self.MEDIUM_PRIORITY_PATTERNS.items():
            for pattern in patterns:
                if re.search(pattern, text, re.IGNORECASE):
                    match = re.search(pattern, text, re.IGNORECASE)
                    detection = PlaceholderDetection(
                        quiz_id=quiz_id,
                        level=level,
                        subject=subject,
                        location=location,
                        category=category,
                        severity='medium',
                        matched_pattern=pattern,
                        context=self._extract_context(text, match),
                        question_index=question_index,
                        answer_index=answer_index
                    )
                    self.report.add_detection(detection)

        # Check LOW patterns (optionnel, évite le bruit)
        # for category, patterns in self.LOW_PRIORITY_PATTERNS.items():
        #     for pattern in patterns:
        #         if re.search(pattern, text, re.IGNORECASE):
        #             # ...

    def _extract_context(self, text: str, match: re.Match, context_size: int = 60) -> str:
        """Extrait le contexte autour d'un match"""
        start = max(0, match.start() - context_size)
        end = min(len(text), match.end() + context_size)
        context = text[start:end]

        if start > 0:
            context = "..." + context
        if end < len(text):
            context = context + "..."

        return context.strip()

    def export_report(self, output_path: Path = None) -> Dict:
        """Exporte le rapport en JSON structuré"""
        # Conversion du rapport en dict sérialisable
        report_dict = {
            'meta': {
                'scan_date': self.report.scan_date,
                'total_quiz_scanned': self.report.total_quiz_scanned,
                'total_placeholders_found': self.report.total_placeholders_found,
                'quiz_with_placeholders_count': len(self.report.quiz_with_placeholders),
            },
            'statistics': {
                'by_severity': self.report.stats_by_severity,
                'by_category': self.report.stats_by_category,
            },
            'quiz_ids_affected': sorted(list(self.report.quiz_with_placeholders)),
            'detections': [
                {
                    'quiz_id': d.quiz_id,
                    'level': d.level,
                    'subject': d.subject,
                    'location': d.location,
                    'category': d.category,
                    'severity': d.severity,
                    'pattern': d.matched_pattern,
                    'context': d.context,
                    'question_index': d.question_index,
                    'answer_index': d.answer_index,
                }
                for d in self.report.detections
            ]
        }

        # Export vers fichier si demandé
        if output_path:
            output_path = Path(output_path)
            output_path.parent.mkdir(parents=True, exist_ok=True)

            with open(output_path, 'w', encoding='utf-8') as f:
                json.dump(report_dict, f, ensure_ascii=False, indent=2)

            print(f"✅ Rapport exporté: {output_path}")

        return report_dict

    def print_summary(self):
        """Affiche un résumé du rapport dans le terminal"""
        print("\n" + "="*70)
        print("📊 RAPPORT DE DÉTECTION DES PLACEHOLDERS")
        print("="*70)
        print(f"Date du scan: {self.report.scan_date}")
        print(f"Quiz scannés: {self.report.total_quiz_scanned}")
        print(f"Placeholders détectés: {self.report.total_placeholders_found}")
        print(f"Quiz affectés: {len(self.report.quiz_with_placeholders)}")
        print()

        print("📈 Répartition par sévérité:")
        for severity, count in sorted(self.report.stats_by_severity.items()):
            emoji = {'critical': '🔴', 'high': '🟠', 'medium': '🟡', 'low': '🟢'}.get(severity, '⚪')
            print(f"  {emoji} {severity.upper()}: {count}")
        print()

        print("📋 Répartition par catégorie:")
        for category, count in sorted(
            self.report.stats_by_category.items(),
            key=lambda x: x[1],
            reverse=True
        )[:10]:  # Top 10
            print(f"  • {category}: {count}")
        print()

        print("🎯 Quiz prioritaires (critical + high):")
        critical_high_ids = set()
        for detection in self.report.detections:
            if detection.severity in ['critical', 'high']:
                critical_high_ids.add(detection.quiz_id)

        print(f"  IDs: {sorted(list(critical_high_ids))[:20]}")  # Top 20
        if len(critical_high_ids) > 20:
            print(f"  ... et {len(critical_high_ids) - 20} autres")

        print("="*70 + "\n")


def main():
    parser = argparse.ArgumentParser(
        description="Détection avancée des placeholders dans quiz et quiz_answers"
    )
    parser.add_argument(
        '--quiz-dir',
        default='src/data/quiz',
        help='Répertoire des fichiers quiz'
    )
    parser.add_argument(
        '--answers-dir',
        default='src/data/quiz_answers',
        help='Répertoire des fichiers quiz_answers'
    )
    parser.add_argument(
        '--output',
        default='dev/reports/placeholders_detected.json',
        help='Fichier de sortie JSON'
    )
    parser.add_argument(
        '--min-id',
        type=int,
        help='ID minimum à scanner'
    )
    parser.add_argument(
        '--max-id',
        type=int,
        help='ID maximum à scanner'
    )

    args = parser.parse_args()

    # Création du détecteur
    detector = PlaceholderDetector(
        quiz_dir=args.quiz_dir,
        answers_dir=args.answers_dir
    )

    # Scan
    print("🔍 Scan des placeholders en cours...")
    detector.scan_all(min_id=args.min_id, max_id=args.max_id)

    # Export
    detector.export_report(output_path=args.output)

    # Affichage résumé
    detector.print_summary()


if __name__ == '__main__':
    main()
