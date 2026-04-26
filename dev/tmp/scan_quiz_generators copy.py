import re
from pathlib import Path
import json

base = Path('dev/tools/quiz/enrichment/generator')
paths = [
'generate_1ere_anglais.py', 'generate_1ere_espagnol.py', 'generate_1ere_francais.py', 'generate_1ere_hg.py', 'generate_1ere_hggsp.py', 'generate_1ere_mathematiques.py', 'generate_1ere_nsi.py', 'generate_1ere_philo.py', 'generate_1ere_phychi.py', 'generate_1ere_ses.py', 'generate_1ere_svt.py',
'generate_2nde_anglais.py', 'generate_2nde_emc.py', 'generate_2nde_espagnol.py', 'generate_2nde_francais.py', 'generate_2nde_histoire_geo.py', 'generate_2nde_mathematiques.py', 'generate_2nde_phychi.py', 'generate_2nde_svt.py', 'generate_2nde_technologie.py',
'generate_3eme_anglais.py', 'generate_3eme_emc.py', 'generate_3eme_espagnol.py', 'generate_3eme_francais.py', 'generate_3eme_hg.py', 'generate_3eme_mathematiques.py', 'generate_3eme_phychi.py', 'generate_3eme_svt.py', 'generate_3eme_techno.py',
'generate_4eme_anglais.py', 'generate_4eme_emc.py', 'generate_4eme_espagnol.py', 'generate_4eme_francais.py', 'generate_4eme_hg.py', 'generate_4eme_mathematiques.py', 'generate_4eme_phychi.py', 'generate_4eme_svt.py', 'generate_4eme_technologie.py',
'generate_5eme_anglais.py', 'generate_5eme_emc.py', 'generate_5eme_espagnol.py', 'generate_5eme_francais.py', 'generate_5eme_hg.py', 'generate_5eme_mathematiques.py', 'generate_5eme_phychi.py', 'generate_5eme_svt.py', 'generate_5eme_technologie.py',
'generate_6eme_anglais0001-0048.py', 'generate_6eme_emc0097-0144.py', 'generate_6eme_espagnol.py', 'generate_6eme_francais0145-0192.py', 'generate_6eme_hg.py', 'generate_6eme_mathematiques0049-0096.py', 'generate_6eme_phychi.py', 'generate_6eme_svt.py', 'generate_6eme_technologie0193.py',
'generate_terminale_anglais.py', 'generate_terminale_espagnol.py', 'generate_terminale_francais.py', 'generate_terminale_hg.py', 'generate_terminale_mathematiques.py', 'generate_terminale_philo.py', 'generate_terminale_phychi.py', 'generate_terminale_ses.py', 'generate_terminale_svt.py',
]
results = []
for rel in paths:
    p = base / rel
    if not p.exists():
        results.append({'path': str(p), 'exists': False})
        continue
    text = p.read_text(encoding='utf-8', errors='replace')
    quiz_ids = re.findall(r"\(\s*\d+\s*,", text)
    quiz_count = len(quiz_ids)
    question_ids = len(re.findall(r"['\"]id['\"]\s*:\s*['\"]\d+_\d+['\"]", text))
    open_count = len(re.findall(r"['\"]type['\"]\s*:\s*['\"]open['\"]", text, re.IGNORECASE))
    texte_count = len(re.findall(r"['\"]type['\"]\s*:\s*['\"]texte['\"]", text, re.IGNORECASE))
    type_counts = {}
    for v in re.findall(r"['\"]type['\"]\s*:\s*['\"]([^'\"]+)['\"]", text, re.IGNORECASE):
        t = v.strip().lower()
        type_counts[t] = type_counts.get(t, 0) + 1
    results.append({
        'path': str(p),
        'exists': True,
        'quiz_count': quiz_count,
        'question_count': question_ids,
        'open_type_count': open_count,
        'texte_type_count': texte_count,
        'free_type_count': open_count + texte_count,
        'type_counts': type_counts,
        'size': p.stat().st_size,
        'lines': text.count('\n') + 1,
    })
results_sorted = sorted([r for r in results if r['exists']], key=lambda x: (-x['quiz_count'], -x['question_count']))
print(json.dumps(results_sorted, indent=2, ensure_ascii=False))
missing = [r['path'] for r in results if not r['exists']]
if missing:
    print('\nMissing files:')
    for p in missing:
        print(p)
