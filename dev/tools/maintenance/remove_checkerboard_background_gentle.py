#!/usr/bin/env python3
"""
Script pour supprimer l'arrière-plan en damier des images PNG - Version Douce
Utilise des méthodes plus précises et moins agressives
"""

import os
import sys
from PIL import Image
import argparse
import numpy as np

def detect_checkerboard_pattern(img_array, x, y, window_size=5):
    """
    Détecte si un pixel fait partie d'un pattern de damier
    en analysant son voisinage
    """
    height, width = img_array.shape[:2]
    
    # Vérifier les bords (plus susceptibles d'être du damier)
    margin = min(width, height) * 0.1  # 10% de marge
    is_near_edge = (x < margin or x > width - margin or 
                   y < margin or y > height - margin)
    
    if not is_near_edge:
        return False  # Au centre, on est plus conservateur
    
    # Analyser le voisinage pour détecter un pattern alterné
    half_window = window_size // 2
    x_start = max(0, x - half_window)
    x_end = min(width, x + half_window + 1)
    y_start = max(0, y - half_window)
    y_end = min(height, y + half_window + 1)
    
    region = img_array[y_start:y_end, x_start:x_end]
    
    if len(region) == 0:
        return False
    
    # Calculer la variance - un damier a une variance élevée
    if len(region.shape) == 3:
        gray = np.mean(region, axis=2)
    else:
        gray = region
    
    variance = np.var(gray)
    
    # Un damier a une variance élevée (alternance blanc/gris)
    # Mais on veut éviter les zones avec beaucoup de détails
    return variance > 500 and variance < 5000

def remove_checkerboard_gentle(input_path, output_path=None, method='edge', threshold=15):
    """
    Supprime l'arrière-plan en damier avec une méthode douce
    
    Args:
        input_path: Chemin vers l'image d'entrée
        output_path: Chemin vers l'image de sortie
        method: 'edge' (bords uniquement), 'smart' (détection intelligente), 'conservative' (très conservateur)
        threshold: Seuil de tolérance (plus bas = plus conservateur)
    """
    try:
        # Ouvrir l'image
        img = Image.open(input_path)
        
        # Convertir en RGBA si nécessaire
        if img.mode != 'RGBA':
            img = img.convert('RGBA')
        
        # Convertir en numpy array pour traitement
        img_array = np.array(img)
        height, width = img_array.shape[:2]
        
        # Couleurs du damier (plus restrictives)
        checkerboard_colors = []
        
        if method == 'edge':
            # Méthode 1: Supprimer uniquement les bords (10% de chaque côté)
            margin = min(width, height) * 0.1
            checkerboard_colors = [
                (255, 255, 255),  # Blanc pur
                (240, 240, 240),  # Gris très clair
                (220, 220, 220),  # Gris clair
            ]
        elif method == 'smart':
            # Méthode 2: Détection intelligente avec pattern
            checkerboard_colors = [
                (255, 255, 255),
                (250, 250, 250),
                (245, 245, 245),
            ]
        else:  # conservative
            # Méthode 3: Très conservateur - uniquement blanc pur
            checkerboard_colors = [
                (255, 255, 255),
            ]
            threshold = 5  # Tolérance très faible
        
        # Créer une nouvelle image avec transparence
        pixels = img.load()
        
        # Parcourir tous les pixels
        removed_count = 0
        total_pixels = width * height
        
        for y in range(height):
            for x in range(width):
                r, g, b, a = pixels[x, y]
                
                # Si déjà transparent, ignorer
                if a == 0:
                    continue
                
                # Vérifier si le pixel correspond au damier
                should_remove = False
                
                if method == 'edge':
                    # Supprimer uniquement si proche des bords
                    margin = min(width, height) * 0.1
                    is_near_edge = (x < margin or x > width - margin or 
                                   y < margin or y > height - margin)
                    
                    if is_near_edge:
                        for checker_color in checkerboard_colors:
                            if (abs(r - checker_color[0]) <= threshold and
                                abs(g - checker_color[1]) <= threshold and
                                abs(b - checker_color[2]) <= threshold):
                                should_remove = True
                                break
                
                elif method == 'smart':
                    # Détection intelligente avec pattern
                    is_checkerboard = detect_checkerboard_pattern(img_array, x, y)
                    if is_checkerboard:
                        for checker_color in checkerboard_colors:
                            if (abs(r - checker_color[0]) <= threshold and
                                abs(g - checker_color[1]) <= threshold and
                                abs(b - checker_color[2]) <= threshold):
                                should_remove = True
                                break
                
                else:  # conservative
                    # Très conservateur - uniquement blanc pur exact
                    if r == 255 and g == 255 and b == 255:
                        # Vérifier si c'est vraiment en bordure
                        margin = min(width, height) * 0.15
                        is_near_edge = (x < margin or x > width - margin or 
                                       y < margin or y > height - margin)
                        if is_near_edge:
                            should_remove = True
                
                if should_remove:
                    pixels[x, y] = (r, g, b, 0)  # Alpha = 0 (transparent)
                    removed_count += 1
        
        # Déterminer le chemin de sortie
        if output_path is None:
            base_name = os.path.splitext(input_path)[0]
            extension = os.path.splitext(input_path)[1]
            method_suffix = f"_no_bg_{method}"
            output_path = f"{base_name}{method_suffix}{extension}"
        
        # Sauvegarder l'image
        img.save(output_path, 'PNG')
        
        percentage = (removed_count / total_pixels) * 100
        print(f"[OK] Traite: {os.path.basename(input_path)} -> {os.path.basename(output_path)}")
        print(f"     Pixels supprimes: {removed_count}/{total_pixels} ({percentage:.1f}%)")
        return True
        
    except Exception as e:
        print(f"[ERREUR] Erreur avec {os.path.basename(input_path)}: {str(e)}")
        return False

def main():
    parser = argparse.ArgumentParser(
        description='Supprime l\'arrière-plan en damier des images PNG - Version Douce'
    )
    parser.add_argument(
        'input',
        help='Fichier PNG ou dossier contenant des PNG'
    )
    parser.add_argument(
        '-o', '--output',
        help='Fichier de sortie (si non spécifié, crée un nouveau fichier avec suffixe _no_bg_[method])'
    )
    parser.add_argument(
        '-m', '--method',
        choices=['edge', 'smart', 'conservative'],
        default='edge',
        help='Méthode: edge (bords uniquement, recommandé), smart (détection intelligente), conservative (très conservateur)'
    )
    parser.add_argument(
        '-t', '--threshold',
        type=int,
        default=15,
        help='Seuil de tolérance (5-30, plus bas = plus conservateur, défaut: 15)'
    )
    
    args = parser.parse_args()
    
    input_path = args.input
    
    if not os.path.exists(input_path):
        print(f"Erreur: {input_path} n'existe pas")
        sys.exit(1)
    
    if os.path.isfile(input_path):
        if not input_path.lower().endswith('.png'):
            print("Erreur: Le fichier doit être un PNG")
            sys.exit(1)
        
        if args.output:
            output_path = args.output
        else:
            base_name = os.path.splitext(input_path)[0]
            extension = os.path.splitext(input_path)[1]
            output_path = f"{base_name}_no_bg_{args.method}{extension}"
        
        print(f"Fichier d'entree: {os.path.basename(input_path)}")
        print(f"Fichier de sortie: {os.path.basename(output_path)}")
        print(f"Methode: {args.method} (seuil: {args.threshold})")
        print("IMPORTANT: L'original ne sera PAS modifie")
        print("")
        
        if remove_checkerboard_gentle(input_path, output_path, args.method, args.threshold):
            print(f"[OK] Nouveau fichier cree: {os.path.basename(output_path)}")
            print("[OK] Termine avec succes!")
        else:
            sys.exit(1)
    
    elif os.path.isdir(input_path):
        png_files = [f for f in os.listdir(input_path) 
                    if f.lower().endswith('.png') 
                    and not f.endswith('_no_bg_')]
        
        if not png_files:
            print(f"Aucun fichier PNG trouvé dans {input_path}")
            return
        
        print(f"Traitement de {len(png_files)} fichier(s) PNG...")
        print(f"Methode: {args.method} (seuil: {args.threshold})")
        print("IMPORTANT: Les originaux ne seront PAS modifies")
        print("")
        
        success_count = 0
        for filename in png_files:
            file_path = os.path.join(input_path, filename)
            base_name = os.path.splitext(filename)[0]
            extension = os.path.splitext(filename)[1]
            output_filename = f"{base_name}_no_bg_{args.method}{extension}"
            output_file_path = os.path.join(input_path, output_filename)
            
            if os.path.exists(output_file_path):
                print(f"[SKIP] {output_filename} existe deja")
                continue
            
            if remove_checkerboard_gentle(file_path, output_file_path, args.method, args.threshold):
                success_count += 1
        
        print("")
        print(f"Termine: {success_count}/{len(png_files)} fichier(s) traite(s) avec succes")
    else:
        print(f"Erreur: {input_path} n'est ni un fichier ni un dossier")
        sys.exit(1)

if __name__ == '__main__':
    main()
