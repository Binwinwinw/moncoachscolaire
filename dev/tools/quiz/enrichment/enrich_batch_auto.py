#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script d'enrichissement automatique multi-lots avec validation.

Lance N lots consécutifs d'enrichissement progressif, avec validation
qualité après chaque lot.

Usage:
    python dev/tools/quiz/enrich_batch_auto.py --start-id 392 --num-batches 5
    python dev/tools/quiz/enrich_batch_auto.py --start-id 392 --num-batches 10 --batch-size 50
"""

import subprocess
import sys
import argparse
from pathlib import Path

# Couleurs terminal (Windows compatible)
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
    """Affiche message coloré."""
    print(f"{color}{message}{Colors.ENDC}")

def run_command(cmd, description):
    """Exécute commande et retourne succès/échec."""
    print_colored(f"\n[BATCH] {description}...", Colors.OKBLUE)
    print(f"  Commande: {' '.join(cmd)}")

    try:
        result = subprocess.run(cmd, check=True, capture_output=False, text=True)
        print_colored(f"[OK] {description} termine avec succes", Colors.OKGREEN)
        return True
    except subprocess.CalledProcessError as e:
        print_colored(f"[ERREUR] {description} a echoue (code {e.returncode})", Colors.FAIL)
        return False
    except FileNotFoundError:
        print_colored(f"[ERREUR] Commande non trouvee: {cmd[0]}", Colors.FAIL)
        return False

def main():
    parser = argparse.ArgumentParser(
        description="Enrichissement automatique multi-lots avec validation",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog=__doc__
    )

    parser.add_argument(
        '--start-id',
        type=int,
        required=True,
        help="ID de départ pour le premier lot"
    )

    parser.add_argument(
        '--num-batches',
        type=int,
        default=5,
        help="Nombre de lots à enrichir (défaut: 5)"
    )

    parser.add_argument(
        '--batch-size',
        type=int,
        default=50,
        help="Taille de chaque lot (défaut: 50)"
    )

    parser.add_argument(
        '--no-validation',
        action='store_true',
        help="Ne pas valider après chaque lot (plus rapide)"
    )

    args = parser.parse_args()

    print_colored("\n" + "="*70, Colors.HEADER)
    print_colored("ENRICHISSEMENT AUTOMATIQUE MULTI-LOTS", Colors.HEADER)
    print_colored("="*70 + "\n", Colors.HEADER)

    print(f"Configuration:")
    print(f"  - ID depart: {args.start_id}")
    print(f"  - Nombre de lots: {args.num_batches}")
    print(f"  - Taille par lot: {args.batch_size}")
    print(f"  - Validation: {'Non' if args.no_validation else 'Oui (apres chaque lot)'}")
    print()

    current_id = args.start_id
    success_count = 0
    failed_batches = []

    for batch_num in range(1, args.num_batches + 1):
        end_id = current_id + args.batch_size - 1

        print_colored(f"\n{'='*70}", Colors.HEADER)
        print_colored(f"LOT {batch_num}/{args.num_batches} - Quiz #{current_id} a #{end_id}", Colors.HEADER)
        print_colored(f"{'='*70}", Colors.HEADER)

        # Enrichissement
        cmd_enrich = [
            'python',
            'dev/tools/quiz/enrich_quizzes_progressive.py',
            str(args.batch_size),
            '--start-id', str(current_id)
        ]

        enrich_success = run_command(
            cmd_enrich,
            f"Enrichissement lot {batch_num} (IDs {current_id}-{end_id})"
        )

        if not enrich_success:
            failed_batches.append(batch_num)
            print_colored(f"\n[WARNING] Lot {batch_num} a echoue, passage au suivant", Colors.WARNING)
            current_id = end_id + 1
            continue

        success_count += 1

        # Validation (sauf si --no-validation)
        if not args.no_validation:
            cmd_validate = [
                'python',
                'dev/tools/quiz/validate_quiz_quality.py',
                '--quiz-dir', 'src/data/quiz',
                '--answers-dir', 'src/data/quiz_answers',
                '--report', 'dev/reports/quiz_quality_report.md'
            ]

            run_command(cmd_validate, f"Validation qualite (lot {batch_num})")

        # Préparer prochain lot
        current_id = end_id + 1

    # Rapport final
    print_colored(f"\n{'='*70}", Colors.HEADER)
    print_colored("RAPPORT FINAL", Colors.HEADER)
    print_colored(f"{'='*70}\n", Colors.HEADER)

    print(f"Lots enrichis avec succes: {success_count}/{args.num_batches}")
    print(f"Quiz enrichis: ~{success_count * args.batch_size}")
    print(f"Plage traitee: #{args.start_id} a #{current_id - 1}")

    if failed_batches:
        print_colored(f"\nLots en echec: {', '.join(map(str, failed_batches))}", Colors.WARNING)
    else:
        print_colored("\nTous les lots ont ete enrichis avec succes !", Colors.OKGREEN)

    if not args.no_validation:
        print(f"\nRapport qualite final: dev/reports/quiz_quality_report.md")

    print()

if __name__ == '__main__':
    main()
