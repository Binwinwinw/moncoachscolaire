#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script de conversion PDF vers PNG avec Python
Utilise pdf2image ou PyMuPDF (fitz) si disponibles
"""

import os
import sys

# Fix encoding pour Windows
if sys.platform == 'win32':
    import codecs
    sys.stdout = codecs.getwriter('utf-8')(sys.stdout.buffer, 'strict')
    sys.stderr = codecs.getwriter('utf-8')(sys.stderr.buffer, 'strict')

pdf_path = os.path.join(os.path.dirname(__file__), '..', 'assets', 'img', 'background-school-material.pdf')
output_path = os.path.join(os.path.dirname(__file__), '..', 'assets', 'img', 'background-school-material.png')

print("=== Conversion PDF vers PNG avec Python ===\n")
print(f"Fichier source : {pdf_path}")
print(f"Fichier de sortie : {output_path}\n")

# Vérifier que le PDF existe
if not os.path.exists(pdf_path):
    print(f"❌ Erreur : Le fichier PDF n'existe pas : {pdf_path}")
    sys.exit(1)

print("✅ Fichier PDF trouvé\n")

# Méthode 1 : PyMuPDF (fitz) - Plus léger et rapide
try:
    import fitz  # PyMuPDF
    print("📦 Tentative avec PyMuPDF (fitz)...")
    
    doc = fitz.open(pdf_path)
    page = doc[0]  # Première page seulement
    
    # Rendu à haute résolution (300 DPI)
    zoom = 300 / 72  # 300 DPI / 72 DPI par défaut
    mat = fitz.Matrix(zoom, zoom)
    pix = page.get_pixmap(matrix=mat)
    
    # Sauvegarder en PNG
    pix.save(output_path)
    doc.close()
    
    if os.path.exists(output_path):
        size = os.path.getsize(output_path)
        print(f"✅ Conversion réussie avec PyMuPDF !")
        print(f"   Fichier créé : {output_path}")
        print(f"   Taille : {round(size / 1024, 2)} KB")
        sys.exit(0)
        
except ImportError:
    print("⚠️  PyMuPDF (fitz) non disponible\n")
except Exception as e:
    print(f"⚠️  Erreur avec PyMuPDF : {e}\n")

# Méthode 2 : pdf2image (nécessite poppler)
try:
    from pdf2image import convert_from_path
    print("📦 Tentative avec pdf2image...")
    
    # Convertir la première page en PNG à 300 DPI
    images = convert_from_path(pdf_path, dpi=300, first_page=1, last_page=1)
    
    if images:
        images[0].save(output_path, 'PNG', quality=95)
        
        if os.path.exists(output_path):
            size = os.path.getsize(output_path)
            print(f"✅ Conversion réussie avec pdf2image !")
            print(f"   Fichier créé : {output_path}")
            print(f"   Taille : {round(size / 1024, 2)} KB")
            sys.exit(0)
            
except ImportError:
    print("⚠️  pdf2image non disponible\n")
except Exception as e:
    print(f"⚠️  Erreur avec pdf2image : {e}\n")

# Si aucune méthode n'a fonctionné
print("\n" + "="*60)
print("❌ Aucune bibliothèque de conversion disponible")
print("="*60 + "\n")
print("📋 INSTALLATION DES BIBLIOTHÈQUES :\n")
print("Option 1 - PyMuPDF (Recommandé, plus simple) :")
print("   pip install PyMuPDF\n")
print("Option 2 - pdf2image (nécessite poppler) :")
print("   pip install pdf2image")
print("   + Installer poppler : https://github.com/oschwartz10612/poppler-windows/releases\n")
print("="*60)
sys.exit(1)

