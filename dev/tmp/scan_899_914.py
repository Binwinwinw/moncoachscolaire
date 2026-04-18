import json
import re
from pathlib import Path

base = Path('src/data/quiz')
ph_pat = re.compile(r'(?i)^(concept [abcd]|notion \d+|placeholder|exemple\.\.\.|quel concept|concept [abcd])$')

for i in range(899, 915):
    p = base / f'{i}.json'
    if not p.exists():
        print(i, 'MISSING')
        continue

    data = json.loads(p.read_text(encoding='utf-8'))
    issues = []
    title = data['contents']['title']
    if ph_pat.search(title.lower()):
        issues.append('title')

    for qi, q in enumerate(data['quiz']['questions']):
        qtxt = q.get('question', '')
        if ph_pat.search(qtxt.lower()):
            issues.append(f'q{qi} question')

        ph_ch = [c for c in q.get('choices', []) if ph_pat.search(c.lower())]
        if ph_ch:
            issues.append(f'q{qi} choice placeholders {ph_ch}')

        if q.get('type') == 'texte' and q.get('placeholder', ''):
            issues.append(f'q{qi} text-placeholder')

    for ni, n in enumerate(data.get('exercisenotion', [])):
        if ph_pat.search(n.get('notion', '').lower()) or ph_pat.search(n.get('description', '').lower()):
            issues.append(f'notion{ni}')

    print(i, len(issues), issues[:10])
