<?php

declare(strict_types=1);

namespace MonCoachScolaire\Parsers;

/**
 * Structure commune pour tous les exercices parsés.
 */
class ParsedExercise
{
    public string $subject;
    public string $level;
    public string $title;
    public string $statement;
    public array $questions = [];
    public ?array $formulas = null;
    public ?array $subQuestions = null;
    public ?string $correction = null;
    public ?string $type = null;
    public ?string $raw = null;
    public ?string $parsingNote = null;
    public function __construct(array $data = [])
    {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
    }
}
