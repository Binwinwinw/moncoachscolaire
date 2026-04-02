<?php
declare(strict_types=1);

namespace MonCoachScolaire\Tests\Parsers;

use MonCoachScolaire\Parsers\MathParser;
use MonCoachScolaire\Parsers\ParsedExercise;
use PHPUnit\Framework\TestCase;

final class MathParserTest extends TestCase {
    private MathParser $parser;

    protected function setUp(): void {
        $this->parser = new MathParser();
    }

    public function testProblemeQCMParse(): void {
        $raw = json_encode([
            'Subject' => 'Mathématiques',
            'Content' => 'Calculez ∫x dx. a) x²/2 b) x²',
            'Type' => 'probleme'
        ], JSON_UNESCAPED_UNICODE);

        $parsed = $this->parser->parse($raw);

        $this->assertInstanceOf(ParsedExercise::class, $parsed);
        $this->assertEquals('Mathématiques', $parsed->subject);
        $this->assertEquals('probleme', $parsed->type);
        $this->assertStringContainsString('∫x dx', $parsed->statement);
        $this->assertCount(2, $parsed->questions ?? []);
    }
}
