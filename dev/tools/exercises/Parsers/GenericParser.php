<?php

declare(strict_types=1);

namespace MonCoachScolaire\Parsers;

class GenericParser implements ExerciceParserInterface
{
    public function parse(string $raw): ParsedExercise
    {
        $data = json_decode($raw, true);
        return new ParsedExercise([
            'subject' => $data['Subject'] ?? '',
            'level' => $data['Level'] ?? '',
            'title' => $data['Title'] ?? '',
            'statement' => $data['Content'] ?? '',
            'type' => $data['Type'] ?? null,
            'raw' => $raw,
            'parsingNote' => 'Fallback GenericParser utilisé',
        ]);
    }
}
