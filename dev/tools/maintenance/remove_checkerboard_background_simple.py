#!/usr/bin/env python3
"""
Script pour supprimer l'arrière-plan en damier - Version Simple
Supprime uniquement les bords (méthode la moins agressive)
Ne nécessite que Pillow (pas NumPy)
"""

import os
import sys
from PIL import Image
import argparse

def detect_checkerboard(img, edge_margin=0.1, threshold=15):
    """
    Détecte si l'image a un damier en arrière-plan
    Retourne True si un damier est détecté, False sinon
    """
    width, height = img.size
    
    # Calculer les marges (en pixels)
    margin_x = int(width * edge_margin)
    margin_y = int(height * edge_margin)
    
    # Couleurs du damier
    checkerboard_colors = [
        (255, 255, 255),  # Blanc pur
        (250, 250, 250),  # Blanc légèrement grisé
        (245, 245, 245),  # Gris très clair
        (240, 240, 240),  # Gris clair
        (220, 220, 220),  # Gris moyen-clair
    ]
    
    pixels = img.load()
    
    # Analyser les bords pour détecter un pattern de damier
    checkerboard_pixels = 0
    total_edge_pixels = 0
    
    # Échantillonner les bords (pas tous les pixels pour être plus rapide)
    sample_rate = max(1, min(width, height) // 50)  # Échantillonner environ 50 points par dimension
    
    for y in range(0, height, sample_rate):
        for x in range(0, width, sample_rate):
            # Vérifier si on est dans la zone des bords
            is_near_edge = (x < margin_x or x >= width - margin_x or 
                           y < margin_y or y >= height - margin_y)
            
            if not is_near_edge:
                continue
            
            total_edge_pixels += 1
            r, g, b, a = pixels[x, y]
            
            # Si déjà transparent, ignorer
            if a == 0:
                continue
            
            # Vérifier si le pixel correspond au damier
            for checker_color in checkerboard_colors:
                if (abs(r - checker_color[0]) <= threshold and
                    abs(g - checker_color[1]) <= threshold and
                    abs(b - checker_color[2]) <= threshold):
                    checkerboard_pixels += 1
                    break
    
    if total_edge_pixels == 0:
        return False, 0
    
    # Calculer le pourcentage de pixels de damier sur les bords
    checkerboard_percentage = (checkerboard_pixels / total_edge_pixels) * 100
    
    # Si plus de 30% des bords sont du damier, on considère qu'il y a un damier
    has_checkerboard = checkerboard_percentage > 30
    
    return has_checkerboard, checkerboard_percentage

def remove_checkerboard_simple(input_path, output_path=None, threshold=15, edge_margin=0.1, force=False):
    """
    Supprime l'arrière-plan en damier uniquement sur les bords
    
    Args:
        input_path: Chemin vers l'image d'entrée
        output_path: Chemin vers l'image de sortie
        threshold: Seuil de tolérance (plus bas = plus conservateur)
        edge_margin: Pourcentage de marge sur les bords (0.1 = 10%)
    """
    try:
        # Ouvrir l'image
        img = Image.open(input_path)
        
        # Convertir en RGBA si nécessaire
        if img.mode != 'RGBA':
            img = img.convert('RGBA')
        
        # Détecter si l'image a un damier (sauf si force=True)
        if not force:
            has_checkerboard, percentage = detect_checkerboard(img, edge_margin, threshold)
            if not has_checkerboard:
                return None, f"Aucun damier detecte (seulement {percentage:.1f}% de pixels de damier sur les bords, seuil: 30%)"
        
        width, height = img.size
        
        # Couleurs du damier (blanc et gris clairs)
        checkerboard_colors = [
            (255, 255, 255),  # Blanc pur
            (250, 250, 250),  # Blanc légèrement grisé
            (245, 245, 245),  # Gris très clair
            (240, 240, 240),  # Gris clair
        ]
        
        # Calculer les marges (en pixels)
        margin_x = int(width * edge_margin)
        margin_y = int(height * edge_margin)
        
        # Créer une nouvelle image avec transparence
        pixels = img.load()
        
        # Parcourir uniquement les pixels des bords
        removed_count = 0
        total_edge_pixels = 0
        
        for y in range(height):
            for x in range(width):
                # Vérifier si on est dans la zone des bords
                is_near_edge = (x < margin_x or x >= width - margin_x or 
                               y < margin_y or y >= height - margin_y)
                
                if not is_near_edge:
                    continue  # Ignorer le centre
                
                total_edge_pixels += 1
                r, g, b, a = pixels[x, y]
                
                # Si déjà transparent, ignorer
                if a == 0:
                    continue
                
                # Vérifier si le pixel correspond au damier
                should_remove = False
                for checker_color in checkerboard_colors:
                    if (abs(r - checker_color[0]) <= threshold and
                        abs(g - checker_color[1]) <= threshold and
                        abs(b - checker_color[2]) <= threshold):
                        should_remove = True
                        break
                
                if should_remove:
                    pixels[x, y] = (r, g, b, 0)  # Alpha = 0 (transparent)
                    removed_count += 1
        
        # Déterminer le chemin de sortie
        if output_path is None:
            base_name = os.path.splitext(input_path)[0]
            extension = os.path.splitext(input_path)[1]
            output_path = f"{base_name}_no_bg{extension}"
        
        # Sauvegarder l'image
        img.save(output_path, 'PNG')
        
        if total_edge_pixels > 0:
            percentage = (removed_count / total_edge_pixels) * 100
            print(f"[OK] Traite: {os.path.basename(input_path)} -> {os.path.basename(output_path)}")
            print(f"     Pixels supprimes (bords): {removed_count}/{total_edge_pixels} ({percentage:.1f}%)")
        else:
            print(f"[OK] Traite: {os.path.basename(input_path)} -> {os.path.basename(output_path)}")
        
        return True, None
        
    except Exception as e:
        print(f"[ERREUR] Erreur avec {os.path.basename(input_path)}: {str(e)}")
        return False, str(e)

def main():
    parser = argparse.ArgumentParser(
        description='Supprime l\'arrière-plan en damier - Version Simple (bords uniquement)'
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
        '-t', '--threshold',
        type=int,
        default=15,
        help='Seuil de tolérance (5-30, plus bas = plus conservateur, défaut: 15)'
    )
    parser.add_argument(
        '-m', '--margin',
        type=float,
        default=0.1,
        help='Marge des bords en pourcentage (0.05-0.2, défaut: 0.1 = 10%%)'
    )
    parser.add_argument(
        '--force',
        action='store_true',
        help='Forcer le traitement même si aucun damier n\'est détecté'
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
            output_path = f"{base_name}_no_bg{extension}"
        
        print(f"Fichier d'entree: {os.path.basename(input_path)}")
        print(f"Fichier de sortie: {os.path.basename(output_path)}")
        print(f"Seuil: {args.threshold}, Marge bords: {args.margin*100:.0f}%")
        print("IMPORTANT: L'original ne sera PAS modifie")
        print("Methode: Suppression uniquement des bords (centre preserve)")
        print("")
        
        # Détecter d'abord si l'image a un damier
        img = Image.open(input_path)
        if img.mode != 'RGBA':
            img = img.convert('RGBA')
        
        if not args.force:
            has_checkerboard, percentage = detect_checkerboard(img, args.margin, args.threshold)
            if not has_checkerboard:
                print(f"[SKIP] Aucun damier detecte dans {os.path.basename(input_path)}")
                print(f"       Pourcentage de damier sur les bords: {percentage:.1f}% (seuil: 30%)")
                print(f"       Utilisez --force pour traiter quand meme")
                sys.exit(0)
            else:
                print(f"[OK] Damier detecte: {percentage:.1f}% sur les bords")
                print("")
        
        result, message = remove_checkerboard_simple(input_path, output_path, args.threshold, args.margin, args.force)
        
        if result is False:
            print(f"[ERREUR] {message}")
            sys.exit(1)
        elif result is None:
            print(f"[SKIP] {message}")
            sys.exit(0)
        else:
            print(f"[OK] Nouveau fichier cree: {os.path.basename(output_path)}")
            print("[OK] Termine avec succes!")
    
    elif os.path.isdir(input_path):
        png_files = [f for f in os.listdir(input_path) 
                    if f.lower().endswith('.png') 
                    and not f.endswith('_no_bg.png')]
        
        if not png_files:
            print(f"Aucun fichier PNG trouvé dans {input_path}")
            return
        
        print(f"Traitement de {len(png_files)} fichier(s) PNG...")
        print(f"Seuil: {args.threshold}, Marge bords: {args.margin*100:.0f}%")
        print("IMPORTANT: Les originaux ne seront PAS modifies")
        print("Methode: Suppression uniquement des bords")
        print("")
        
        success_count = 0
        skipped_count = 0
        
        for filename in png_files:
            file_path = os.path.join(input_path, filename)
            base_name = os.path.splitext(filename)[0]
            extension = os.path.splitext(filename)[1]
            output_filename = f"{base_name}_no_bg{extension}"
            output_file_path = os.path.join(input_path, output_filename)
            
            if os.path.exists(output_file_path):
                print(f"[SKIP] {output_filename} existe deja")
                continue
            
            # Détecter si l'image a un damier
            img = Image.open(file_path)
            if img.mode != 'RGBA':
                img = img.convert('RGBA')
            
            if not args.force:
                has_checkerboard, percentage = detect_checkerboard(img, args.margin, args.threshold)
                if not has_checkerboard:
                    print(f"[SKIP] {filename}: Aucun damier detecte ({percentage:.1f}%)")
                    skipped_count += 1
                    continue
                else:
                    print(f"[OK] {filename}: Damier detecte ({percentage:.1f}%)")
            
            result, message = remove_checkerboard_simple(file_path, output_file_path, args.threshold, args.margin, args.force)
            
            if result is True:
                success_count += 1
            elif result is None:
                print(f"[SKIP] {filename}: {message}")
                skipped_count += 1
        
        print("")
        print(f"Termine: {success_count} traite(s), {skipped_count} ignore(s) (pas de damier), {len(png_files) - success_count - skipped_count} erreur(s)")
    else:
        print(f"Erreur: {input_path} n'est ni un fichier ni un dossier")
        sys.exit(1)

if __name__ == '__main__':
    main()
