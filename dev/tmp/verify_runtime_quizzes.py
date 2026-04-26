import json
from collections import Counter, defaultdict
from pathlib import Path

quiz_dir = Path('src/data/quiz')
answer_dir = Path('src/data/quiz_answers')
quiz_files = sorted([p for p in quiz_dir.glob('*.json') if p.is_file()], key=lambda p: int(p.stem) if p.stem.isdigit() else p.stem)
answer_files = {}
for p in answer_dir.glob('*.json'):
    if p.stem.isdigit():
        answer_files[int(p.stem)] = p
    else:
        answer_files[p.stem] = p

invalid_quiz = []
invalid_answer = []
problem_details = []
issues_by_type = defaultdict(set)

def record_problem(qid, issue, *details):
    problem_details.append((qid, issue, *details))
    issues_by_type[issue].add(qid)

for qpath in quiz_files:
    qstem = qpath.stem
    qid = int(qstem) if qstem.isdigit() else qstem
    try:
        data = json.loads(qpath.read_text(encoding='utf-8'))
    except Exception as e:
        invalid_quiz.append((qstem, 'invalid json', str(e)))
        continue
    quiz = data.get('quiz')
    if not isinstance(quiz, dict):
        invalid_quiz.append((qstem, 'missing quiz object', None))
        continue
    qcount = quiz.get('question_count')
    questions = quiz.get('questions')
    if not isinstance(questions, list):
        invalid_quiz.append((qstem, 'questions not list', type(questions).__name__))
        continue
    if qcount != len(questions):
        record_problem(qid, 'question_count mismatch', qcount, len(questions))
    if len(questions) != 8:
        record_problem(qid, 'not 8 questions', len(questions))
    for qi, q in enumerate(questions, start=1):
        if not isinstance(q, dict):
            record_problem(qid, f'question #{qi} not dict', q)
            continue
        qtype = str(q.get('type', '')).strip().lower().replace('_', '-')
        if qtype not in {'qcm', 'vrai-faux'}:
            record_problem(qid, f'bad question type #{qi}', qtype)
        qtext = q.get('question')
        if not isinstance(qtext, str) or not qtext.strip():
            record_problem(qid, f'empty question text #{qi}', qtext)
        if qtype == 'qcm':
            opts = q.get('choices')
            if not isinstance(opts, list) or len(opts) < 2:
                record_problem(qid, f'qcm bad choices #{qi}', opts)
            elif len(opts) != len(set(opts)):
                record_problem(qid, f'qcm duplicate choices #{qi}', opts)
        if qtype == 'vrai-faux' and 'choices' in q:
            record_problem(qid, f'vrai-faux has choices #{qi}', q.get('choices'))
    if qid not in answer_files:
        invalid_answer.append((qstem, 'missing answer file'))
    else:
        try:
            ansdata = json.loads(answer_files[qid].read_text(encoding='utf-8'))
        except Exception as e:
            invalid_answer.append((qstem, 'invalid answer json', str(e)))
            continue
        answers = ansdata.get('quiz', {}).get('answers')
        if not isinstance(answers, list):
            invalid_answer.append((qstem, 'answers not list', type(answers).__name__))
            continue
        if len(answers) != len(questions):
            record_problem(qid, 'answers count mismatch', len(answers), len(questions))
        for ai, a in enumerate(answers, start=1):
            if not isinstance(a, dict):
                record_problem(qid, f'answer #{ai} not dict', a)
                continue
            atype = str(a.get('type', '')).strip().lower().replace('_', '-')
            if atype not in {'qcm', 'vrai-faux'}:
                record_problem(qid, f'bad answer type #{ai}', atype)
            if not a.get('question_id'):
                record_problem(qid, f'missing answer question_id #{ai}', a)
            if not a.get('answer'):
                record_problem(qid, f'missing answer text #{ai}', a)

print('total_quiz_files', len(quiz_files))
print('missing_answer_files', len(invalid_answer))
print('invalid_quiz_files', len(invalid_quiz))
print('problem_details', len(problem_details))

counts = Counter(item[1] for item in problem_details)
print('\nproblem categories:')
for issue, count in counts.most_common():
    print(issue, count)

print('\nunique quiz ids per category:')
for issue, qids in sorted(issues_by_type.items(), key=lambda item: (-len(item[1]), item[0])):
    sample = sorted(qids)[:10]
    print(f'{issue}: {len(qids)} quiz ids, sample {sample}')

print('\nfirst 50 problem details:')
for rec in problem_details[:50]:
    print(rec)

if invalid_quiz:
    print('\nInvalid quiz files:')
    for rec in invalid_quiz[:20]:
        print(rec)
if invalid_answer:
    print('\nInvalid answer files:')
    for rec in invalid_answer[:50]:
        print(rec)
