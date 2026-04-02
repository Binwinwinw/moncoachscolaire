<?php

declare(strict_types=1);

namespace MonCoachScolaire\Parsers;

class MathParser implements ExerciceParserInterface
{
    public function parse(string $raw): ParsedExercise
    {
        $data = json_decode($raw, true);
        $statement = $data['Content'] ?? '';
        $questions = [];
        $formulas = [];
        // Extraction naïve des formules LaTeX entre $...$ ou \(...\)
        if (preg_match_all('/\$([^$]+)\$/', $statement, $matches)) {
            $formulas = array_map(function($f) {
                // Normalisation simple : ∫ → \\int
                $f = str_replace('∫', '\\int', $f);
                return $f;
            }, $matches[1]);
        }
        // Extraction des questions (ex : a), b), c) ...)
        if (preg_match_all('/([a-z]\))\s*(.+?)(?=(?:[a-z]\))|$)/is', $statement, $qmatches, PREG_SET_ORDER)) {
            foreach ($qmatches as $q) {
                $questions[] = trim($q[2]);
            }
        } else {
            $questions[] = trim($statement);
        }
        return new ParsedExercise([
            'subject' => $data['Subject'] ?? 'Mathématiques',
            'level' => $data['Level'] ?? '',
            'title' => $data['Title'] ?? '',
            'statement' => $statement,
            'questions' => $questions,
            'formulas' => $formulas ?: null,
            'type' => $data['Type'] ?? null,
            'raw' => $raw,
        ]);
    }
}
