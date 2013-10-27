<?php

namespace Packfire\Template\Mustache;

class Tokenizer
{
    /**
     * The tag regular expression
     * @since 1.2.0
     */
    const TAG_REGEX = '`%s([%s]{0,1})(%s)%s`is';

    const TYPE_NORMAL = '!ev';
    const TYPE_OPEN = '#';
    const TYPE_CLOSE = '/';
    const TYPE_UNESCAPE = '&';
    const TYPE_UNESCAPETRIPLE = '{';
    const TYPE_INVERT = '^';
    const TYPE_COMMENT = '!';
    const TYPE_PARTIAL1 = '>';
    const TYPE_PARTIAL2 = '<';
    const TYPE_CHANGETAG = '=';
    const TYPE_TEXT = '!t';
    const TYPE_LINE = '!l';

    const TOKEN_TYPE = 'type';
    const TOKEN_NAME = 'name';
    const TOKEN_OPEN_DELIMITER = 'open';
    const TOKEN_CLOSE_DELIMITER = 'close';
    const TOKEN_POSITION = 'position';
    const TOKEN_LINE = 'line';
    const TOKEN_COLUMN = 'column';
    const TOKEN_VALUE = 'value';
    const TOKEN_NODES = 'nodes';

    /**
     * The opening delimiter
     * @var string
     * @since 1.2.0
     */
    protected $openDelimiter = '{{';

    /**
     * The closing delimiter
     * @var string
     * @since 1.2.0
     */
    protected $closeDelimiter = '}}';

    /**
     * The current line number
     * @var integer
     * @since 1.2.0
     */
    protected $line = 1;

    public function parse($text)
    {
        $tokens = array();
        $textLength = strlen($text);
        $position = 0;
        $this->line = 1;
        while ($position < $textLength) {
            $match = array();
            $newlinePosition = strpos($text, "\n", $position);
            if ($newlinePosition === false) {
                $newlinePosition = $textLength;
            }
            do {
                $hasTagMatch = preg_match(
                    $this->buildMatchingTag(),
                    substr($text, 0, $newlinePosition),
                    $match,
                    PREG_OFFSET_CAPTURE,
                    $position
                );
                if ($hasTagMatch) {
                    // there's a tag in between
                    $tagLength = strlen($match[0][0]);
                    $tagStart = $match[0][1];
                    $tagEnd = $tagStart + $tagLength;
                    if ($match[1][0] == self::TYPE_UNESCAPETRIPLE) {
                        $tagLength += 1;
                        $tagEnd += 1;
                    }

                    $subText = substr($text, $position, $tagStart - $position);
                    if (strlen($subText)) {
                        $tokens[] = array(
                            self::TOKEN_TYPE => self::TYPE_TEXT,
                            self::TOKEN_LINE => $this->line,
                            self::TOKEN_VALUE => $subText
                        );
                    }

                    $tokens[] = $this->buildTagToken($match, $position);
                    $position = $tagEnd;
                }
            } while ($hasTagMatch);

            $subText = substr($text, $position, $newlinePosition - $position);
            if (strlen($subText)) {
                $tokens[] = array(
                    self::TOKEN_TYPE => self::TYPE_TEXT,
                    self::TOKEN_LINE => $this->line,
                    self::TOKEN_VALUE => $subText
                );
            }
            $position = $newlinePosition;
            if (substr($text, $position, 1) === "\n") {
                $tokens[] = array(
                    self::TOKEN_TYPE => self::TYPE_LINE,
                    self::TOKEN_LINE => $this->line,
                    self::TOKEN_VALUE => "\n"
                );
                ++$this->line;
                ++$position;
            }
        }
        $tokens = $this->processTokens($tokens);
        $this->reset();
        return $tokens;
    }

    public function changeDelimiters($open, $close)
    {
        $this->openDelimiter = $open;
        $this->closeDelimiter = $close;
    }

    public function reset()
    {
        $this->openDelimiter = '{{';
        $this->closeDelimiter = '}}';
        $this->line = 1;
    }

    protected function processTokens($tokens, &$index = 0, $closingTag = null)
    {
        $result = array();
        $count = count($tokens);
        for (; $index < $count; ++$index) {
            $token = $tokens[$index];
            if ($token[self::TOKEN_TYPE] == self::TYPE_OPEN
                || $token[self::TOKEN_TYPE] == self::TYPE_INVERT) {
                ++$index;
                $nodes = $this->processTokens($tokens, $index, $token[self::TOKEN_NAME]);
                $token[self::TOKEN_NODES] = $nodes;
                $result[] = $token;
            } elseif ($closingTag && $token[self::TOKEN_TYPE] == self::TYPE_CLOSE && $token[self::TOKEN_NAME] == $closingTag) {
                if ($index >= 1
                        && $tokens[$index - 1][self::TOKEN_TYPE] == self::TYPE_LINE
                        && $tokens[$index + 1][self::TOKEN_TYPE] == self::TYPE_LINE) {
                    ++$index;
                } elseif ($index >= 2
                        && $tokens[$index - 2][self::TOKEN_TYPE] == self::TYPE_LINE
                        && Mustache::isTokenWhitespace($tokens[$index - 1])
                        && $tokens[$index + 1][self::TOKEN_TYPE] == self::TYPE_LINE) {
                    array_pop($result);
                    ++$index;
                }
                break;
            } else {
                $result[] = $token;
            }
        }
        return $result;
    }

    protected function buildTagToken($match, $position)
    {
        $name = $match[2][0];
        $openDelimiter = $this->openDelimiter;
        $closeDelimiter = $this->closeDelimiter;
        if ($match[1][0] == self::TYPE_CHANGETAG) {
            if (substr($name, -1) == '=') {
                $name = substr($name, 0, strlen($name) - 1);
            }
            list($this->openDelimiter, $this->closeDelimiter) = explode(' ', $name);
        }
        $type = $match[1][0];
        if (!$type) {
            $type = self::TYPE_NORMAL;
        }
        return array(
            self::TOKEN_TYPE => $type,
            self::TOKEN_NAME => trim($name),
            self::TOKEN_LINE => $this->line,
            self::TOKEN_OPEN_DELIMITER => $openDelimiter,
            self::TOKEN_CLOSE_DELIMITER => $closeDelimiter
        );
    }

    /**
     * Build the tag matching regular expression
     * @param string $name (optional) The tag name to match
     * @return string Returns the final regular expression
     * @since 1.2.0
     */
    private function buildMatchingTag($name = '.+?', $type = '^/&#={!><')
    {
        return sprintf(self::TAG_REGEX, preg_quote($this->openDelimiter), preg_quote($type), $name, preg_quote($this->closeDelimiter));
    }
}
