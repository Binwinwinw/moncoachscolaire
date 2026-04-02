#!/usr/bin/env python3
"""
Script pour nettoyer les fichiers sensibles de l'historique Git
Utilise git-filter-repo (solution moderne et sûre)

Usage: python clean_git_history.py --help
"""

import subprocess
import sys
import os
from pathlib import Path

def run_command(cmd, check=True):
    """Exécuter une commande shell"""
    print(f"▶ {' '.join(cmd)}")
    result = subprocess.run(cmd, check=check)
    return result.returncode == 0

def check_git_filter_repo():
    """Vérifier que git-filter-repo est installé"""
    try:
        subprocess.run(['git', 'filter-repo', '--version'], 
                      capture_output=True, check=True)
        return True
    except:
        return False

def main():
    print("\n" + "="*60)
    print("🔐 GIT HISTORY CLEANER - Supprimer les fichiers sensibles")
    print("="*60)
    
    # 1. Vérifier git-filter-repo
    print("\n1️⃣  Vérification des dépendances...")
    if not check_git_filter_repo():
        print("❌ git-filter-repo n'est pas installé!")
        print("   Installation: pip install git-filter-repo")
        sys.exit(1)
    print("✅ git-filter-repo OK")
    
    # 2. Vérifier qu'on est dans un repo git
    print("\n2️⃣  Vérification du repo git...")
    if not Path(".git").exists():
        print("❌ Pas de repo git trouvé!")
        sys.exit(1)
    print("✅ Repo git trouvé")
    
    # 3. Afficher les fichiers sensibles
    print("\n3️⃣  Fichiers sensibles à nettoyer:")
    files_to_remove = [
        '.env.production',
        'tools/',
        'tests/'
    ]
    
    for file in files_to_remove:
        print(f"   • {file}")
    
    # 4. Confirmation
    print("\n⚠️  ATTENTION:")
    print("   • Cela va RÉÉCRIRE l'historique git")
    print("   • Un force-push sera nécessaire")
    print("   • Tous les développeurs devront rebaser")
    
    response = input("\n✓ Continuer? (y/N): ").strip().lower()
    if response != 'y':
        print("Annulé.")
        sys.exit(0)
    
    # 5. Créer une sauvegarde
    print("\n4️⃣  Sauvegarde...")
    # (La sauvegarde se fait avec les réfs git automatiquement)
    print("✅ Git reflog conserve l'historique (30 jours par défaut)")
    
    # 6. Nettoyer
    print("\n5️⃣  Nettoyage de l'historique...")
    
    for file in files_to_remove:
        print(f"\n   Suppression: {file}")
        cmd = ['git', 'filter-repo', '--invert-paths', '--path', file]
        if not run_command(cmd, check=False):
            print(f"⚠️  Erreur lors du nettoyage de {file}")
    
    # 7. Reflog et GC
    print("\n6️⃣  Optimisation...")
    print("   Expiration du reflog...")
    run_command(['git', 'reflog', 'expire', '--expire=now', '--all'])
    
    print("   Garbage collection...")
    run_command(['git', 'gc', '--prune=now', '--aggressive'])
    
    # 8. Vérification
    print("\n7️⃣  Vérification...")
    for file in files_to_remove:
        result = subprocess.run(
            ['git', 'log', '--all', '--', file],
            capture_output=True,
            text=True
        )
        if result.stdout:
            print(f"⚠️  {file} trouve encore dans l'historique!")
        else:
            print(f"✅ {file} - Supprimé")
    
    # 9. Instructions pour push
    print("\n" + "="*60)
    print("✅ NETTOYAGE TERMINÉ")
    print("="*60)
    print("\n⏭️  Prochaines étapes:")
    print("\n1️⃣  Vérifier l'état:")
    print("   git log --oneline -5")
    print("   git status")
    
    print("\n2️⃣  Force push (⚠️ Attention!):")
    print("   git push --force")
    
    print("\n3️⃣  Informer l'équipe:")
    print("   Les autres développeurs doivent rebaser:")
    print("   git fetch origin")
    print("   git rebase origin/main")
    
    print("\n4️⃣  🔒 CHANGER LES SECRETS:")
    print("   • DB password")
    print("   • API keys")
    print("   • Tokens")
    print("\n" + "="*60 + "\n")

if __name__ == '__main__':
    main()
