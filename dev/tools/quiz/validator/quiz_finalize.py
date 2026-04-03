#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Pipeline de finalisation quiz MonCoachScolaire.

Usage:
    python quiz_finalize.py
    python quiz_finalize.py --report dev/reports/quiz_final_checklist.md
    python quiz_finalize.py --fix-stub
    python quiz_finalize.py --no-pack

Actions:
- Vérifie la parité quiz <-> quiz_answers
- Vérifie la qualité pédagogique des quiz
- Détecte placeholders critiques dans quiz/answers
- (Option) génère des bundles packs optimisés
- Génère un rapport final sous forme Markdown
"""

import argparse
import json
import subprocess
from pathlib import Path


def run(cmd, check=True):
    print(f">>> running: {cmd}")
    res = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    print(res.stdout)
    if res.stderr:
        print(res.stderr)
    if check and res.returncode != 0:
        raise SystemExit(res.returncode)
    return res.returncode


def main():
    parser = argparse.ArgumentParser(description='Orchestrateur de pipeline quiz')
    parser.add_argument('--report', default='dev/reports/quiz_final_checklist.md', help='Fichier de rapport final Markdown')
    parser.add_argument('--fix-stub', action='store_true', help='Fixer stub quiz_answers manquants via check_quiz_integrity')
    parser.add_argument('--no-pack', action='store_true', help='Ne pas exécuter generate_quiz_packs (optionnel)')
    args = parser.parse_args()

    results = []

    # 1) Integrity check
    cmd = 'python dev/tools/quiz/validator/check_quiz_integrity.py --quiz-dir src/data/quiz --answers-dir src/data/quiz_answers --report dev/reports/quiz_integrity_report.md'
    if args.fix_stub:
        cmd += ' --fix-stub'
    rc = run(cmd, check=False)
    results.append(('integrity', rc == 0))

    # 2) Quality check
    cmd = 'python dev/tools/quiz/validator/validate_quiz_quality.py --quiz-dir src/data/quiz --answers-dir src/data/quiz_answers --report dev/reports/quiz_quality_report.md'
    rc = run(cmd, check=False)
    results.append(('quality', rc == 0))

    # 3) Placeholder detection
    cmd = 'python dev/tools/quiz/validator/detect_quiz_placeholders.py --quiz-dir src/data/quiz --answers-dir src/data/quiz_answers --output dev/reports/quiz_placeholders_report.json'
    rc = run(cmd, check=False)
    results.append(('placeholders', rc == 0))

    # 4) Génération de packs (optionnel)
    if not args.no_pack:
        pack_script = Path('dev/tools/quiz/generator/generate_quiz_packs.py')
        if pack_script.exists():
            cmd = f'python {pack_script.as_posix()} --start-id 1 --end-id 1700 --input-quiz-dir src/data/quiz --input-answers-dir src/data/quiz_answers --output-dir src/data/quiz_packs --min-questions 8'
            rc = run(cmd, check=False)
            results.append(('packs', rc == 0))
        else:
            print(f'WARNING : pack script introuvable ({pack_script})')
            results.append(('packs', False))
    else:
        results.append(('packs', True))

    # rapport final
    report_path = Path(args.report)
    report_path.parent.mkdir(parents=True, exist_ok=True)

    with report_path.open('w', encoding='utf-8') as report:
        report.write('# Checklist finale Quiz \n\n')
        report.write('## Résultats\n')
        for name, success in results:
            report.write(f'- **{name}** : {"OK" if success else "FAIL"}\n')
        report.write('\n## Détails des rapports générés\n')
        report.write('- dev/reports/quiz_integrity_report.md\n')
        report.write('- dev/reports/quiz_quality_report.md\n')
        report.write('- dev/reports/quiz_placeholders_report.json\n')
        if not args.no_pack:
            report.write('- quiz packs générés dans src/data/quiz_packs\n')

    print(f'Rapport final créé: {report_path}')

    if all(success for _, success in results):
        return 0
    return 2


if __name__ == '__main__':
    raise SystemExit(main())
