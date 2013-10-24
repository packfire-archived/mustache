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
        $this->assertEquals(Tokenizer::TYPE_TEXT, $tokens[0][Tokenizer::TOKEN_TYPE]);
    }

    public function testTextTwoLineToken()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->parse("testing\ntext");
        $this->assertCount(3, $tokens);
        $this->assertEquals('testing', $tokens[0][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_TEXT, $tokens[0][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals("\n", $tokens[1][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_LINE, $tokens[1][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals('text', $tokens[2][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_TEXT, $tokens[2][Tokenizer::TOKEN_TYPE]);
    }

    public function testTokenInBetween()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->parse("testing {{name}} text");
        $this->assertCount(3, $tokens);
        $this->assertEquals('testing ', $tokens[0][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_TEXT, $tokens[0][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals('name', $tokens[1][Tokenizer::TOKEN_NAME]);
        $this->assertEquals(Tokenizer::TYPE_NORMAL, $tokens[1][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals(' text', $tokens[2][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_TEXT, $tokens[2][Tokenizer::TOKEN_TYPE]);
    }

    public function testTokensAndNewLines()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->parse("testing\n{{name}}\ntext");
        $this->assertCount(5, $tokens);
        $this->assertEquals('testing', $tokens[0][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_TEXT, $tokens[0][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals("\n", $tokens[1][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_LINE, $tokens[1][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals('name', $tokens[2][Tokenizer::TOKEN_NAME]);
        $this->assertEquals(Tokenizer::TYPE_NORMAL, $tokens[2][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals("\n", $tokens[3][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_LINE, $tokens[3][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals('text', $tokens[4][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_TEXT, $tokens[4][Tokenizer::TOKEN_TYPE]);
    }

    public function testUnescapeTriple()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->parse("testing\n{{{name}}}\ntext");
        $this->assertCount(5, $tokens);
        $this->assertEquals('testing', $tokens[0][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_TEXT, $tokens[0][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals("\n", $tokens[1][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_LINE, $tokens[1][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals('name', $tokens[2][Tokenizer::TOKEN_NAME]);
        $this->assertEquals(Tokenizer::TYPE_UNESCAPETRIPLE, $tokens[2][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals("\n", $tokens[3][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_LINE, $tokens[3][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals('text', $tokens[4][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_TEXT, $tokens[4][Tokenizer::TOKEN_TYPE]);
    }

    public function testSectionsToken()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->parse("testing {{#bool}}text{{/bool}}");
        $this->assertCount(2, $tokens);
        $this->assertEquals('testing ', $tokens[0][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_TEXT, $tokens[0][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals('bool', $tokens[1][Tokenizer::TOKEN_NAME]);
        $this->assertEquals(Tokenizer::TYPE_OPEN, $tokens[1][Tokenizer::TOKEN_TYPE]);
        $this->assertCount(1, $tokens[1][Tokenizer::TOKEN_NODES]);
        $this->assertEquals('text', $tokens[1][Tokenizer::TOKEN_NODES][0][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals(Tokenizer::TYPE_TEXT, $tokens[1][Tokenizer::TOKEN_NODES][0][Tokenizer::TOKEN_TYPE]);
    }

    public function testChangeTagDelimiters()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->parse("testing\n{{=| |=}}{{|bool|}}");
        $this->assertCount(6, $tokens);
        $this->assertEquals('| |', $tokens[2][Tokenizer::TOKEN_NAME]);
        $this->assertEquals(Tokenizer::TYPE_CHANGETAG, $tokens[2][Tokenizer::TOKEN_TYPE]);
        $this->assertEquals('{{', $tokens[3][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals('}}', $tokens[5][Tokenizer::TOKEN_VALUE]);
        $this->assertEquals('bool', $tokens[4][Tokenizer::TOKEN_NAME]);
        $this->assertEquals(Tokenizer::TYPE_NORMAL, $tokens[4][Tokenizer::TOKEN_TYPE]);
    }
}
