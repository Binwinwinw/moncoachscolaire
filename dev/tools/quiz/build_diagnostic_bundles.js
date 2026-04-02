#!/usr/bin/env node
/*
 * Build diagnostic bundles by level from per-quiz JSON files.
 *
 * Source files:
 * - src/data/quiz/{id}.json
 * - src/data/quiz_answers/{id}.json
 *
 * Generated files:
 * - src/data/quiz_packs/{level}.json
 * - src/data/quiz_answers_packs/{level}.json
 * - src/data/quiz_packs/index.json
 * - src/data/quiz_answers_packs/index.json
 */

const fs = require('fs');
const path = require('path');

const root = process.cwd();
const quizDir = path.join(root, 'src', 'data', 'quiz');
const answersDir = path.join(root, 'src', 'data', 'quiz_answers');
const quizPacksDir = path.join(root, 'src', 'data', 'quiz_packs');
const answerPacksDir = path.join(root, 'src', 'data', 'quiz_answers_packs');

function ensureDir(dirPath) {
  if (!fs.existsSync(dirPath)) {
    fs.mkdirSync(dirPath, { recursive: true });
  }
}

function readJson(filePath) {
  return JSON.parse(fs.readFileSync(filePath, 'utf8'));
}

function writeJson(filePath, data) {
  fs.writeFileSync(filePath, JSON.stringify(data, null, 2) + '\n', 'utf8');
}

function normalizeLevel(rawLevel) {
  const value = String(rawLevel || '').trim().toLowerCase();
  const aliases = {
    '6eme': '6eme',
    '6eme ': '6eme',
    '6eme\u0300': '6eme',
    '6eme\u0301': '6eme',
    '6ème': '6eme',
    '5eme': '5eme',
    '5ème': '5eme',
    '4eme': '4eme',
    '4ème': '4eme',
    '3eme': '3eme',
    '3ème': '3eme',
    '2nde': 'seconde',
    'seconde': 'seconde',
    '1ere': '1ere',
    '1ère': '1ere',
    'premiere': 'premiere',
    'première': 'premiere',
    'terminale': 'terminale',
    'bac': 'bac'
  };
  return aliases[value] || value || 'unknown';
}

function listQuizIds() {
  return fs
    .readdirSync(quizDir)
    .filter((name) => /^\d+\.json$/i.test(name))
    .map((name) => Number(path.basename(name, '.json')))
    .sort((a, b) => a - b);
}

function buildBundles() {
  ensureDir(quizPacksDir);
  ensureDir(answerPacksDir);

  const ids = listQuizIds();
  const now = new Date().toISOString();

  const quizPacks = {};
  const answerPacks = {};
  const quizIdToLevel = {};
  const warnings = [];

  for (const id of ids) {
    const quizPath = path.join(quizDir, `${id}.json`);
    const answersPath = path.join(answersDir, `${id}.json`);

    if (!fs.existsSync(answersPath)) {
      warnings.push(`Missing answers file for quiz id ${id}`);
      continue;
    }

    const quizData = readJson(quizPath);
    const answersData = readJson(answersPath);

    const levelRaw =
      quizData?.quiz?.level ||
      quizData?.contents?.level ||
      answersData?.contents?.level ||
      '';
    const level = normalizeLevel(levelRaw);

    if (!quizPacks[level]) {
      quizPacks[level] = {
        generated_at: now,
        level,
        quizzes: []
      };
    }

    if (!answerPacks[level]) {
      answerPacks[level] = {
        generated_at: now,
        level,
        quizzes: []
      };
    }

    quizPacks[level].quizzes.push({
      id,
      ...quizData
    });

    answerPacks[level].quizzes.push({
      id,
      ...answersData
    });

    quizIdToLevel[String(id)] = level;
  }

  const levels = Object.keys(quizPacks).sort();
  for (const level of levels) {
    quizPacks[level].quizzes.sort((a, b) => a.id - b.id);
    answerPacks[level].quizzes.sort((a, b) => a.id - b.id);

    writeJson(path.join(quizPacksDir, `${level}.json`), quizPacks[level]);
    writeJson(path.join(answerPacksDir, `${level}.json`), answerPacks[level]);
  }

  const index = {
    generated_at: now,
    levels: {},
    quiz_id_to_level: quizIdToLevel
  };

  for (const level of levels) {
    index.levels[level] = {
      quiz_count: quizPacks[level].quizzes.length,
      ids: quizPacks[level].quizzes.map((q) => q.id)
    };
  }

  writeJson(path.join(quizPacksDir, 'index.json'), index);
  writeJson(path.join(answerPacksDir, 'index.json'), index);

  console.log(`[bundles] levels: ${levels.length}`);
  console.log(`[bundles] quizzes: ${Object.keys(quizIdToLevel).length}`);
  if (warnings.length > 0) {
    console.log('[bundles] warnings:');
    warnings.forEach((w) => console.log(`- ${w}`));
  }
}

if (!fs.existsSync(quizDir) || !fs.existsSync(answersDir)) {
  console.error('Required source directories are missing.');
  process.exit(1);
}

try {
  buildBundles();
} catch (error) {
  console.error('Failed to build diagnostic bundles:', error.message);
  process.exit(1);
}
