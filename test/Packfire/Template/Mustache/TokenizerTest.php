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
        $this->assertEquals('testing text', $tokens[0][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_TEXT, $tokens[0][Tokenizer::TOKEN_TYPE]);
    }

    public function testTextTwoLineToken()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->parse("testing\ntext");
        $this->assertCount(3, $tokens);
        $this->assertEquals('testing', $tokens[0][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_TEXT, $tokens[0][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals("\n", $tokens[1][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_NEWLINE, $tokens[1][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals('text', $tokens[2][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_TEXT, $tokens[2][Tokenizer::TOKEN_TYPE]);
    }

    public function testTokenInBetween()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->parse("testing {{name}} text");
        $this->assertCount(3, $tokens);
        $this->assertEquals('testing ', $tokens[0][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_TEXT, $tokens[0][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals('name', $tokens[1][Tokenizer::TOKEN_NAME]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_TAG, $tokens[1][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals(' text', $tokens[2][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_TEXT, $tokens[2][Tokenizer::TOKEN_TYPE]);
    }

    public function testTokensAndNewLines()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->parse("testing\n{{name}}\ntext");
        $this->assertCount(5, $tokens);
        $this->assertEquals('testing', $tokens[0][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_TEXT, $tokens[0][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals("\n", $tokens[1][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_NEWLINE, $tokens[1][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals('name', $tokens[2][Tokenizer::TOKEN_NAME]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_TAG, $tokens[2][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals("\n", $tokens[3][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_NEWLINE, $tokens[3][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals('text', $tokens[4][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_TEXT, $tokens[4][Tokenizer::TOKEN_TYPE]);
    }

    public function testUnescapeTriple()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->parse("testing\n{{{name}}}\ntext");
        $this->assertCount(5, $tokens);
        $this->assertEquals('testing', $tokens[0][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_TEXT, $tokens[0][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals("\n", $tokens[1][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_NEWLINE, $tokens[1][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals('name', $tokens[2][Tokenizer::TOKEN_NAME]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_TAG, $tokens[2][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals(Tokenizer::TYPE_UNESCAPETRIPLE, $tokens[2][Tokenizer::TOKEN_TAG_TYPE]);
        $this->assertEquals("\n", $tokens[3][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_NEWLINE, $tokens[3][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals('text', $tokens[4][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TOKEN_TYPE_TEXT, $tokens[4][Tokenizer::TOKEN_TYPE]);
    }
}
