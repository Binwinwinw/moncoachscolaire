#!/usr/bin/env python3
"""
Script pour supprimer l'arrière-plan en damier des images PNG
Le damier est généralement composé de carrés blancs et gris clairs alternés
"""

import os
import sys
from PIL import Image
import argparse

def is_checkerboard_pixel(r, g, b, a, checkerboard_colors):
    """
    Vérifie si un pixel correspond à une couleur du damier
    """
    if a == 0:  # Déjà transparent
        return True
    
    # Vérifier si le pixel correspond à une couleur du damier
    for checker_color in checkerboard_colors:
        # Tolérance pour les variations de couleur
        tolerance = 10
        if (abs(r - checker_color[0]) <= tolerance and
            abs(g - checker_color[1]) <= tolerance and
            abs(b - checker_color[2]) <= tolerance):
            return True
    return False

def remove_checkerboard_background(input_path, output_path=None, method='auto'):
    """
    Supprime l'arrière-plan en damier d'une image PNG
    
    Args:
        input_path: Chemin vers l'image d'entrée
        output_path: Chemin vers l'image de sortie (si None, remplace l'original)
        method: Méthode de détection ('auto', 'white', 'gray', 'both')
    """
    try:
        # Ouvrir l'image
        img = Image.open(input_path)
        
        # Convertir en RGBA si nécessaire
        if img.mode != 'RGBA':
            img = img.convert('RGBA')
        
        # Définir les couleurs du damier selon la méthode
        checkerboard_colors = []
        
        if method == 'auto' or method == 'white':
            # Blanc pur (255, 255, 255)
            checkerboard_colors.append((255, 255, 255))
            # Blanc légèrement grisé
            checkerboard_colors.append((250, 250, 250))
            checkerboard_colors.append((245, 245, 245))
        
        if method == 'auto' or method == 'gray':
            # Gris clair typique du damier
            checkerboard_colors.append((200, 200, 200))
            checkerboard_colors.append((220, 220, 220))
            checkerboard_colors.append((230, 230, 230))
            checkerboard_colors.append((240, 240, 240))
        
        if method == 'both':
            checkerboard_colors.append((255, 255, 255))
            checkerboard_colors.append((200, 200, 200))
            checkerboard_colors.append((220, 220, 220))
        
        # Créer une nouvelle image avec transparence
        pixels = img.load()
        width, height = img.size
        
        # Parcourir tous les pixels
        for y in range(height):
            for x in range(width):
                r, g, b, a = pixels[x, y]
                
                # Si le pixel correspond au damier, le rendre transparent
                if is_checkerboard_pixel(r, g, b, a, checkerboard_colors):
                    pixels[x, y] = (r, g, b, 0)  # Alpha = 0 (transparent)
        
        # Déterminer le chemin de sortie
        # IMPORTANT: Ne jamais modifier l'original, toujours créer un nouveau fichier
        if output_path is None:
            # Créer un nouveau fichier avec suffixe _no_bg
            base_name = os.path.splitext(input_path)[0]
            extension = os.path.splitext(input_path)[1]
            output_path = f"{base_name}_no_bg{extension}"
        
        # Sauvegarder l'image dans le nouveau fichier
        img.save(output_path, 'PNG')
        print(f"[OK] Traite: {os.path.basename(input_path)} -> {os.path.basename(output_path)}")
        return True
        
    except Exception as e:
        print(f"[ERREUR] Erreur avec {os.path.basename(input_path)}: {str(e)}")
        return False

def process_directory(directory, method='auto', backup=True):
    """
    Traite tous les fichiers PNG d'un répertoire
    Crée de nouveaux fichiers avec suffixe _no_bg au lieu de modifier les originaux
    """
    png_files = [f for f in os.listdir(directory) if f.lower().endswith('.png') and not f.endswith('_no_bg.png')]
    
    if not png_files:
        print(f"Aucun fichier PNG trouvé dans {directory}")
        return
    
    print(f"Traitement de {len(png_files)} fichier(s) PNG...")
    print(f"Méthode: {method}")
    print("IMPORTANT: Les originaux ne seront PAS modifiés, de nouveaux fichiers seront créés")
    print("")
    
    success_count = 0
    
    for filename in png_files:
        input_path = os.path.join(directory, filename)
        
        # Créer un nouveau fichier avec suffixe _no_bg
        base_name = os.path.splitext(filename)[0]
        extension = os.path.splitext(filename)[1]
        output_filename = f"{base_name}_no_bg{extension}"
        output_path = os.path.join(directory, output_filename)
        
        # Ne pas traiter si le fichier de sortie existe déjà
        if os.path.exists(output_path):
            print(f"[SKIP] {output_filename} existe déjà, ignoré")
            continue
        
        if remove_checkerboard_background(input_path, output_path, method=method):
            success_count += 1
    
    print("")
    print(f"Terminé: {success_count}/{len(png_files)} fichier(s) traité(s) avec succès")

def main():
    parser = argparse.ArgumentParser(
        description='Supprime l\'arrière-plan en damier des images PNG'
    )
    parser.add_argument(
        'input',
        help='Fichier PNG ou dossier contenant des PNG'
    )
    parser.add_argument(
        '-o', '--output',
        help='Fichier de sortie (si non spécifié, crée un nouveau fichier avec suffixe _no_bg)'
    )
    parser.add_argument(
        '-m', '--method',
        choices=['auto', 'white', 'gray', 'both'],
        default='auto',
        help='Méthode de détection: auto (détecte blanc et gris), white (blanc uniquement), gray (gris uniquement), both (blanc et gris)'
    )
    parser.add_argument(
        '--no-backup',
        action='store_true',
        help='(Obsolète - les originaux ne sont plus modifiés, donc pas besoin de backup)'
    )
    
    args = parser.parse_args()
    
    input_path = args.input
    
    if not os.path.exists(input_path):
        print(f"Erreur: {input_path} n'existe pas")
        sys.exit(1)
    
    if os.path.isfile(input_path):
        # Traiter un seul fichier
        if not input_path.lower().endswith('.png'):
            print("Erreur: Le fichier doit être un PNG")
            sys.exit(1)
        
        # Déterminer le chemin de sortie
        if args.output:
            output_path = args.output
        else:
            # Créer un nouveau fichier avec suffixe _no_bg
            base_name = os.path.splitext(input_path)[0]
            extension = os.path.splitext(input_path)[1]
            output_path = f"{base_name}_no_bg{extension}"
        
        print(f"Fichier d'entree: {os.path.basename(input_path)}")
        print(f"Fichier de sortie: {os.path.basename(output_path)}")
        print("IMPORTANT: L'original ne sera PAS modifie")
        print("")
        
        if remove_checkerboard_background(input_path, output_path, args.method):
            print(f"[OK] Nouveau fichier cree: {os.path.basename(output_path)}")
            print("[OK] Termine avec succes!")
        else:
            sys.exit(1)
    
    elif os.path.isdir(input_path):
        # Traiter un dossier
        process_directory(input_path, args.method, backup=not args.no_backup)
    else:
        print(f"Erreur: {input_path} n'est ni un fichier ni un dossier")
        sys.exit(1)

if __name__ == '__main__':
    main()
