<?php

declare(strict_types=1);

namespace MonCoachScolaire\Tests\Parsers;

use MonCoachScolaire\Parsers\FrancaisParser;
use MonCoachScolaire\Parsers\ParsedExercise;
use PHPUnit\Framework\TestCase;

final class FrancaisParserTest extends TestCase {
    private FrancaisParser $parser;

    protected function setUp(): void {
        $this->parser = new FrancaisParser();
    }

    public function testBlancDetection(): void {
        $raw = json_encode([
            'Subject' => 'Français',
            'Content' => 'Complétez : (blanc)',
            'Type' => 'accord'
        ], JSON_UNESCAPED_UNICODE);
        $parsed = $this->parser->parse($raw);
        $this->assertInstanceOf(ParsedExercise::class, $parsed);
        $this->assertEquals(['blanc(e)'], $parsed->subQuestions);
    }
}
