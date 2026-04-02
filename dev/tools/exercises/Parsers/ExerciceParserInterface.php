<?php

declare(strict_types=1);

namespace MonCoachScolaire\Parsers;

interface ExerciceParserInterface
{
    /**
     * Parse un exercice brut (JSON, texte, etc.) et retourne un ParsedExercise structuré.
     * @param string $raw
     * @return ParsedExercise
     */
    public function parse(string $raw): ParsedExercise;
}
