#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script enrichissement quiz v2 — Recherche web pédagogique basée metadata.

Enrichit corrections avec explications vérifiées à partir de :
- Level scolaire (6eme, 5eme, 4eme, 3eme, 2nde, 1ere, terminale)
- Subject (Français, Mathématiques, Anglais, etc.)
- Description quiz (concept général)
- QUESTIONS RÉELLES (mots-clés importants)

Sources prioritaires :
1. Wikiversity (ressources ouvertes pédagogiques)
2. Wiktionary (définitions académiques)
3. Wikipedia (explications contextualisées)
4. Éduscol data (ressources Education Nationale)

Usage:
    python dev/tools/quiz/enrich_with_sources.py --quiz-id 105
    python dev/tools/quiz/enrich_with_sources.py --batch-start 13 --batch-size 50 --dry-run
"""

import json
import argparse
import requests
import time
import csv
from pathlib import Path
from typing import Dict, List, Tuple
import urllib.parse

# Couleurs terminal
class Colors:
    OKBLUE = '\033[94m'
    OKGREEN = '\033[92m'
    WARNING = '\033[93m'
    FAIL = '\033[91m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'

def print_colored(message, color=Colors.ENDC):
    """Affiche message coloré."""
    print(f"{color}{message}{Colors.ENDC}")

class PedagogicSourceFinder:
    """Cherche explications pédagogiques dans sources officielles."""

    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({'User-Agent': 'MonCoachScolaire/1.0'})
        self.cache = {}
        self.source_map = {
            'wikiversity': 'Wikiversity - Ressources pédagogiques libres',
            'wiktionary': 'Wiktionary - Définitions académiques',
            'wikipedia': 'Wikipedia - Ressources encyclopédiques',
            'eduscol': 'Éduscol - Programme officiel Education Nationale'
        }

    def extract_keywords_from_questions(self, questions: List[Dict]) -> List[str]:
        """Extrait mots-clés importants des questions."""
        keywords = []

        for q in questions:
            question_text = q.get('question', '').lower()

            # Cherche patterns importants
            words = [w for w in question_text.split() if len(w) > 4]
            keywords.extend(words)

            # Améliore avec choices
            for choice in q.get('choices', []):
                if len(choice) > 4:
                    keywords.append(choice.lower())

        # Retirer doublons, garder top 5
        return list(set(keywords))[:5]

    def search_wikiversity(self, level: str, subject: str, concept: str, keywords: List[str]) -> Tuple[str, str]:
        """Cherche dans Wikiversity (ressources pédagogiques)."""
        try:
            # Construire requête intelligente
            query_parts = [subject, concept] + keywords[:2]
            query = ' '.join(query_parts).strip()

            # URL Wikiversity search
            url = f"https://en.wikiversity.org/w/api.php"
            params = {
                'action': 'query',
                'list': 'search',
                'srsearch': query,
                'format': 'json',
                'srlimit': 3
            }

            response = self.session.get(url, params=params, timeout=5)
            data = response.json()

            if data.get('query', {}).get('search'):
                # Récupérer texte du premier résultat
                search_result = data['query']['search'][0]
                page_title = search_result['title']

                # Chercher contenu page
                url_content = f"https://en.wikiversity.org/w/api.php"
                params_content = {
                    'action': 'query',
                    'titles': page_title,
                    'prop': 'extracts',
                    'explaintext': True,
                    'format': 'json',
                    'exintro': True
                }

                response_content = self.session.get(url_content, params=params_content, timeout=5)
                data_content = response_content.json()

                for page_id, page_data in data_content.get('query', {}).get('pages', {}).items():
                    if 'extract' in page_data:
                        extract = page_data['extract'][:300]  # Max 300 chars
                        url_source = f"https://en.wikiversity.org/wiki/{urllib.parse.quote(page_title)}"
                        return (extract, url_source)

        except Exception as e:
            print_colored(f"  [Wikiversity] Erreur: {str(e)}", Colors.WARNING)

        return None, None

    def search_wiktionary(self, concept: str) -> Tuple[str, str]:
        """Cherche définition dans Wiktionary."""
        try:
            url = f"https://en.wiktionary.org/w/api.php"
            params = {
                'action': 'query',
                'titles': concept,
                'prop': 'extracts',
                'explaintext': True,
                'format': 'json'
            }

            response = self.session.get(url, params=params, timeout=5)
            data = response.json()

            for page_id, page_data in data.get('query', {}).get('pages', {}).items():
                if 'extract' in page_data:
                    extract = page_data['extract'][:250]
                    url_source = f"https://en.wiktionary.org/wiki/{urllib.parse.quote(concept)}"
                    return (extract, url_source)

        except Exception as e:
            print_colored(f"  [Wiktionary] Erreur: {str(e)}", Colors.WARNING)

        return None, None

    def search_wikipedia(self, subject: str, concept: str, keywords: List[str]) -> Tuple[str, str]:
        """Cherche explication générale dans Wikipedia."""
        try:
            query_parts = [concept, subject] + keywords[:1]
            query = ' '.join(query_parts).strip()

            url = f"https://en.wikipedia.org/w/api.php"
            params = {
                'action': 'query',
                'list': 'search',
                'srsearch': query,
                'format': 'json',
                'srlimit': 1
            }

            response = self.session.get(url, params=params, timeout=5)
            data = response.json()

            if data.get('query', {}).get('search'):
                search_result = data['query']['search'][0]
                page_title = search_result['title']

                # Récupérer contenu
                url_content = f"https://en.wikipedia.org/w/api.php"
                params_content = {
                    'action': 'query',
                    'titles': page_title,
                    'prop': 'extracts',
                    'explaintext': True,
                    'format': 'json',
                    'exintro': True
                }

                response_content = self.session.get(url_content, params=params_content, timeout=5)
                data_content = response_content.json()

                for page_id, page_data in data_content.get('query', {}).get('pages', {}).items():
                    if 'extract' in page_data:
                        extract = page_data['extract'][:300]
                        url_source = f"https://en.wikipedia.org/wiki/{urllib.parse.quote(page_title)}"
                        return (extract, url_source)

        except Exception as e:
            print_colored(f"  [Wikipedia] Erreur: {str(e)}", Colors.WARNING)

        return None, None

    def find_pedagogic_source(self, level: str, subject: str, description: str,
                             questions: List[Dict]) -> Dict:
        """Cherche source pédagogique pour le quiz."""

        print_colored(f"    [RECHERCHE] {subject} - {description}", Colors.OKBLUE)

        # Extraire concepts clés
        keywords = self.extract_keywords_from_questions(questions)
        main_concept = description.split(':')[-1].strip() if ':' in description else description

        # Stratégie recherche multisource
        sources_found = []

        # 1. Wikiversity (priorité 1 - ressources pédagogiques)
        print_colored("      Wikiversity...", Colors.OKBLUE)
        extract, url = self.search_wikiversity(level, subject, main_concept, keywords)
        if extract:
            sources_found.append({
                'type': 'wikiversity',
                'extract': extract,
                'url': url
            })
            time.sleep(0.5)  # Rate limit

        # 2. Wiktionary (priorité 2 - définitions)
        print_colored("      Wiktionary...", Colors.OKBLUE)
        extract, url = self.search_wiktionary(main_concept)
        if extract:
            sources_found.append({
                'type': 'wiktionary',
                'extract': extract,
                'url': url
            })
            time.sleep(0.5)

        # 3. Wikipedia (priorité 3 - contexte général)
        if not sources_found:
            print_colored("      Wikipedia...", Colors.OKBLUE)
            extract, url = self.search_wikipedia(subject, main_concept, keywords)
            if extract:
                sources_found.append({
                    'type': 'wikipedia',
                    'extract': extract,
                    'url': url
                })
                time.sleep(0.5)

        return {
            'concept': main_concept,
            'keywords': keywords,
            'sources': sources_found
        }

def enrich_quiz_answers_with_sources(quiz_path: Path, answers_path: Path,
                                     source_finder: PedagogicSourceFinder,
                                     dry_run: bool = False) -> Dict:
    """Enrichit réponses quiz avec explications sources vérifiées."""

    # Charger quiz existant
    with open(quiz_path, 'r', encoding='utf-8') as f:
        quiz_data = json.load(f)

    with open(answers_path, 'r', encoding='utf-8') as f:
        answers_data = json.load(f)

    # Extraire metadata
    level = quiz_data.get('contents', {}).get('level', 'unknown')
    subject = quiz_data.get('contents', {}).get('subject', 'unknown')
    description = quiz_data.get('contents', {}).get('description', 'unknown')
    questions = quiz_data.get('quiz', {}).get('questions', [])

    quiz_id = quiz_path.stem

    # Chercher sources
    source_info = source_finder.find_pedagogic_source(level, subject, description, questions)

    # Enrichir réponses avec explications sources
    if source_info['sources']:
        primary_source = source_info['sources'][0]

        # Mettre à jour réponses
        for answer in answers_data.get('quiz', {}).get('answers', []):
            # Format enrichi : explication succincte + source
            extract = primary_source['extract'][:150]  # Limiter taille
            source_type = primary_source['type']
            url = primary_source['url']

            answer['correction'] = (
                f"{extract}... "
                f"[En savoir plus: {source_type}]"
            )
            answer['source_url'] = url
            answer['source_type'] = source_type

    # Sauvegarder (sauf dry-run)
    if not dry_run:
        with open(answers_path, 'w', encoding='utf-8') as f:
            json.dump(answers_data, f, indent=2, ensure_ascii=False)

        # Ajouter source_info au quiz
        quiz_data['sources_info'] = source_info
        with open(quiz_path, 'w', encoding='utf-8') as f:
            json.dump(quiz_data, f, indent=2, ensure_ascii=False)

    return {
        'quiz_id': quiz_id,
        'level': level,
        'subject': subject,
        'concept': source_info['concept'],
        'sources_found': len(source_info['sources']),
        'dry_run': dry_run
    }


def _safe_int_stem(path: Path) -> int:
    """Convertit le stem en int pour tri robuste."""
    try:
        return int(path.stem)
    except ValueError:
        return 10**9


def _normalize_answer_indexes(answers: List[Dict]) -> List[Dict]:
    """Garantit un index cohérent pour chaque réponse."""
    normalized = []
    for idx, answer in enumerate(answers):
        row = dict(answer)
        row['index'] = idx
        normalized.append(row)
    return normalized


def export_csv_batches(
    quiz_dir: Path,
    output_prefix: Path,
    batch_size: int = 50,
    start_id: int = 1,
    end_id: int = 999999,
) -> Dict:
    """Exporte les metadata quiz en CSV batchés pour traitement externe."""

    quiz_files = [
        p for p in quiz_dir.glob('*.json')
        if p.stem.isdigit() and start_id <= int(p.stem) <= end_id
    ]
    quiz_files.sort(key=_safe_int_stem)

    rows: List[Dict] = []
    for file_path in quiz_files:
        with open(file_path, 'r', encoding='utf-8') as f:
            quiz = json.load(f)

        rows.append({
            'id': file_path.stem,
            'level': quiz.get('quiz', {}).get('level', quiz.get('contents', {}).get('level', '')),
            'subject': quiz.get('quiz', {}).get('subject', quiz.get('contents', {}).get('subject', '')),
            'title': quiz.get('contents', {}).get('title', quiz.get('quiz', {}).get('title', '')),
            'description': quiz.get('contents', {}).get('description', ''),
            'question_count': quiz.get('quiz', {}).get('question_count', 0),
            'questions_json': json.dumps(quiz.get('quiz', {}).get('questions', []), ensure_ascii=False),
        })

    output_prefix.parent.mkdir(parents=True, exist_ok=True)

    if not rows:
        return {
            'total_quiz': 0,
            'total_batches': 0,
            'files': [],
        }

    batch_files: List[str] = []
    total_batches = (len(rows) + batch_size - 1) // batch_size

    for batch_index in range(total_batches):
        start = batch_index * batch_size
        end = start + batch_size
        chunk = rows[start:end]
        output_file = output_prefix.parent / f"{output_prefix.name}_batch{batch_index + 1}.csv"

        with open(output_file, 'w', newline='', encoding='utf-8') as csvfile:
            writer = csv.DictWriter(
                csvfile,
                fieldnames=[
                    'id', 'level', 'subject', 'title', 'description',
                    'question_count', 'questions_json',
                ]
            )
            writer.writeheader()
            writer.writerows(chunk)

        batch_files.append(str(output_file))
        print_colored(f"[EXPORT] {output_file.name} pret ({len(chunk)} quiz)", Colors.OKGREEN)

    return {
        'total_quiz': len(rows),
        'total_batches': total_batches,
        'files': batch_files,
    }


def _truncate_text(value: str, max_len: int) -> str:
    """Tronque un texte pour limiter le volume token/envoye au LLM."""
    if not isinstance(value, str):
        return ''
    cleaned = ' '.join(value.split())
    if len(cleaned) <= max_len:
        return cleaned
    return cleaned[:max_len - 3].rstrip() + '...'


def _compact_questions(questions: List[Dict], max_questions: int, max_question_len: int) -> List[Dict]:
    """Conserve uniquement les champs utiles pour l'enrichissement externe."""
    compacted: List[Dict] = []

    for idx, question in enumerate(questions[:max_questions], start=1):
        row = {
            'question_id': idx,
            'type': question.get('type', 'open'),
            'question': _truncate_text(question.get('question', ''), max_question_len),
        }

        if isinstance(question.get('choices'), list):
            row['choices'] = [
                _truncate_text(str(choice), 60)
                for choice in question.get('choices', [])[:4]
            ]

        compacted.append(row)

    return compacted


def export_llm_packs(
    quiz_dir: Path,
    output_dir: Path,
    batch_size: int = 8,
    start_id: int = 1,
    end_id: int = 999999,
    max_questions: int = 6,
    max_question_len: int = 140,
    max_description_len: int = 220,
) -> Dict:
    """Exporte des micro-batches JSON + prompts courts pour Perplexity."""

    quiz_files = [
        p for p in quiz_dir.glob('*.json')
        if p.stem.isdigit() and start_id <= int(p.stem) <= end_id
    ]
    quiz_files.sort(key=_safe_int_stem)

    records: List[Dict] = []
    for file_path in quiz_files:
        with open(file_path, 'r', encoding='utf-8') as f:
            quiz = json.load(f)

        questions = quiz.get('quiz', {}).get('questions', [])
        records.append({
            'id': file_path.stem,
            'level': quiz.get('quiz', {}).get('level', quiz.get('contents', {}).get('level', '')),
            'subject': quiz.get('quiz', {}).get('subject', quiz.get('contents', {}).get('subject', '')),
            'title': _truncate_text(
                quiz.get('contents', {}).get('title', quiz.get('quiz', {}).get('title', '')),
                120,
            ),
            'description': _truncate_text(quiz.get('contents', {}).get('description', ''), max_description_len),
            'question_count': quiz.get('quiz', {}).get('question_count', len(questions)),
            'questions': _compact_questions(questions, max_questions=max_questions, max_question_len=max_question_len),
        })

    output_dir.mkdir(parents=True, exist_ok=True)

    if not records:
        return {
            'total_quiz': 0,
            'total_batches': 0,
            'json_files': [],
            'prompt_files': [],
        }

    json_files: List[str] = []
    prompt_files: List[str] = []
    total_batches = (len(records) + batch_size - 1) // batch_size

    for batch_index in range(total_batches):
        start = batch_index * batch_size
        end = start + batch_size
        chunk = records[start:end]
        batch_num = batch_index + 1

        json_file = output_dir / f"perplexity_pack_batch{batch_num}.json"
        with open(json_file, 'w', encoding='utf-8') as f:
            json.dump(chunk, f, indent=2, ensure_ascii=False)

        prompt_file = output_dir / f"perplexity_prompt_batch{batch_num}.txt"
        prompt_text = (
            "Tu es un assistant pedagogique. Produis UNIQUEMENT un JSON (tableau d'objets).\\n"
            "Pour chaque quiz d'entree, retourne :\\n"
            "- id\\n"
            "- provider: 'perplexity-auto'\\n"
            "- exercisenotion: 4 objets {notion, description} bases programme FR\\n"
            "- answers: reponse pour chaque question fournie avec champs {question_id, type, answer, correction}\\n"
            "Contraintes: correction claire 2 a 3 phrases, ton pedagogique, pas d'emojis, pas de texte hors JSON.\\n"
            "Si une question est trop vague, fournir une reponse generique utile, sans inventer des faits non verifies.\\n\\n"
            "INPUT_JSON:\\n"
            f"{json.dumps(chunk, ensure_ascii=False, indent=2)}\\n"
        )
        with open(prompt_file, 'w', encoding='utf-8') as f:
            f.write(prompt_text)

        json_files.append(str(json_file))
        prompt_files.append(str(prompt_file))
        print_colored(
            f"[LLM PACK] batch {batch_num}: {len(chunk)} quiz -> {json_file.name} / {prompt_file.name}",
            Colors.OKGREEN,
        )

    return {
        'total_quiz': len(records),
        'total_batches': total_batches,
        'json_files': json_files,
        'prompt_files': prompt_files,
    }


def merge_enriched_json(
    quiz_dir: Path,
    answers_dir: Path,
    enriched_json_path: Path,
    dry_run: bool = False,
    source_credit_default: str = '',
) -> Dict:
    """Merge les enrichissements externes dans quiz + quiz_answers."""

    with open(enriched_json_path, 'r', encoding='utf-8') as f:
        payload = json.load(f)

    if isinstance(payload, list):
        items = payload
    elif isinstance(payload, dict):
        items = payload.get('data') or payload.get('results') or payload.get('items') or []
    else:
        raise ValueError('Format JSON non supporte: attendu liste ou objet contenant data/results/items')

    merged = 0
    skipped = 0
    errors: List[str] = []

    for item in items:
        quiz_id = str(item.get('id', '')).strip()
        if not quiz_id.isdigit():
            skipped += 1
            continue

        quiz_path = quiz_dir / f"{quiz_id}.json"
        answers_path = answers_dir / f"{quiz_id}.json"

        if not quiz_path.exists():
            skipped += 1
            continue

        try:
            with open(quiz_path, 'r', encoding='utf-8') as f:
                quiz_data = json.load(f)

            exercisenotion = item.get('exercisenotion')
            if isinstance(exercisenotion, list) and exercisenotion:
                quiz_data['exercisenotion'] = exercisenotion

            source_credit = item.get('source_credit') or source_credit_default
            if source_credit:
                quiz_data['sources_info'] = {
                    'credit': source_credit,
                    'provider': item.get('provider', 'external-enrichment'),
                    'source_urls': item.get('source_urls', []),
                }

            answers_list = item.get('answers', [])
            if not isinstance(answers_list, list) or not answers_list:
                skipped += 1
                continue

            normalized_answers = _normalize_answer_indexes(answers_list)

            if source_credit:
                for answer in normalized_answers:
                    answer['correction_source'] = source_credit

            answers_data = {
                'contents': {
                    'title': quiz_data.get('contents', {}).get('title', quiz_data.get('quiz', {}).get('title', '')),
                    'level': quiz_data.get('contents', {}).get('level', quiz_data.get('quiz', {}).get('level', '')),
                    'subject': quiz_data.get('contents', {}).get('subject', quiz_data.get('quiz', {}).get('subject', '')),
                },
                'quiz': {
                    'title': quiz_data.get('quiz', {}).get('title', quiz_data.get('contents', {}).get('title', '')),
                    'question_count': quiz_data.get('quiz', {}).get('question_count', len(normalized_answers)),
                    'answers': normalized_answers,
                    'level': quiz_data.get('quiz', {}).get('level', quiz_data.get('contents', {}).get('level', '')),
                    'subject': quiz_data.get('quiz', {}).get('subject', quiz_data.get('contents', {}).get('subject', '')),
                }
            }

            if not dry_run:
                with open(quiz_path, 'w', encoding='utf-8') as f:
                    json.dump(quiz_data, f, indent=2, ensure_ascii=False)

                with open(answers_path, 'w', encoding='utf-8') as f:
                    json.dump(answers_data, f, indent=2, ensure_ascii=False)

            merged += 1

        except Exception as exc:
            errors.append(f"Quiz {quiz_id}: {exc}")

    return {
        'merged': merged,
        'skipped': skipped,
        'errors': errors,
        'input_items': len(items),
        'dry_run': dry_run,
    }

def main():
    parser = argparse.ArgumentParser(
        description="Enrichissement quiz avec sources pédagogiques vérifiées",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog=__doc__
    )

    parser.add_argument('--quiz-id', type=int, help='ID quiz à enrichir')
    parser.add_argument('--batch-start', type=int, default=13, help='ID départ pour lot')
    parser.add_argument('--batch-size', type=int, default=10, help='Nombre quiz par lot')
    parser.add_argument('--start-id', type=int, default=1, help='ID min pour export CSV')
    parser.add_argument('--end-id', type=int, default=999999, help='ID max pour export CSV')
    parser.add_argument('--export-csv', action='store_true', help='Exporte metadata quiz en CSV batchés')
    parser.add_argument('--output-prefix', default='dev/reports/batch_quiz_metadata', help='Préfixe des CSV exportés')
    parser.add_argument('--export-llm-pack', action='store_true', help='Exporte des micro-batches JSON + prompt courts pour Perplexity')
    parser.add_argument('--llm-output-dir', default='dev/reports/perplexity_packs', help='Dossier sortie des packs LLM')
    parser.add_argument('--llm-batch-size', type=int, default=8, help='Nombre de quiz par micro-batch LLM')
    parser.add_argument('--max-questions', type=int, default=6, help='Nombre max de questions exportees par quiz pour LLM')
    parser.add_argument('--max-question-len', type=int, default=140, help='Longueur max d une question exportee')
    parser.add_argument('--max-description-len', type=int, default=220, help='Longueur max de description exportee')
    parser.add_argument('--merge-json', help='Fichier JSON enrichi à fusionner (retour Perplexity)')
    parser.add_argument('--source-credit', default='', help='Crédit source à ajouter aux corrections')
    parser.add_argument('--dry-run', action='store_true', help='Preview sans modifier')

    args = parser.parse_args()

    project_root = Path(__file__).parent.parent.parent.parent
    quiz_dir = project_root / "src" / "data" / "quiz"
    answers_dir = project_root / "src" / "data" / "quiz_answers"

    source_finder = PedagogicSourceFinder()

    print_colored("\n" + "="*70, Colors.BOLD)
    print_colored("ENRICHISSEMENT QUIZ V2 — SOURCES PÉDAGOGIQUES", Colors.BOLD)
    print_colored("="*70 + "\n", Colors.BOLD)

    if args.export_csv:
        output_prefix = project_root / args.output_prefix
        export_result = export_csv_batches(
            quiz_dir=quiz_dir,
            output_prefix=output_prefix,
            batch_size=args.batch_size,
            start_id=args.start_id,
            end_id=args.end_id,
        )
        print_colored(
            f"\n[EXPORT OK] {export_result['total_quiz']} quiz -> {export_result['total_batches']} batch(es)",
            Colors.OKGREEN,
        )
        return

    if args.export_llm_pack:
        llm_output_dir = project_root / args.llm_output_dir
        llm_result = export_llm_packs(
            quiz_dir=quiz_dir,
            output_dir=llm_output_dir,
            batch_size=args.llm_batch_size,
            start_id=args.start_id,
            end_id=args.end_id,
            max_questions=args.max_questions,
            max_question_len=args.max_question_len,
            max_description_len=args.max_description_len,
        )
        print_colored(
            f"\n[LLM EXPORT OK] {llm_result['total_quiz']} quiz -> {llm_result['total_batches']} micro-batch(es)",
            Colors.OKGREEN,
        )
        return

    if args.merge_json:
        merge_path = project_root / args.merge_json
        if not merge_path.exists():
            print_colored(f"[ERREUR] Fichier introuvable: {merge_path}", Colors.FAIL)
            suggestions = sorted((project_root / 'dev' / 'reports').glob('*enriched*.json'), key=lambda p: p.name)
            if not suggestions:
                suggestions = sorted((project_root / 'dev' / 'reports').glob('*.json'), key=lambda p: p.name)[:10]
            if suggestions:
                print_colored("Fichiers JSON disponibles dans dev/reports :", Colors.WARNING)
                for item in suggestions[:10]:
                    print(f"  - {item.relative_to(project_root)}")
            print_colored("Astuce: place le JSON Perplexity dans dev/reports puis relance --merge-json", Colors.WARNING)
            return

        merge_result = merge_enriched_json(
            quiz_dir=quiz_dir,
            answers_dir=answers_dir,
            enriched_json_path=merge_path,
            dry_run=args.dry_run,
            source_credit_default=args.source_credit,
        )
        print_colored(
            f"\n[MERGE OK] merged={merge_result['merged']} skipped={merge_result['skipped']} input={merge_result['input_items']}",
            Colors.OKGREEN,
        )
        if merge_result['errors']:
            print_colored(f"[WARN] {len(merge_result['errors'])} erreurs", Colors.WARNING)
            for err in merge_result['errors'][:10]:
                print_colored(f"  - {err}", Colors.WARNING)
        return

    if args.quiz_id:
        # Mode single quiz
        quiz_path = quiz_dir / f"{args.quiz_id}.json"
        answers_path = answers_dir / f"{args.quiz_id}.json"

        if quiz_path.exists():
            result = enrich_quiz_answers_with_sources(quiz_path, answers_path,
                                                      source_finder, args.dry_run)
            print_colored(f"\n[OK] Quiz #{result['quiz_id']} enrichi", Colors.OKGREEN)
            print(f"  Concept: {result['concept']}")
            print(f"  Sources trouvées: {result['sources_found']}")
        else:
            print_colored(f"[ERREUR] Quiz #{args.quiz_id} introuvable", Colors.FAIL)

    else:
        # Mode batch
        print(f"[*] Enrichissement par lot")
        print(f"    Départ: Quiz #{args.batch_start}")
        print(f"    Taille lot: {args.batch_size}")
        print()

        enriched = []
        cursor = args.batch_start
        count = 0

        while count < args.batch_size:
            quiz_path = quiz_dir / f"{cursor}.json"
            answers_path = answers_dir / f"{cursor}.json"

            if quiz_path.exists():
                try:
                    result = enrich_quiz_answers_with_sources(quiz_path, answers_path,
                                                              source_finder, args.dry_run)
                    enriched.append(result)

                    prefix = "[DRY-RUN] " if args.dry_run else ""
                    print(f"{prefix}Quiz #{cursor}: {result['subject']} - {result['sources_found']} source(s)")
                    count += 1

                except Exception as e:
                    print_colored(f"  [ERREUR] Quiz #{cursor}: {str(e)}", Colors.FAIL)

            cursor += 1

        print_colored(f"\n[SUCCESS] {len(enriched)} quiz enrichis avec sources", Colors.OKGREEN)

if __name__ == '__main__':
    main()
