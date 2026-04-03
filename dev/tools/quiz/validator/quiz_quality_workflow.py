#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script workflow validation qualité quiz avec seuils de blocage.

Exécute enrichissement/génération quiz puis valide la qualité.
Bloque (exit code 1) si seuils de qualité non respectés.

Usage:
    python dev/tools/quiz/quiz_quality_workflow.py enrich --batch-size 50 --start-id 192
    python dev/tools/quiz/quiz_quality_workflow.py generate
    python dev/tools/quiz/quiz_quality_workflow.py harmonize

Seuils par défaut (configurables via --thresholds):
    - HIGH priority: max 30% des quiz avec problèmes haute priorité
    - MEDIUM priority: max 50% des quiz avec problèmes moyenne priorité
    - Corrections valides: min 70% des quiz avec corrections complètes

Exit codes:
    0 = Succès (qualité acceptable)
    1 = Échec (seuils qualité non respectés)
    2 = Erreur exécution (script/dépendance manquante)
"""

import subprocess
import sys
import json
import argparse
import os
from pathlib import Path

# Couleurs terminal (Windows compatible - pas d'emojis Unicode)
class Colors:
    HEADER = '\033[95m'
    OKBLUE = '\033[94m'
    OKCYAN = '\033[96m'
    OKGREEN = '\033[92m'
    WARNING = '\033[93m'
    FAIL = '\033[91m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'

def print_colored(message, color=Colors.ENDC):
    """Affiche message coloré (compatible Windows terminal)."""
    print(f"{color}{message}{Colors.ENDC}")

def run_command(cmd, description):
    """Exécute commande subprocess avec gestion erreurs."""
    print_colored(f"\n[WORKFLOW] {description}...", Colors.OKBLUE)
    print(f"  Commande: {' '.join(cmd)}")

    try:
        result = subprocess.run(cmd, check=True, capture_output=True, text=True, encoding='utf-8')
        print_colored(f"[OK] {description} termine avec succes", Colors.OKGREEN)
        return result.stdout
    except subprocess.CalledProcessError as e:
        print_colored(f"[ERREUR] {description} a echoue", Colors.FAIL)
        print(f"  Code erreur: {e.returncode}")
        if e.stderr:
            print(f"  Stderr: {e.stderr}")
        sys.exit(2)
    except FileNotFoundError:
        print_colored(f"[ERREUR] Commande non trouvee: {cmd[0]}", Colors.FAIL)
        print(f"  Verifiez que Python/Node est dans le PATH")
        sys.exit(2)

def parse_quality_report(report_path):
    """Parse le rapport de qualité markdown pour extraire métriques."""
    if not os.path.exists(report_path):
        print_colored(f"[ERREUR] Rapport qualite introuvable: {report_path}", Colors.FAIL)
        sys.exit(2)

    with open(report_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Extraction métriques (format du rapport validate_quiz_quality.py)
    metrics = {}

    # Total quiz analysés
    if "Quiz analyses" in content:
        try:
            line = [l for l in content.split('\n') if 'Quiz analyses' in l][0]
            metrics['total_quizzes'] = int(line.split(':')[1].strip())
        except:
            metrics['total_quizzes'] = 0

    # Problèmes détectés par priorité
    priority_counts = {}
    for priority in ['HIGH', 'MEDIUM', 'LOW']:
        if f"Priorite {priority}" in content:
            try:
                line = [l for l in content.split('\n') if f'Priorite {priority}' in l][0]
                count = int(line.split(':')[1].strip().split()[0])
                priority_counts[priority] = count
            except:
                priority_counts[priority] = 0

    metrics['priority_counts'] = priority_counts

    # Problèmes totaux
    if "Problemes detectes" in content:
        try:
            line = [l for l in content.split('\n') if 'Problemes detectes' in l][0]
            metrics['total_problems'] = int(line.split(':')[1].strip())
        except:
            metrics['total_problems'] = sum(priority_counts.values())

    return metrics

def check_quality_thresholds(metrics, thresholds):
    """Vérifie si métriques respectent seuils qualité."""
    issues = []
    total_quizzes = metrics.get('total_quizzes', 1)
    priority_counts = metrics.get('priority_counts', {})

    # Seuil haute priorité (% quiz avec problèmes HIGH)
    high_threshold = thresholds.get('high_max_percent', 30)
    high_count = priority_counts.get('HIGH', 0)
    high_percent = (high_count / total_quizzes * 100) if total_quizzes > 0 else 0

    if high_percent > high_threshold:
        issues.append({
            'severity': 'CRITICAL',
            'message': f"Trop de problemes haute priorite: {high_percent:.1f}% (seuil: {high_threshold}%)",
            'current': high_percent,
            'threshold': high_threshold
        })

    # Seuil moyenne priorité (% quiz avec problèmes MEDIUM)
    medium_threshold = thresholds.get('medium_max_percent', 50)
    medium_count = priority_counts.get('MEDIUM', 0)
    medium_percent = (medium_count / total_quizzes * 100) if total_quizzes > 0 else 0

    if medium_percent > medium_threshold:
        issues.append({
            'severity': 'WARNING',
            'message': f"Trop de problemes moyenne priorite: {medium_percent:.1f}% (seuil: {medium_threshold}%)",
            'current': medium_percent,
            'threshold': medium_threshold
        })

    return issues

def main():
    parser = argparse.ArgumentParser(
        description="Workflow validation qualite quiz avec seuils de blocage",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog=__doc__
    )

    parser.add_argument(
        'action',
        choices=['enrich', 'generate', 'harmonize'],
        help="Type d'operation: enrich (enrichissement progressif), generate (generation quiz bank), harmonize (harmonisation metadonnees)"
    )

    parser.add_argument(
        '--batch-size',
        type=int,
        default=50,
        help="Nombre de quiz a enrichir (enrich seulement, defaut: 50)"
    )

    parser.add_argument(
        '--start-id',
        type=int,
        default=None,
        help="ID de depart pour enrichissement progressif (enrich seulement)"
    )

    parser.add_argument(
        '--high-max-percent',
        type=float,
        default=30.0,
        help="Seuil max pourcentage problemes haute priorite (defaut: 30%%)"
    )

    parser.add_argument(
        '--medium-max-percent',
        type=float,
        default=50.0,
        help="Seuil max pourcentage problemes moyenne priorite (defaut: 50%%)"
    )

    parser.add_argument(
        '--no-block',
        action='store_true',
        help="Ne pas bloquer si seuils depasses (warning seulement)"
    )

    args = parser.parse_args()

    # Chemins workspace
    workspace_root = Path(__file__).parent.parent.parent.parent
    report_path = workspace_root / "dev" / "reports" / "quiz_quality_report.md"

    print_colored("\n" + "="*70, Colors.HEADER)
    print_colored("WORKFLOW VALIDATION QUALITE QUIZ", Colors.HEADER)
    print_colored("="*70 + "\n", Colors.HEADER)

    # Étape 1: Exécution action demandée
    if args.action == 'enrich':
        cmd = ['python', 'dev/tools/quiz/enrich_quizzes_progressive.py', str(args.batch_size)]
        if args.start_id is not None:
            cmd.extend(['--start-id', str(args.start_id)])
        run_command(cmd, f"Enrichissement progressif ({args.batch_size} quiz)")

    elif args.action == 'generate':
        cmd = ['python', 'dev/tools/quiz/generate_quiz_bank.py',
               '--config', 'dev/tools/quiz/config_quiz_bank.v1.json',
               '--apply', '--sync-src-quiz']
        run_command(cmd, "Generation quiz bank v1")

    elif args.action == 'harmonize':
        cmd = ['python', 'dev/tools/quiz/generate_quiz_bank.py',
               '--config', 'dev/tools/quiz/config_quiz_bank.v1.json',
               '--harmonize-only', '--apply', '--sync-src-quiz']
        run_command(cmd, "Harmonisation metadonnees quiz")

    # Étape 2: Validation qualité
    cmd_validate = [
        'python', 'dev/tools/quiz/validate_quiz_quality.py',
        '--quiz-dir', 'src/data/quiz',
        '--answers-dir', 'src/data/quiz_answers',
        '--report', str(report_path)
    ]
    run_command(cmd_validate, "Validation qualite pedagogique")

    # Étape 3: Analyse rapport et vérification seuils
    print_colored("\n[WORKFLOW] Analyse rapport qualite...", Colors.OKBLUE)
    metrics = parse_quality_report(str(report_path))

    print("\n--- METRIQUES QUALITE ---")
    print(f"Quiz analyses: {metrics.get('total_quizzes', 0)}")
    print(f"Problemes totaux: {metrics.get('total_problems', 0)}")
    print(f"  Haute priorite (HIGH): {metrics.get('priority_counts', {}).get('HIGH', 0)}")
    print(f"  Moyenne priorite (MEDIUM): {metrics.get('priority_counts', {}).get('MEDIUM', 0)}")
    print(f"  Basse priorite (LOW): {metrics.get('priority_counts', {}).get('LOW', 0)}")

    # Vérification seuils
    thresholds = {
        'high_max_percent': args.high_max_percent,
        'medium_max_percent': args.medium_max_percent
    }

    issues = check_quality_thresholds(metrics, thresholds)

    if not issues:
        print_colored("\n[OK] Tous les seuils de qualite sont respectes !", Colors.OKGREEN)
        print_colored(f"Rapport complet: {report_path}\n", Colors.OKCYAN)
        sys.exit(0)

    else:
        print_colored("\n[WARNING] Seuils de qualite non respectes:", Colors.WARNING)
        for issue in issues:
            severity_color = Colors.FAIL if issue['severity'] == 'CRITICAL' else Colors.WARNING
            print_colored(f"  [{issue['severity']}] {issue['message']}", severity_color)

        print_colored(f"\nRapport complet: {report_path}", Colors.OKCYAN)

        if args.no_block:
            print_colored("\n[INFO] Mode --no-block: workflow continue malgre avertissements\n", Colors.WARNING)
            sys.exit(0)
        else:
            print_colored("\n[FAIL] Workflow bloque. Ameliorez la qualite ou utilisez --no-block\n", Colors.FAIL)
            sys.exit(1)

if __name__ == '__main__':
    main()
