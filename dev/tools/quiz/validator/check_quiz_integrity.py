#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Vérification d'intégrité des corpus de quiz
- src/data/quiz/*.json
- src/data/quiz_answers/*.json

Usage:
    python check_quiz_integrity.py
    python check_quiz_integrity.py --quiz-dir src/data/quiz --answers-dir src/data/quiz_answers --report dev/reports/quiz_integrity_report.md
    python check_quiz_integrity.py --fix-stub

Options:
    --fix-stub : créer des fichiers quiz_answers manquants avec un squelette de placeholder pour éviter les 404 dans l'API.
"""

import argparse
import json
from pathlib import Path


def read_id_set(directory: Path):
    return {p.stem for p in directory.glob('*.json') if p.is_file()}


def make_stub_answer_file(quiz_id, location):
    data = {
        'contents': {
            'title': f'QUIZ {quiz_id} - réponses à remplir',
            'level': 'unknown',
            'subject': 'unknown',
        },
        'quiz': {
            'title': f'Quiz {quiz_id} - corrections placeholder',
            'question_count': 0,
            'answers': [],
            'level': 'unknown',
            'subject': 'unknown',
        }
    }
    with open(location, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)


def main():
    parser = argparse.ArgumentParser(description='Vérifie correspondance quiz / quiz_answers')
    parser.add_argument('--quiz-dir', default='src/data/quiz', help='Répertoire des quiz (questions)')
    parser.add_argument('--answers-dir', default='src/data/quiz_answers', help='Répertoire des réponses')
    parser.add_argument('--report', default=None, help='Chemin de rapport MD à générer')
    parser.add_argument('--fix-stub', action='store_true', help='Génère les fichiers manquants quiz_answers en mode squelette')

    args = parser.parse_args()

    quiz_dir = Path(args.quiz_dir)
    answers_dir = Path(args.answers_dir)

    if not quiz_dir.is_dir() or not answers_dir.is_dir():
        raise SystemExit(f'ERREUR : répertoire introuvable {quiz_dir} ou {answers_dir}')

    qids = read_id_set(quiz_dir)
    aids = read_id_set(answers_dir)

    missing_answers = sorted(int(q) for q in qids - aids)
    missing_quiz = sorted(int(a) for a in aids - qids)

    print('---')
    print('Quiz count:', len(qids))
    print('Quiz_answers count:', len(aids))
    print('Missing answers:', len(missing_answers), missing_answers[:20])
    print('Extra answers without quiz:', len(missing_quiz), missing_quiz[:20])
    print('---')

    if args.fix_stub and missing_answers:
        for quiz_id in missing_answers:
            target = answers_dir / f'{quiz_id}.json'
            print('Création stub', target)
            make_stub_answer_file(quiz_id, target)

    if args.report:
        with open(args.report, 'w', encoding='utf-8') as out:
            out.write('# Rapport intégrité quiz\n\n')
            out.write(f'- quiz files: {len(qids)}\n')
            out.write(f'- quiz_answers files: {len(aids)}\n')
            out.write(f'- missing answers: {len(missing_answers)}\n')
            out.write(f'- orphan answers: {len(missing_quiz)}\n')
            if missing_answers:
                out.write('\n## IDs sans quiz_answers\n')
                out.writelines([f'- {i}\n' for i in missing_answers])
            if missing_quiz:
                out.write('\n## IDs sans quiz\n')
                out.writelines([f'- {i}\n' for i in missing_quiz])

    if missing_answers or missing_quiz:
        raise SystemExit(2)

    print('OK - Tout est cohérent')


if __name__ == '__main__':
    main()
