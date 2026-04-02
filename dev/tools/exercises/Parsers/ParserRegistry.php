<?php

declare(strict_types=1);

namespace MonCoachScolaire\Parsers;

class ParserRegistry
{
    /** @var array<string, ExerciceParserInterface> */
    private array $parsers = [];
    private ExerciceParserInterface $fallback;

    public function __construct(ExerciceParserInterface $fallback)
    {
        $this->fallback = $fallback;
    }

    public function register(string $key, ExerciceParserInterface $parser): void
    {
        $this->parsers[$key] = $parser;
    }

    public function getParser(string $subject, ?string $type = null): ExerciceParserInterface
    {
        $key = strtolower($subject);
        if ($type) {
            $key .= '_' . strtolower($type);
        }
        if (isset($this->parsers[$key])) {
            return $this->parsers[$key];
        }
        if (isset($this->parsers[strtolower($subject)])) {
            return $this->parsers[strtolower($subject)];
        }
        return $this->fallback;
    }
}
