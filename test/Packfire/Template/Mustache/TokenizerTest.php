<?php

namespace Packfire\Template\Mustache;

use \PHPUnit_Framework_TestCase;

class TokenizerTest extends PHPUnit_Framework_TestCase
{
    public function testTextToken()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->parse('testing text');
        $this->assertCount(1, $tokens);
    }

    public function testTextTwoLineToken()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->parse("testing\ntext");
        $this->assertCount(3, $tokens);
    }

    public function testTokenInBetween()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->parse("testing {{name}} text");
        $this->assertCount(3, $tokens);
    }
}
