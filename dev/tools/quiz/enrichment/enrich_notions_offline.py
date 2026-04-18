#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Enrichissement des quiz SANS API - Génère des notions déterministes basées sur les métadonnées
Évite complètement les appels API Groq qui sont limités

Usage:
    python dev/tools/quiz/enrichment/enrich_notions_offline.py
    python dev/tools/quiz/enrichment/enrich_notions_offline.py --dry-run
"""

import json
import os
import sys
from pathlib import Path
from datetime import datetime
from typing import Optional, List, Dict
import argparse


# Template de notions par niveau et matière
NOTION_TEMPLATES = {
    # Collège
    ("6ème", "Français"): [
        "Grammaire et orthographe",
        "Compréhension de texte",
        "Expression écrite"
    ],
    ("6ème", "Mathématiques"): [
        "Opérations arithmétiques",
        "Géométrie élémentaire",
        "Nombres et fractions"
    ],
    ("6ème", "Anglais"): [
        "Vocabulaire de base",
        "Grammaire simple",
        "Expression orale"
    ],
    ("6ème", "SVT"): [
        "Biologie élémentaire",
        "Corps humain",
        "Écosystèmes"
    ],
    ("6ème", "HG"): [
        "Géographie générale",
        "Histoire antique",
        "Repères spatiaux"
    ],

    # 5ème
    ("5ème", "Français"): [
        "Analyse littéraire",
        "Conjugaison et syntaxe",
        "Rédaction structurée"
    ],
    ("5ème", "Mathématiques"): [
        "Nombres relatifs",
        "Équations simples",
        "Géométrie plane"
    ],
    ("5ème", "Anglais"): [
        "Verbes irréguliers",
        "Prétérit et present",
        "Dialogues quotidiens"
    ],

    # 4ème
    ("4ème", "Français"): [
        "Figures de style",
        "Analyse de poèmes",
        "Discours et argumentation"
    ],
    ("4ème", "Mathématiques"): [
        "Pythagorisme et trigonométrie",
        "Fonctions linéaires",
        "Probabilités basiques"
    ],

    # 3ème
    ("3ème", "Français"): [
        "Critique littéraire",
        "Brevet des collèges",
        "Dissertation courte"
    ],
    ("3ème", "Mathématiques"): [
        "Équations du second degré",
        "Fonctions polynomiales",
        "Statistiques et probabilités"
    ],
    ("3ème", "Anglais"): [
        "Expression au past",
        "Propositions conditionnelles",
        "Compréhension orale"
    ],
    ("3ème", "HG"): [
        "Guerres mondiales",
        "Civilisations antiques",
        "Frontières et territoires"
    ],
    ("3ème", "SVT"): [
        "Génétique et reproduction",
        "Évolution des espèces",
        "Organes sensoriels"
    ],

    # Lycée - 2nde
    ("2nde", "Français"): [
        "Mouvements littéraires",
        "Analyse narratologique",
        "Argumentation structurée"
    ],
    ("2nde", "Mathématiques"): [
        "Fonctions polynomiales",
        "Géométrie analytique",
        "Ensembles et logique"
    ],
    ("2nde", "Anglais"): [
        "Littérature anglophone",
        "Phonétique avancée",
        "Expression nuancée"
    ],
    ("2nde", "SVT"): [
        "Cellule et organites",
        "Énergies renouvelables",
        "Biodiversité"
    ],
    ("2nde", "HG"): [
        "Géopolitique mondiale",
        "Migrations humaines",
        "Développement durable"
    ],
    ("2nde", "SES"): [
        "Économie de marché",
        "Sociologie",
        "Sciences politiques"
    ],
    ("2nde", "Physique-Chimie"): [
        "Optique et lumière",
        "État de la matière",
        "Transformations chimiques"
    ],

    # Lycée - 1ère
    ("1ère", "Français"): [
        "Baccalauréat français",
        "Poésie baroque et classique",
        "Théâtre et représentation"
    ],
    ("1ère", "Mathématiques"): [
        "Calcul différentiel",
        "Suites numériques",
        "Probabilités continues"
    ],
    ("1ère", "Anglais"): [
        "Civilisation britannique",
        "Nuances grammaticales",
        "Débat en anglais"
    ],
    ("1ère", "Philosophie"): [
        "Métbysique",
        "Théorie de la connaissance",
        "Éthique"
    ],
    ("1ère", "HG"): [
        "Monde au XVIIIe siècle",
        "Révolutions et réformes",
        "Puissances émergentes"
    ],
    ("1ère", "SVT"): [
        "Génétique moléculaire",
        "Évolution darwinienne",
        "Écologie systémique"
    ],
    ("1ère", "SES"): [
        "Inégalités et redistribution",
        "Croissance économique",
        "Mondialisation"
    ],
    ("1ère", "Physique-Chimie"): [
        "Ondes et oscillations",
        "Propriétés chimiques",
        "Cinétique réactionnelle"
    ],
    ("1ère", "Espagnol"): [
        "Culture hispanique",
        "Subjuntivo en contexte",
        "Littérature castillane"
    ],
    ("1ère", "Allemand"): [
        "Grammaire germanique",
        "Culture germanophone",
        "Textes littéraires"
    ],
    ("1ère", "NSI"): [
        "Algorithmes et complexité",
        "Programmation objet",
        "Données et réseaux"
    ],

    # Lycée - Terminale et BAC
    ("Terminale", "Français"): [
        "Canon littéraire français",
        "Rhétorique et style",
        "Critique et interprétation"
    ],
    ("Terminale", "Mathématiques"): [
        "Intégration et primitives",
        "Logarithme naturel",
        "Espaces vectoriels"
    ],
    ("Terminale", "Anglais"): [
        "Littérature contemporaine",
        "Débat et réfutation",
        "Anglais spécialisé"
    ],
    ("Terminale", "Philosophie"): [
        "Liberté et déterminisme",
        "Justice et droit",
        "Beauté et art"
    ],
    ("Terminale", "HG"): [
        "Monde depuis 1945",
        "Fin de guerre froide",
        "Enjeux géopolitiques modernes"
    ],
    ("Terminale", "SVT"): [
        "Théorie synthétique de l'évolution",
        "Biologie cellulaire moléculaire",
        "Écosystèmes et biosphère"
    ],
    ("Terminale", "SES"): [
        "Croissance et développement",
        "Justice sociale",
        "Intégration économique mondiale"
    ],
    ("Terminale", "Physique-Chimie"): [
        "Mécanique quantique intro",
        "Thermodynamique",
        "Électromagnétisme"
    ],
    ("Terminale", "Espagnol"): [
        "Grandes œuvres espagnoles",
        "Politique et société latino",
        "Presse et média espagnol"
    ],
    ("Terminale", "NSI"): [
        "Intelligence artificielle",
        "Bases de données avancées",
        "Sécurité informatique"
    ],

    # BAC
    ("bac", "Français"): [
        "Analyse stylistique avancée",
        "Défense d'une thèse",
        "Comparaison textuelle"
    ],
    ("bac", "Mathématiques"): [
        "Calcul intégral complet",
        "Fonctions transcendantes",
        "Produit scalaire 3D"
    ],
    ("bac", "Anglais"): [
        "Textes littéraires complets",
        "Civilisation anglo-saxonne",
        "Expression nuancée complexe"
    ],
    ("bac", "Philosophie"): [
        "Anthropologie philosophique",
        "Métaphysique avancée",
        "Systèmes éthiques"
    ],
    ("bac", "HG"): [
        "Puissances et conflits",
        "Espaces et échanges",
        "Réussite et déperdition"
    ],
    ("bac", "SVT"): [
        "Génomique et biotechnologies",
        "Écologie planétaire",
        "Immunologie moléculaire"
    ],
    ("bac", "SES"): [
        "Échanges et flux financiers",
        "Inégalités et développement",
        "Intégration monétaire"
    ],
    ("bac", "Physique-Chimie"): [
        "Électrodynamique quantique",
        "Cinétique chimique avancée",
        "Thermodynamique statistique"
    ],
}


def get_notions_for_quiz(niveau: str, matiere: str) -> List[Dict]:
    """Génère les notions déterministes pour un quiz"""
    key = (niveau.lower(), matiere.lower())

    # Chercher une correspondance exacte d'abord
    for (k_nivel, k_matiere), notions in NOTION_TEMPLATES.items():
        if (k_nivel.lower() == niveau.lower() and
            k_matiere.lower() == matiere.lower()):
            return [
                {"notion": notion, "description": f"Notion fondamentale de {matiere}"}
                for notion in notions[:3]
            ]

    # Fallback: utiliser le niveau seul pour générer quelque chose
    fallback_notions = [
        f"Fondamentaux de {matiere}",
        f"Compétences de base {niveau}",
        f"Savoir-faire {matiere.lower()}"
    ]
    return [
        {"notion": n, "description": f"Notion du programme {niveau}"}
        for n in fallback_notions
    ]


class OfflineEnricher:
    """Enrichissement offline sans API"""

    def __init__(self):
        self.quizzes_dir = Path("src/data/quiz")
        self.log_dir = Path("dev/tmp/enrichment_logs")
        self.log_dir.mkdir(parents=True, exist_ok=True)

        self.stats = {
            "total_processed": 0,
            "total_enriched": 0,
            "total_failed": 0,
            "start_time": datetime.now().isoformat(),
        }

    def enrich_quiz(self, quiz_id: int) -> bool:
        """Enrichit un quiz avec les notions offline"""
        quiz_file = self.quizzes_dir / f"{quiz_id}.json"

        if not quiz_file.exists():
            return False

        try:
            with open(quiz_file, 'r', encoding='utf-8') as f:
                quiz_data = json.load(f)

            # Skip si déjà enrichi
            if quiz_data.get('exercisenotion'):
                return True

            # Extraire niveau et matiere
            contents = quiz_data.get('contents', {})
            quiz = quiz_data.get('quiz', {})

            niveau = contents.get('level') or quiz.get('level') or 'Indéfini'
            matiere = contents.get('subject') or quiz.get('subject') or 'Indéfini'

            # Générer les notions
            notions = get_notions_for_quiz(niveau, matiere)
            if not notions:
                return False

            # Sauvegarder
            quiz_data['exercisenotion'] = notions
            with open(quiz_file, 'w', encoding='utf-8') as f:
                json.dump(quiz_data, f, ensure_ascii=False, indent=2)

            self.stats["total_enriched"] += 1
            return True

        except Exception as e:
            print(f"Error {quiz_id}: {e}")
            self.stats["total_failed"] += 1
            return False

    def run(self, dry_run: bool = False) -> None:
        """Lance l'enrichissement complet"""

        # Identifier les quizzes manquants
        missing_ids = []
        for f in sorted(os.listdir(self.quizzes_dir)):
            if not f.endswith('.json'):
                continue
            try:
                quiz_id = int(f.replace('.json', ''))
                with open(self.quizzes_dir / f, 'r', encoding='utf-8') as file:
                    data = json.load(file)
                    if not data.get('exercisenotion'):
                        missing_ids.append(quiz_id)
            except:
                continue

        print(f"Total quizzes sans notions: {len(missing_ids)}")

        if dry_run:
            print(f"[DRY RUN] Traiterait {len(missing_ids)} quizzes")
            return

        # Traiter tous les quizzes
        for i, quiz_id in enumerate(missing_ids, 1):
            self.stats["total_processed"] += 1
            if self.enrich_quiz(quiz_id):
                if i % 100 == 0:
                    print(f"  [{i:4d}/{len(missing_ids)}] Enrichis: {self.stats['total_enriched']}")

        # Sauvegarder le rapport
        self.stats["end_time"] = datetime.now().isoformat()
        report_file = self.log_dir / f"enrichment_offline_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(self.stats, f, ensure_ascii=False, indent=2)

        print(f"\nRapport d'enrichissement:")
        print(f"  Traites: {self.stats['total_processed']}")
        print(f"  Enrichis: {self.stats['total_enriched']}")
        print(f"  Echoues: {self.stats['total_failed']}")
        print(f"  Rapport: {report_file}")


def main():
    parser = argparse.ArgumentParser(description="Enrichir les quiz OFFLINE (sans API)")
    parser.add_argument("--dry-run", action='store_true', help="Dry run mode")
    args = parser.parse_args()

    try:
        enricher = OfflineEnricher()
        enricher.run(dry_run=args.dry_run)
    except Exception as e:
        print(f"Erreur: {e}")
        sys.exit(1)


if __name__ == "__main__":
    main()
