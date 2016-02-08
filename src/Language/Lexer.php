<?php

namespace Fubhy\GraphQL\Language;

class Lexer
{
    /**
     * @var \Fubhy\GraphQL\Language\Source
     */
    protected $source;

    /**
     * @param \Fubhy\GraphQL\Language\Source $source
     */
    public function __construct(Source $source) {
        $this->source = $source;
    }

    /**
     * Reads from body starting at startPosition until it finds a non-whitespace
     * or commented character, then returns the position of that character for
     * lexing.
     *
     * @param int $start
     *
     * @return int
     */
    protected function positionAfterWhitespace($start)
    {
        $position = $start;
        $length = $this->source->getLength();

        while ($start < $length) {
            $code = $this->charCodeAt($position);

            // Skip whitespace.
            if (
                $code === 32 || // space
                $code === 44 || // comma
                $code === 160 || // '\xa0'
                $code === 0x2028 || // line separator
                $code === 0x2029 || // paragraph separator
                $code > 8 && $code < 14 // whitespace
            ) {
                ++$position;
                // Skip comments.
            } elseif ($code === 35) { // #
                ++$position;

                while (
                    $position < $length &&
                    ($code = $this->charCodeAt($position)) &&
                    $code !== 10 && $code !== 13 && $code !== 0x2028 && $code !== 0x2029
                ) {
                    ++$position;
                }
            } else {
                break;
            }
        }

        return $position;
    }

    /**
     * @param int $start
     *
     * @return \Fubhy\GraphQL\Language\Token
     *
     * @throws \Exception
     */
    public function readToken($start)
    {
        $length = $this->source->getLength();
        $position = $this->positionAfterWhitespace($start);

        if ($position >= $length) {
            return new Token(Token::EOF_TYPE, $length, $length);
        }

        $code = $this->charCodeAt($position);

        switch ($code) {
            // !
            case 33:
                return new Token(Token::BANG_TYPE, $position, $position + 1);
            // $
            case 36:
                return new Token(Token::DOLLAR_TYPE, $position, $position + 1);
            // (
            case 40:
                return new Token(Token::PAREN_L_TYPE, $position, $position + 1);
            // )
            case 41:
                return new Token(Token::PAREN_R_TYPE, $position, $position + 1);
            // .
            case 46:
                if ($this->charCodeAt($position + 1) === 46 && $this->charCodeAt($position + 2) === 46) {
                    return new Token(Token::SPREAD_TYPE, $position, $position + 3);
                }
                break;
            // :
            case 58:
                return new Token(Token::COLON_TYPE, $position, $position + 1);
            // =
            case 61:
                return new Token(Token::EQUALS_TYPE, $position, $position + 1);
            // @
            case 64:
                return new Token(Token::AT_TYPE, $position, $position + 1);
            // [
            case 91:
                return new Token(Token::BRACKET_L_TYPE, $position, $position + 1);
            // ]
            case 93:
                return new Token(Token::BRACKET_R_TYPE, $position, $position + 1);
            // {
            case 123:
                return new Token(Token::BRACE_L_TYPE, $position, $position + 1);
            // |
            case 124:
                return new Token(Token::PIPE_TYPE, $position, $position + 1);
            // }
            case 125:
                return new Token(Token::BRACE_R_TYPE, $position, $position + 1);
            // A-Z
            case 65: case 66: case 67: case 68: case 69: case 70: case 71: case 72:
            case 73: case 74: case 75: case 76: case 77: case 78: case 79: case 80:
            case 81: case 82: case 83: case 84: case 85: case 86: case 87: case 88:
            case 89: case 90:
            // _
            case 95:
            // a-z
            case 97: case 98: case 99: case 100: case 101: case 102: case 103: case 104:
            case 105: case 106: case 107: case 108: case 109: case 110: case 111:
            case 112: case 113: case 114: case 115: case 116: case 117: case 118:
            case 119: case 120: case 121: case 122:
                return $this->readName($position);
            // -
            case 45:
            // 0-9
            case 48: case 49: case 50: case 51: case 52:
            case 53: case 54: case 55: case 56: case 57:
                return $this->readNumber($position, $code);
            // "
            case 34:
                return $this->readString($position);
        }

        // @todo Throw proper exception.
        throw new \Exception('Unexpected character.');
    }

    /**
     * Reads a number token from the source file, either a float or an int
     * depending on whether a decimal point appears.
     *
     * Int:   -?(0|[1-9][0-9]*)
     * Float: -?(0|[1-9][0-9]*)\.[0-9]+(e-?[0-9]+)?
     *
     * @param int $start
     * @param int $code
     *
     * @return \Fubhy\GraphQL\Language\Token
     *
     * @throws \Exception
     */
    protected function readNumber($start, $code)
    {
        $position = $start;
        $type = Token::INT_TYPE;

        if ($code === 45) { // -
            $code = $this->charCodeAt(++$position);
        }

        if ($code === 48) { // 0
            $code = $this->charCodeAt(++$position);
        } elseif ($code >= 49 && $code <= 57) { // 1 - 9
            do {
                $code = $this->charCodeAt(++$position);
            } while ($code >= 48 && $code <= 57); // 0 - 9
        } else {
            // @todo Throw proper exception.
            throw new \Exception('Invalid number.');
        }

        if ($code === 46) { // .
            $type = Token::FLOAT_TYPE;

            $code = $this->charCodeAt(++$position);
            if ($code >= 48 && $code <= 57) { // 0 - 9
                do {
                    $code = $this->charCodeAt(++$position);
                } while ($code >= 48 && $code <= 57); // 0 - 9
            } else {
                // @todo Throw proper exception.
                throw new \Exception('Invalid number.');
            }

            if ($code === 69 || $code === 101) { // E e
                $code = $this->charCodeAt(++$position);
                if ($code === 43 || $code === 45) { // + -
                    $code = $this->charCodeAt(++$position);
                }
                if ($code >= 48 && $code <= 57) { // 0 - 9
                    do {
                        $code = $this->charCodeAt(++$position);
                    } while ($code >= 48 && $code <= 57); // 0 - 9
                } else {
                    // @todo Throw proper exception.
                    throw new \Exception('Invalid number.');
                }
            }
        }

        $body = $this->source->getBody();
        $value = mb_substr($body, $start, $position - $start, 'UTF-8');
        return new Token($type, $start, $position, $value);
    }

    /**
     * @param int $start
     *
     * @return \Fubhy\GraphQL\Language\Token
     *
     * @throws \Exception
     */
    protected function readString($start)
    {
        $position = $start + 1;
        $chunk = $position;
        $length = $this->source->getLength();
        $body = $this->source->getBody();
        $code = NULL;
        $value = '';

        while (
            $position < $length &&
            ($code = $this->charCodeAt($position)) &&
            $code !== 34 &&
            $code !== 10 && $code !== 13 && $code !== 0x2028 && $code !== 0x2029
        ) {
            ++$position;

            if ($code === 92) { // \
                $value .= mb_substr($body, $chunk, $position - 1 - $chunk, 'UTF-8');
                $code = $this->charCodeAt($position);

                switch ($code) {
                    case 34:
                        $value .= '"';
                        break;
                    case 47:
                        $value .= '\/';
                        break;
                    case 92:
                        $value .= '\\';
                        break;
                    case 98:
                        $value .= '\b';
                        break;
                    case 102:
                        $value .= '\f';
                        break;
                    case 110:
                        $value .= '\n';
                        break;
                    case 114:
                        $value .= '\r';
                        break;
                    case 116:
                        $value .= '\t';
                        break;
                    case 117:
                        $charCode = $this->uniCharCode(
                            $this->charCodeAt($position + 1),
                            $this->charCodeAt($position + 2),
                            $this->charCodeAt($position + 3),
                            $this->charCodeAt($position + 4)
                        );

                        if ($charCode < 0) {
                            // @todo Throw proper exception.
                            throw new \Exception('Bad character escape sequence.');
                        }

                        $value .= $this->fromCharCode($charCode);
                        $position += 4;
                        break;
                    default:
                        // @todo Throw proper exception.
                        throw new \Exception('Bad character escape sequence.');
                }

                ++$position;
                $chunk = $position;
            }
        }

        if ($code !== 34) {
            // @todo Throw proper exception.
            throw new \Exception('Unterminated string.');
        }

        $value .= mb_substr($body, $chunk, $position - $chunk, 'UTF-8');
        return new Token(Token::STRING_TYPE, $start, $position + 1, $value);
    }

    /**
     * Reads an alphanumeric + underscore name from the source.
     *
     * [_A-Za-z][_0-9A-Za-z]*
     *
     * @param int $position
     *
     * @return \Fubhy\GraphQL\Language\Token
     */
    protected function readName($position)
    {
        $end = $position + 1;
        $length = $this->source->getLength();
        $body = $this->source->getBody();

        while (
            $end < $length &&
            ($code = $this->charCodeAt($end)) &&
            (
                $code === 95 || // _
                $code >= 48 && $code <= 57 || // 0-9
                $code >= 65 && $code <= 90 || // A-Z
                $code >= 97 && $code <= 122 // a-z
            )
        ) {
            ++$end;
        }

        $value = mb_substr($body, $position, $end - $position, 'UTF-8');
        return new Token(Token::NAME_TYPE, $position, $end, $value);
    }

    /**
     * Implementation of JavaScript's String.prototype.charCodeAt function.
     *
     * @param int $index
     *
     * @return null|number
     */
    protected function charCodeAt($index)
    {
        $body = $this->source->getBody();
        $char = mb_substr($body, $index, 1, 'UTF-8');

        if (mb_check_encoding($char, 'UTF-8')) {
            return hexdec(bin2hex(mb_convert_encoding($char, 'UTF-32BE', 'UTF-8')));
        } else {
            return NULL;
        }
    }

    /**
     * Implementation of JavaScript's String.fromCharCode function.
     *
     * @param int $code
     *
     * @return string
     */
    protected function fromCharCode($code)
    {
        $code = intval($code);
        return mb_convert_encoding("&#{$code};", 'UTF-8', 'HTML-ENTITIES');
    }

    /**
     * Converts four hexadecimal chars to the integer that the
     * string represents. For example, uniCharCode('0','0','0','f')
     * will return 15, and uniCharCode('0','0','f','f') returns 255.
     *
     * Returns a negative number on error, if a char was invalid.
     *
     * This is implemented by noting that char2hex() returns -1 on error,
     * which means the result of ORing the char2hex() will also be negative.
     *
     * @param $a
     * @param $b
     * @param $c
     * @param $d
     *
     * @return int
     */
    protected function uniCharCode($a, $b, $c, $d)
    {
        return $this->char2hex($a) << 12 | $this->char2hex($b) << 8 | $this->char2hex($c) << 4 | $this->char2hex($d);
    }

    /**
     * Converts a hex character to its integer value.
     * '0' becomes 0, '9' becomes 9
     * 'A' becomes 10, 'F' becomes 15
     * 'a' becomes 10, 'f' becomes 15
     *
     * Returns -1 on error.
     *
     * @param $a
     *
     * @return int
     */
    protected function char2hex($a)
    {
        return
            $a >= 48 && $a <= 57 ? $a - 48 : // 0-9
                ($a >= 65 && $a <= 70 ? $a - 55 : // A-F
                    ($a >= 97 && $a <= 102 ? $a - 87 : -1)); // a-f
    }
}
