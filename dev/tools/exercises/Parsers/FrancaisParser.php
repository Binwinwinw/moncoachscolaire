<?php

declare(strict_types=1);

namespace MonCoachScolaire\Parsers;

class FrancaisParser implements ExerciceParserInterface
{
    public function parse(string $raw): ParsedExercise
    {
        $data = json_decode($raw, true);
        $statement = $data['Content'] ?? '';
        $subQuestions = null;
        // Extraction des sous-questions si présentes
        if (!empty($data['sub_questions'])) {
            $subQuestions = is_string($data['sub_questions']) ? json_decode($data['sub_questions'], true) : $data['sub_questions'];
        } else {
            // Fallback : découpe par numérotation ou puces
            if (preg_match_all('/(\d+\)|\d+\.|- )\s*(.+?)(?=(\d+\)|\d+\.|- |$))/s', $statement, $matches, PREG_SET_ORDER)) {
                $subQuestions = array_map(fn($m) => trim($m[2]), $matches);
            }
            // Si aucune sous-question trouvée, détecter (blanc) ou (blanche)
            if (empty($subQuestions) && preg_match('/\(blanc(?:he)?\)/i', $statement)) {
                $subQuestions = ["blanc(e)"];
            }
        }
        return new ParsedExercise([
            'subject' => $data['Subject'] ?? 'Français',
            'level' => $data['Level'] ?? '',
            'title' => $data['Title'] ?? '',
            'statement' => $statement,
            'subQuestions' => $subQuestions,
            'type' => $data['Type'] ?? null,
            'raw' => $raw,
        ]);
    }
}
