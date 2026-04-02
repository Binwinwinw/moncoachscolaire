<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use MonCoachScolaire\Parsers\ParserRegistry;
use MonCoachScolaire\Parsers\MathParser;
use MonCoachScolaire\Parsers\FrancaisParser;
use MonCoachScolaire\Parsers\GenericParser;

final class ParserRegistryTest extends TestCase
{
    public function testRegistryRoutesToMathParser(): void
    {
        $registry = new ParserRegistry(new GenericParser());
        $mathParser = new MathParser();
        $registry->register('mathématiques', $mathParser);
        $raw = json_encode([
            'Subject' => 'Mathématiques',
            'Content' => 'a) Calcule 2+2. b) Donne la formule $E=mc^2$.'
        ]);
        $parser = $registry->getParser('Mathématiques');
        $parsed = $parser->parse($raw);
        $this->assertEquals('Mathématiques', $parsed->subject);
        $this->assertCount(2, $parsed->questions);
        $this->assertContains('E=mc^2', $parsed->formulas[0]);
    }

    public function testRegistryRoutesToFrancaisParser(): void
    {
        $registry = new ParserRegistry(new GenericParser());
        $frParser = new FrancaisParser();
        $registry->register('français', $frParser);
        $raw = json_encode([
            'Subject' => 'Français',
            'Content' => '1) Explique le texte. 2) Donne un exemple.'
        ]);
        $parser = $registry->getParser('Français');
        $parsed = $parser->parse($raw);
        $this->assertEquals('Français', $parsed->subject);
        $this->assertIsArray($parsed->subQuestions);
        $this->assertCount(2, $parsed->subQuestions);
    }

    public function testFallbackGenericParser(): void
    {
        $registry = new ParserRegistry(new GenericParser());
        $raw = json_encode([
            'Subject' => 'Philosophie',
            'Content' => 'Dissertez sur la liberté.'
        ]);
        $parser = $registry->getParser('Philosophie');
        $parsed = $parser->parse($raw);
        $this->assertEquals('Philosophie', $parsed->subject);
        $this->assertEquals('Fallback GenericParser utilisé', $parsed->parsingNote);
    }
}
