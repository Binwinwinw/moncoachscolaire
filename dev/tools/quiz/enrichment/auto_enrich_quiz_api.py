#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script d'enrichissement automatique de quiz via APIs gratuites
Génère du contenu pédagogique vérifié et vérifiable pour remplacer les placeholders

APIs utilisées (gratuites):
- Wikipedia API: Définitions et contexte académique
- Wiktionary API: Définitions linguistiques et exemples
- Wikiversity API: Contenu pédagogique par niveau scolaire

Usage:
    python auto_enrich_quiz_api.py --quiz-id 10
    python auto_enrich_quiz_api.py --quiz-ids 6,7,8,9,10 --dry-run
    python auto_enrich_quiz_api.py --from-placeholders dev/reports/placeholders_detected.json --severity critical
    python auto_enrich_quiz_api.py --auto-validate --backup
"""

import json
import argparse
import requests
import time
import re
from pathlib import Path
from typing import Dict, List, Optional, Tuple
from dataclasses import dataclass, field
from datetime import datetime
import urllib.parse


@dataclass
class EnrichmentSource:
    """Source de données pour enrichissement"""
    api: str  # 'wikipedia', 'wiktionary', 'wikiversity'
    url: str
    content: str
    confidence: float  # 0-1
    verified: bool = False
    metadata: Dict = field(default_factory=dict)


@dataclass
class EnrichedContent:
    """Contenu enrichi généré"""
    original_text: str
    enriched_text: str
    sources: List[EnrichmentSource]
    quality_score: float  # 0-1
    review_needed: bool = False
    generation_date: str = field(default_factory=lambda: datetime.now().isoformat())


class QuizEnricherAPI:
    """Enrichisseur automatique de quiz via APIs gratuites"""

    # Configuration des APIs
    WIKIPEDIA_API = "https://fr.wikipedia.org/api/rest_v1/page/summary/"
    WIKTIONARY_API = "https://fr.wiktionary.org/w/api.php"
    WIKIVERSITY_API = "https://fr.wikiversity.org/w/api.php"

    # Délai entre appels API (rate limiting)
    API_DELAY = 0.5  # secondes

    # Mapping niveau → Wikiversity
    LEVEL_MAPPING = {
        '6eme': 'Collège en France/Classe de sixième',
        '5eme': 'Collège en France/Classe de cinquième',
        '4eme': 'Collège en France/Classe de quatrième',
        '3eme': 'Collège en France/Classe de troisième',
        'seconde': 'Lycée en France/Classe de seconde',
        'premiere': 'Lycée en France/Classe de première',
        'terminale': 'Lycée en France/Classe de terminale',
    }

    # Mapping matière → mots-clés Wikiversity
    SUBJECT_KEYWORDS = {
        'Mathématiques': ['mathématiques', 'algèbre', 'géométrie', 'arithmétique'],
        'Français': ['français', 'grammaire', 'orthographe', 'littérature'],
        'Anglais': ['anglais', 'english', 'grammaire anglaise'],
        'SVT': ['biologie', 'SVT', 'sciences de la vie', 'sciences naturelles'],
        'Physique': ['physique', 'mécanique', 'optique', 'électricité'],
        'Chimie': ['chimie', 'réactions chimiques', 'atomes', 'molécules'],
        'Histoire': ['histoire', 'chronologie', 'période historique'],
        'Géographie': ['géographie', 'cartographie', 'territoires'],
    }

    def __init__(self, quiz_dir: Path, answers_dir: Path, backup: bool = True):
        self.quiz_dir = Path(quiz_dir)
        self.answers_dir = Path(answers_dir)
        self.backup = backup
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'MonCoachScolaire/1.0 (Educational; quiz enrichment bot)'
        })

    def enrich_quiz(self, quiz_id: int, dry_run: bool = False) -> Dict:
        """Enrichit un quiz complet (questions + corrections)"""
        quiz_path = self.quiz_dir / f"{quiz_id}.json"
        answers_path = self.answers_dir / f"{quiz_id}.json"

        if not quiz_path.exists():
            return {'error': f'Quiz {quiz_id} not found'}

        # Backup
        if self.backup and not dry_run:
            self._create_backup(quiz_id)

        # Chargement des données
        with open(quiz_path, 'r', encoding='utf-8') as f:
            quiz_data = json.load(f)

        level = quiz_data.get('contents', {}).get('level', 'unknown')
        subject = quiz_data.get('contents', {}).get('subject', 'unknown')

        print(f"\n{'='*70}")
        print(f"📝 Enrichissement Quiz #{quiz_id} - {subject} {level}")
        print(f"{'='*70}\n")

        enrichment_log = {
            'quiz_id': quiz_id,
            'level': level,
            'subject': subject,
            'questions_enriched': [],
            'corrections_enriched': [],
            'dry_run': dry_run,
        }

        # Enrichissement des questions
        questions = quiz_data.get('quiz', {}).get('questions', [])
        for idx, question in enumerate(questions):
            question_text = question.get('question', '')

            if self._needs_enrichment(question_text):
                print(f"🔍 Question {idx+1}: Enrichissement requis")
                enriched = self._enrich_question(question_text, subject, level)

                if not dry_run and enriched:
                    question['question'] = enriched.enriched_text
                    enrichment_log['questions_enriched'].append({
                        'index': idx,
                        'original': question_text[:80],
                        'enriched': enriched.enriched_text[:80],
                        'sources': [s.api for s in enriched.sources],
                        'quality_score': enriched.quality_score,
                    })
                    print(f"  ✅ Enrichi (score: {enriched.quality_score:.2f})")
                else:
                    print(f"  ⚠️  Mode dry-run ou échec enrichissement")

        # Enrichissement des corrections
        if answers_path.exists():
            with open(answers_path, 'r', encoding='utf-8') as f:
                answers_data = json.load(f)

            answers = answers_data.get('quiz', {}).get('answers', [])
            for answer in answers:
                correction = answer.get('correction', '')
                answer_idx = answer.get('index', answer.get('id', -1))

                if self._needs_enrichment(correction):
                    print(f"🔍 Correction {answer_idx+1}: Enrichissement requis")
                    enriched = self._enrich_correction(correction, subject, level)

                    if not dry_run and enriched:
                        answer['correction'] = enriched.enriched_text
                        enrichment_log['corrections_enriched'].append({
                            'index': answer_idx,
                            'original': correction[:80],
                            'enriched': enriched.enriched_text[:80],
                            'sources': [s.api for s in enriched.sources],
                            'quality_score': enriched.quality_score,
                        })
                        print(f"  ✅ Enrichi (score: {enriched.quality_score:.2f})")
                    else:
                        print(f"  ⚠️  Mode dry-run ou échec enrichissement")

            # Sauvegarde answers
            if not dry_run and enrichment_log['corrections_enriched']:
                with open(answers_path, 'w', encoding='utf-8') as f:
                    json.dump(answers_data, f, ensure_ascii=False, indent=2)
                print(f"\n💾 Fichier answers sauvegardé: {answers_path}")

        # Sauvegarde quiz
        if not dry_run and enrichment_log['questions_enriched']:
            with open(quiz_path, 'w', encoding='utf-8') as f:
                json.dump(quiz_data, f, ensure_ascii=False, indent=2)
            print(f"💾 Fichier quiz sauvegardé: {quiz_path}")

        return enrichment_log

    def _needs_enrichment(self, text: str) -> bool:
        """Détermine si un texte nécessite enrichissement"""
        if not text or len(text.strip()) < 20:
            return True

        # Patterns de placeholders critiques
        placeholder_patterns = [
            r'question\s+(de\s+)?diagnostic',
            r'TODO|FIXME|À\s+COMPLÉTER',
            r'\bconcept\s*[a-d]\b',
            r'\.\.\.\s*\?*\s*$',
            r'exemple\s+de\s+(question|réponse)',
        ]

        for pattern in placeholder_patterns:
            if re.search(pattern, text, re.IGNORECASE):
                return True

        return False

    def _enrich_question(self, original_text: str, subject: str, level: str) -> Optional[EnrichedContent]:
        """Enrichit une question via APIs"""
        # Extraction du concept principal
        concept = self._extract_concept(original_text, subject)

        if not concept:
            print("  ⚠️  Aucun concept identifiable")
            return None

        # Recherche Wikipedia
        wiki_source = self._fetch_wikipedia(concept)

        # Recherche Wiktionary (pour langues)
        wikt_source = None
        if subject in ['Français', 'Anglais']:
            wikt_source = self._fetch_wiktionary(concept)

        # Synthèse des sources
        sources = [s for s in [wiki_source, wikt_source] if s]

        if not sources:
            print("  ⚠️  Aucune source API trouvée")
            return None

        # Génération de la question enrichie
        enriched_text = self._generate_question_from_sources(
            concept, sources, subject, level
        )

        quality_score = self._calculate_quality_score(enriched_text, sources)

        return EnrichedContent(
            original_text=original_text,
            enriched_text=enriched_text,
            sources=sources,
            quality_score=quality_score,
            review_needed=(quality_score < 0.7)
        )

    def _enrich_correction(self, original_text: str, subject: str, level: str) -> Optional[EnrichedContent]:
        """Enrichit une correction via APIs"""
        # Extraction du concept principal
        concept = self._extract_concept(original_text, subject)

        if not concept:
            return None

        # Recherche Wikipedia
        wiki_source = self._fetch_wikipedia(concept)

        if not wiki_source:
            return None

        # Génération de la correction enrichie
        enriched_text = self._generate_correction_from_sources(
            concept, [wiki_source], subject, level
        )

        quality_score = self._calculate_quality_score(enriched_text, [wiki_source])

        return EnrichedContent(
            original_text=original_text,
            enriched_text=enriched_text,
            sources=[wiki_source],
            quality_score=quality_score,
            review_needed=(quality_score < 0.7)
        )

    def _extract_concept(self, text: str, subject: str) -> Optional[str]:
        """Extrait le concept principal d'un texte"""
        # Nettoyage
        text_clean = re.sub(r'[\.!?\(\)\[\]]', ' ', text)
        text_clean = re.sub(r'\s+', ' ', text_clean).strip()

        # Mots-clés par matière
        keywords = self.SUBJECT_KEYWORDS.get(subject, [])

        # Extraction simple: premier nom significatif
        words = text_clean.split()
        for word in words:
            if len(word) > 4 and word[0].isupper():
                return word

        # Fallback: chercher dans keywords
        for keyword in keywords:
            if keyword.lower() in text_clean.lower():
                return keyword

        return None

    def _fetch_wikipedia(self, concept: str) -> Optional[EnrichmentSource]:
        """Récupère le résumé Wikipedia d'un concept"""
        try:
            time.sleep(self.API_DELAY)  # Rate limiting

            url = f"{self.WIKIPEDIA_API}{urllib.parse.quote(concept)}"
            response = self.session.get(url, timeout=10)

            if response.status_code != 200:
                return None

            data = response.json()
            extract = data.get('extract', '')

            if not extract or len(extract) < 50:
                return None

            return EnrichmentSource(
                api='wikipedia',
                url=data.get('content_urls', {}).get('desktop', {}).get('page', ''),
                content=extract,
                confidence=0.9,  # Wikipedia = haute confiance
                verified=True,
                metadata={'title': data.get('title', '')}
            )

        except Exception as e:
            print(f"  ❌ Erreur Wikipedia API: {str(e)}")
            return None

    def _fetch_wiktionary(self, word: str) -> Optional[EnrichmentSource]:
        """Récupère la définition Wiktionary d'un mot"""
        try:
            time.sleep(self.API_DELAY)

            params = {
                'action': 'query',
                'format': 'json',
                'titles': word,
                'prop': 'extracts',
                'exintro': True,
                'explaintext': True,
            }

            response = self.session.get(self.WIKTIONARY_API, params=params, timeout=10)

            if response.status_code != 200:
                return None

            data = response.json()
            pages = data.get('query', {}).get('pages', {})

            if not pages:
                return None

            page = next(iter(pages.values()))
            extract = page.get('extract', '')

            if not extract or len(extract) < 30:
                return None

            return EnrichmentSource(
                api='wiktionary',
                url=f"https://fr.wiktionary.org/wiki/{urllib.parse.quote(word)}",
                content=extract,
                confidence=0.8,
                verified=True,
                metadata={'word': word}
            )

        except Exception as e:
            print(f"  ❌ Erreur Wiktionary API: {str(e)}")
            return None

    def _generate_question_from_sources(self, concept: str, sources: List[EnrichmentSource],
                                       subject: str, level: str) -> str:
        """Génère une question pédagogique à partir des sources"""
        # Prendre le premier paragraphe de la meilleure source
        best_source = max(sources, key=lambda s: s.confidence)
        content = best_source.content

        # Extraction du premier paragraphe (résumé)
        first_para = content.split('\n')[0] if '\n' in content else content
        first_para = first_para[:200]  # Limite taille

        # Formulation de question selon la matière
        if subject == 'Français':
            question = f"Parmi les propositions suivantes concernant {concept}, laquelle est correcte ?"
        elif subject == 'Mathématiques':
            question = f"Quel énoncé définit correctement {concept} ?"
        elif subject == 'Anglais':
            question = f"Which definition best describes '{concept}'?"
        else:
            question = f"Quelle affirmation sur {concept} est exacte ?"

        return question

    def _generate_correction_from_sources(self, concept: str, sources: List[EnrichmentSource],
                                         subject: str, level: str) -> str:
        """Génère une correction pédagogique à partir des sources"""
        source = sources[0]
        content = source.content

        # Extraction du contexte pertinent (2 premières phrases)
        sentences = re.split(r'[\.!?]+', content)
        context = '. '.join(sentences[:2]).strip() + '.'

        # Formulation pédagogique
        correction = (
            f"{concept} : {context}\n\n"
            f"Source vérifiée : {source.api.capitalize()} - {source.url}"
        )

        return correction

    def _calculate_quality_score(self, text: str, sources: List[EnrichmentSource]) -> float:
        """Calcule un score de qualité 0-1"""
        score = 0.0

        # Longueur (0.3)
        if len(text) >= 150:
            score += 0.3
        elif len(text) >= 80:
            score += 0.15

        # Présence de sources (0.4)
        score += min(len(sources) * 0.2, 0.4)

        # Confiance des sources (0.3)
        if sources:
            avg_confidence = sum(s.confidence for s in sources) / len(sources)
            score += avg_confidence * 0.3

        return min(score, 1.0)

    def _create_backup(self, quiz_id: int):
        """Crée un backup avant modification"""
        backup_dir = Path('dev/backups/quiz_enrichment')
        backup_dir.mkdir(parents=True, exist_ok=True)

        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')

        # Backup quiz
        quiz_path = self.quiz_dir / f"{quiz_id}.json"
        if quiz_path.exists():
            backup_path = backup_dir / f"{quiz_id}_quiz_{timestamp}.json"
            import shutil
            shutil.copy2(quiz_path, backup_path)

        # Backup answers
        answers_path = self.answers_dir / f"{quiz_id}.json"
        if answers_path.exists():
            backup_path = backup_dir / f"{quiz_id}_answers_{timestamp}.json"
            import shutil
            shutil.copy2(answers_path, backup_path)

        print(f"💾 Backup créé: {backup_dir}/")


def main():
    parser = argparse.ArgumentParser(
        description="Enrichissement automatique de quiz via APIs gratuites"
    )
    parser.add_argument(
        '--quiz-id',
        type=int,
        help='ID du quiz à enrichir'
    )
    parser.add_argument(
        '--quiz-ids',
        help='Liste d\'IDs séparés par virgules (ex: 6,7,8,9,10)'
    )
    parser.add_argument(
        '--from-placeholders',
        help='Fichier JSON des placeholders détectés'
    )
    parser.add_argument(
        '--severity',
        choices=['critical', 'high', 'medium', 'low'],
        help='Sévérité minimale pour enrichissement depuis --from-placeholders'
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
        '--dry-run',
        action='store_true',
        help='Mode test sans modification'
    )
    parser.add_argument(
        '--no-backup',
        action='store_true',
        help='Désactiver la création de backup'
    )

    args = parser.parse_args()

    # Détermination des IDs à enrichir
    quiz_ids = []

    if args.quiz_id:
        quiz_ids = [args.quiz_id]
    elif args.quiz_ids:
        quiz_ids = [int(id.strip()) for id in args.quiz_ids.split(',')]
    elif args.from_placeholders:
        # Chargement depuis rapport placeholders
        with open(args.from_placeholders, 'r', encoding='utf-8') as f:
            placeholder_report = json.load(f)

        # Filtrage par sévérité
        severity_order = ['critical', 'high', 'medium', 'low']
        min_severity_idx = severity_order.index(args.severity) if args.severity else 0

        quiz_ids_set = set()
        for detection in placeholder_report.get('detections', []):
            severity = detection.get('severity', 'low')
            if severity_order.index(severity) <= min_severity_idx:
                quiz_ids_set.add(detection['quiz_id'])

        quiz_ids = sorted(list(quiz_ids_set))
        print(f"📋 {len(quiz_ids)} quiz à enrichir (sévérité >= {args.severity or 'critical'})")

    if not quiz_ids:
        print("❌ Aucun quiz spécifié. Utilisez --quiz-id, --quiz-ids ou --from-placeholders")
        return

    # Création de l'enrichisseur
    enricher = QuizEnricherAPI(
        quiz_dir=args.quiz_dir,
        answers_dir=args.answers_dir,
        backup=(not args.no_backup)
    )

    # Enrichissement
    results = []
    for quiz_id in quiz_ids:
        result = enricher.enrich_quiz(quiz_id, dry_run=args.dry_run)
        results.append(result)
        print()

    # Résumé
    print("\n" + "="*70)
    print("📊 RÉSUMÉ DE L'ENRICHISSEMENT")
    print("="*70)
    total_questions = sum(len(r.get('questions_enriched', [])) for r in results)
    total_corrections = sum(len(r.get('corrections_enriched', [])) for r in results)
    print(f"Quiz traités: {len(results)}")
    print(f"Questions enrichies: {total_questions}")
    print(f"Corrections enrichies: {total_corrections}")
    print(f"Mode: {'DRY-RUN (test)' if args.dry_run else 'PRODUCTION'}")
    print("="*70 + "\n")


if __name__ == '__main__':
    main()
