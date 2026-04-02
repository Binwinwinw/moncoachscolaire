/**
 * dev/tools/tests/debug_exercise.mjs
 * Debug CLI pour inspecter un exercice via /api/get_exercises.php
 *
 * Usage:
 *   node dev/tools/tests/debug_exercise.mjs --baseUrl=http://localhost/moncoachscolaire --id=123 [--level="6ème"] [--subject="Mathématiques"]
 */

const args = parseArgs(process.argv.slice(2));
const baseUrl = (args.baseUrl || '').replace(/\/$/, '');
const exerciseId = parseInt(args.id || '0', 10);
const level = args.level || null;
const subject = args.subject || null;
const action = args.action || 'exercises';

if (!baseUrl || !exerciseId) {
    console.log('❌ Paramètres requis manquants.');
    console.log('Usage: node dev/tools/tests/debug_exercise.mjs --baseUrl=http://localhost/moncoachscolaire --id=123 [--level="6ème"] [--subject="Mathématiques"]');
    process.exit(1);
}

const endpoint = `${baseUrl}/api/get_exercises.php`;
const query = new URLSearchParams({ action });

if (action === 'exercise_html') {
    query.set('id', String(exerciseId));
}

if (level) query.set('level', level);
if (subject) query.set('subject', subject);

const url = `${endpoint}?${query.toString()}`;
console.log(`🔗 Endpoint: ${url}`);

const result = await fetchJson(url);
const data = result?.data ?? null;

if (result?.error) {
    console.log('❌ JSON invalide ou réponse non parsable.');
    console.log(`HTTP_STATUS: ${result.status ?? 'UNKNOWN'}`);
    console.log(`Réponse brute (extrait): ${result.raw ?? ''}`);
    process.exit(1);
}

if (!data || !data.success) {
    console.log('❌ Réponse invalide ou échec API.');
    console.log('Réponse brute:', JSON.stringify(data, null, 2).slice(0, 300));
    process.exit(1);
}

if (action === 'exercise_html') {
    const html = data.html ?? '';
    const preview = String(html).trim().slice(0, 300);
    console.log(`✅ HTML récupéré (${String(html).length} chars)`);
    console.log(`HTML preview: ${preview}`);
    process.exit(0);
}

const exercises = Array.isArray(data.exercises) ? data.exercises : [];
if (exercises.length === 0) {
    console.log('⚠️ Aucun exercice retourné.');
    process.exit(0);
}

const exercise = findExerciseById(exercises, exerciseId);

if (!exercise) {
    console.log(`⚠️ Exercice ID ${exerciseId} introuvable dans la réponse.`);
    process.exit(0);
}

printExerciseDebug(exercise);

function parseArgs(argv) {
    const out = {};
    for (const arg of argv) {
        const [key, value] = arg.split('=');
        if (key.startsWith('--')) {
            out[key.replace(/^--/, '')] = value ?? true;
        }
    }
    return out;
}

async function fetchJson(url) {
    try {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const text = await res.text();
        try {
            const data = JSON.parse(text);
            return { data, status: res.status, raw: text.slice(0, 300), error: false };
        } catch {
            return { data: null, status: res.status, raw: text.slice(0, 300), error: true };
        }
    } catch (err) {
        return { data: null, status: null, raw: '', error: true };
    }
}

function findExerciseById(exercises, id) {
    return exercises.find(ex => Number(ex.Id ?? ex.ExerciseID) === id) || null;
}

function printExerciseDebug(ex) {
    const id = ex.Id ?? ex.ExerciseID ?? 'NON TROUVÉ';
    const title = ex.Title ?? 'NON TROUVÉ';
    const subject = ex.Subject ?? 'NON TROUVÉ';
    const level = ex.Level ?? 'NON TROUVÉ';
    const answerType = ex.AnswerType ?? 'NON TROUVÉ';
    const type = ex.type ?? 'NON TROUVÉ';
    const typeSource = ex.type_source ?? 'NON TROUVÉ';

    const instruction = truncateText(ex.Instruction ?? '', 150);
    const content = truncateText(ex.Content ?? '', 300);

    const answer = ex.Answer ?? ex.Solution ?? 'NON TROUVÉ';

    const choicesPreview = formatChoicesPreview(ex.Choices);

    const contentStr = String(ex.Content ?? '');
    const hasBlanks = contentStr.includes('___') ? 'OUI' : 'NON';
    const hasChoiceSlashPattern = /\b[\p{L}’'-]+\s*\/\s*[\p{L}’'-]+\b/u.test(contentStr) ? 'OUI' : 'NON';

    console.log('\n====================== DEBUG EXERCICE ======================');
    console.log(`Id: ${id}`);
    console.log(`Title: ${title}`);
    console.log(`Subject: ${subject}`);
    console.log(`Level: ${level}`);
    console.log(`AnswerType (raw): ${answerType}`);
    console.log(`type: ${type}`);
    console.log(`type_source: ${typeSource}`);
    console.log(`Choices: ${choicesPreview}`);
    console.log(`Instruction: ${instruction}`);
    console.log(`Content: ${content}`);
    console.log(`Answer/Solution: ${formatAnswer(answer)}`);
    console.log(`Content contient "___" ? ${hasBlanks}`);
    console.log(`Content contient pattern X / Y ? ${hasChoiceSlashPattern}`);
    console.log('============================================================');
}

function truncateText(text, maxLen) {
    const t = String(text).replace(/\s+/g, ' ').trim();
    return t.length <= maxLen ? t : `${t.slice(0, maxLen)}...`;
}

function formatChoicesPreview(choices) {
    if (!choices) return 'AUCUN';

    let parsed = choices;
    if (typeof choices === 'string') {
        try {
            parsed = JSON.parse(choices);
        } catch {
            return 'FORMAT INCONNU';
        }
    }
    if (!Array.isArray(parsed)) return 'FORMAT INCONNU';

    const preview = parsed.slice(0, 3).map(c => (typeof c === 'object' ? JSON.stringify(c) : String(c)));
    return `${parsed.length} item(s) | preview: ${preview.join(' | ')}`;
}

function formatAnswer(answer) {
    if (answer === 'NON TROUVÉ') return answer;
    if (Array.isArray(answer) || typeof answer === 'object') {
        return JSON.stringify(answer);
    }
    return String(answer);
}
