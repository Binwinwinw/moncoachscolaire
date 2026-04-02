"""
Convert PDF to HTML via DOCX intermediate format for better fidelity.

Process:
1. PDF → DOCX using PyMuPDF (preserves layout and structure)
2. DOCX → HTML using python-docx + custom converter

Usage:
    python tools/convert_pdf_via_docx.py "<input.pdf>" --output "<output.html>"
    
Functions:
    convert_pdf_to_docx(pdf_path, docx_path=None) -> str
    convert_docx_to_html(docx_path, html_path=None) -> str
    convert_pdf_to_html_via_docx(pdf_path, html_path=None) -> str
"""
from __future__ import annotations
import os
import sys
import argparse
from typing import Optional
import html as html_module


def convert_pdf_to_docx(pdf_path: str, docx_path: Optional[str] = None) -> str:
    """Convert PDF to DOCX using pdf2docx."""
    try:
        from pdf2docx import Converter
    except ImportError:
        raise RuntimeError("pdf2docx is not installed")
    
    if not os.path.isfile(pdf_path):
        raise FileNotFoundError(f"PDF not found: {pdf_path}")
    
    if docx_path is None:
        base, _ = os.path.splitext(pdf_path)
        docx_path = base + ".docx"
    
    os.makedirs(os.path.dirname(docx_path) or ".", exist_ok=True)
    
    # Convert PDF to DOCX
    cv = Converter(pdf_path)
    cv.convert(docx_path, start=0, end=None)
    cv.close()
    
    return docx_path


def convert_docx_to_html(docx_path: str, html_path: Optional[str] = None) -> str:
    """Convert DOCX to HTML using python-docx."""
    try:
        from docx import Document
    except ImportError:
        raise RuntimeError("python-docx is not installed")
    
    if not os.path.isfile(docx_path):
        raise FileNotFoundError(f"DOCX not found: {docx_path}")
    
    if html_path is None:
        base, _ = os.path.splitext(docx_path)
        html_path = base + ".html"
    
    os.makedirs(os.path.dirname(html_path) or ".", exist_ok=True)
    
    doc = Document(docx_path)
    html_parts = []
    
    # Process each paragraph
    for para in doc.paragraphs:
        text = html_module.escape(para.text)
        if not text.strip():
            continue
        
        # Check paragraph style
        style_name = para.style.name.lower() if para.style else ""
        
        if "heading 1" in style_name or "titre 1" in style_name:
            html_parts.append(f"<h1>{text}</h1>")
        elif "heading 2" in style_name or "titre 2" in style_name:
            html_parts.append(f"<h2>{text}</h2>")
        elif "heading 3" in style_name or "titre 3" in style_name:
            html_parts.append(f"<h3>{text}</h3>")
        elif "heading" in style_name or "titre" in style_name:
            html_parts.append(f"<h4>{text}</h4>")
        else:
            html_parts.append(f"<p>{text}</p>")
    
    # Process tables
    for table in doc.tables:
        html_parts.append("<table>")
        for row in table.rows:
            html_parts.append("<tr>")
            for cell in row.cells:
                cell_text = html_module.escape(cell.text)
                html_parts.append(f"<td>{cell_text}</td>")
            html_parts.append("</tr>")
        html_parts.append("</table>")
    
    # Build final HTML
    html_output = f"""<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Document converti</title>
  <style>
    body {{
      margin: 2rem auto;
      max-width: 960px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      line-height: 1.6;
      color: #333;
    }}
    h1, h2, h3, h4 {{
      color: #2c3e50;
      margin-top: 1.5em;
      margin-bottom: 0.5em;
    }}
    h1 {{ font-size: 2em; border-bottom: 2px solid #3498db; padding-bottom: 0.3em; }}
    h2 {{ font-size: 1.5em; border-bottom: 1px solid #bdc3c7; padding-bottom: 0.2em; }}
    h3 {{ font-size: 1.25em; }}
    p {{ margin: 0.5em 0; }}
    table {{
      border-collapse: collapse;
      width: 100%;
      margin: 1em 0;
    }}
    td, th {{
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }}
    tr:nth-child(even) {{ background-color: #f2f2f2; }}
  </style>
</head>
<body>
{chr(10).join(html_parts)}
</body>
</html>"""
    
    with open(html_path, "w", encoding="utf-8") as f:
        f.write(html_output)
    
    return html_path


def convert_pdf_to_html_via_docx(pdf_path: str, html_path: Optional[str] = None, keep_docx: bool = False) -> str:
    """Convert PDF to HTML via DOCX intermediate format."""
    if not os.path.isfile(pdf_path):
        raise FileNotFoundError(f"PDF not found: {pdf_path}")
    
    # Step 1: PDF → DOCX
    base, _ = os.path.splitext(pdf_path)
    docx_path = base + "_temp.docx"
    print(f"Étape 1/2 : Conversion PDF → DOCX...")
    convert_pdf_to_docx(pdf_path, docx_path)
    print(f"  ✓ DOCX créé: {docx_path}")
    
    # Step 2: DOCX → HTML
    if html_path is None:
        html_path = base + ".html"
    print(f"Étape 2/2 : Conversion DOCX → HTML...")
    convert_docx_to_html(docx_path, html_path)
    print(f"  ✓ HTML créé: {html_path}")
    
    # Clean up temporary DOCX unless requested to keep
    if not keep_docx and os.path.isfile(docx_path):
        os.remove(docx_path)
        print(f"  ✓ Fichier temporaire supprimé")
    
    return html_path


def _parse_args(argv: list[str]) -> argparse.Namespace:
    p = argparse.ArgumentParser(
        description="Convert PDF to HTML via DOCX for better fidelity"
    )
    p.add_argument("input", help="Path to input PDF file")
    p.add_argument("--output", "-o", help="Path to output HTML file")
    p.add_argument("--keep-docx", action="store_true", 
                   help="Keep intermediate DOCX file")
    return p.parse_args(argv)


def main(argv: list[str] | None = None) -> int:
    ns = _parse_args(argv or sys.argv[1:])
    try:
        out = convert_pdf_to_html_via_docx(ns.input, ns.output, ns.keep_docx)
        print(f"\n✓ Conversion terminée!")
        print(f"Fichier HTML: {out}")
        if os.path.isfile(out):
            size_kb = os.path.getsize(out) / 1024
            print(f"Taille: {size_kb:.1f} KB")
        return 0
    except Exception as e:
        print(f"Erreur: {e}", file=sys.stderr)
        import traceback
        traceback.print_exc()
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
