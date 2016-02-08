<?php

namespace Fubhy\GraphQL\Tests\Tests\Language;

use Fubhy\GraphQL\Language\Lexer;
use Fubhy\GraphQL\Language\Source;
use Fubhy\GraphQL\Language\Token;

class LexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider skipsWhitespacesProvider()
     */
    public function testSkipsWhitespaces($body, $expected)
    {
        $this->assertEquals($expected, $this->lexOne($body));
    }

    public function skipsWhitespacesProvider()
    {
        $body1 = '

    foo


';

        $body2 = '
    #comment
    foo#comment
';

        $body3 = ',,,foo,,,';

        return [
            [$body1, new Token(Token::NAME_TYPE, 6, 9, 'foo')],
            [$body2, new Token(Token::NAME_TYPE, 18, 21, 'foo')],
            [$body3, new Token(Token::NAME_TYPE, 3, 6, 'foo')],
        ];
    }

    public function testErrorsRespectWhitespace()
    {
        // @todo Implement this after porting exceptions.
    }

    /**
     * @dataProvider lexesStringsProvider()
     */
    public function testLexesStrings($body, $expected)
    {
        $this->assertEquals($expected, $this->lexOne($body));
    }

    public function lexesStringsProvider()
    {
        return [
            ['"simple"', new Token(Token::STRING_TYPE, 0, 8, 'simple')],
            ['"quote \\""', new Token(Token::STRING_TYPE, 0, 10, 'quote "')],
            ['" white space "', new Token(Token::STRING_TYPE, 0, 15, ' white space ')],
            ['"escaped \\n\\r\\b\\t\\f"', new Token(Token::STRING_TYPE, 0, 20, 'escaped \n\r\b\t\f')],
            ['"slashes \\\\ \\/"', new Token(Token::STRING_TYPE, 0, 15, 'slashes \\ \/')],
            ['"unicode яуц"', new Token(Token::STRING_TYPE, 0, 13, 'unicode яуц')],
            ['"unicode \u1234\u5678\u90AB\uCDEF"', new Token(Token::STRING_TYPE, 0, 34, 'unicode ሴ噸邫췯')],
        ];
    }

    public function testLexReportsUsefulStringErrors()
    {
        // @todo Implement this after porting exceptions.
    }

    /**
     * @dataProvider lexesNumbersProvider()
     */
    public function testLexesNumbers($body, $expected)
    {
        $this->assertEquals($expected, $this->lexOne($body));
    }

    public function lexesNumbersProvider()
    {
        return [
            ['"simple"', new Token(Token::STRING_TYPE, 0, 8, 'simple')],
            ['" white space "', new Token(Token::STRING_TYPE, 0, 15, ' white space ')],
            ['"escaped \\n\\r\\b\\t\\f"', new Token(Token::STRING_TYPE, 0, 20, 'escaped \n\r\b\t\f')],
            ['"slashes \\\\ \\/"', new Token(Token::STRING_TYPE, 0, 15, 'slashes \\ \/')],
            ['"unicode \\u1234\\u5678\\u90AB\\uCDEF"', new Token(Token::STRING_TYPE, 0, 34, 'unicode ሴ噸邫췯')],
            ['4', new Token(Token::INT_TYPE, 0, 1, '4')],
            ['4.123', new Token(Token::FLOAT_TYPE, 0, 5, '4.123')],
            ['-4', new Token(Token::INT_TYPE, 0, 2, '-4')],
            ['9', new Token(Token::INT_TYPE, 0, 1, '9')],
            ['0', new Token(Token::INT_TYPE, 0, 1, '0')],
            ['00', new Token(Token::INT_TYPE, 0, 1, '0')],
            ['-4.123', new Token(Token::FLOAT_TYPE, 0, 6, '-4.123')],
            ['0.123', new Token(Token::FLOAT_TYPE, 0, 5, '0.123')],
            ['-1.123e4', new Token(Token::FLOAT_TYPE, 0, 8, '-1.123e4')],
            ['-1.123e-4', new Token(Token::FLOAT_TYPE, 0, 9, '-1.123e-4')],
            ['-1.123e4567', new Token(Token::FLOAT_TYPE, 0, 11, '-1.123e4567')],
        ];
    }

    public function testReportsUsefulNumberErrors()
    {
        // @todo Implement this after porting exceptions.
    }

    /**
     * @dataProvider lexesPunctuationProvider()
     */
    public function testLexesPunctuation($body, $expected)
    {
        $this->assertEquals($expected, $this->lexOne($body));
    }

    public function lexesPunctuationProvider()
    {
        return [
            ['!', new Token(Token::BANG_TYPE, 0, 1, NULL)],
            ['$', new Token(Token::DOLLAR_TYPE, 0, 1, NULL)],
            ['(', new Token(Token::PAREN_L_TYPE, 0, 1, NULL)],
            [')', new Token(Token::PAREN_R_TYPE, 0, 1, NULL)],
            ['...', new Token(Token::SPREAD_TYPE, 0, 3, NULL)],
            [':', new Token(Token::COLON_TYPE, 0, 1, NULL)],
            ['=', new Token(Token::EQUALS_TYPE, 0, 1, NULL)],
            ['@', new Token(Token::AT_TYPE, 0, 1, NULL)],
            ['[', new Token(Token::BRACKET_L_TYPE, 0, 1, NULL)],
            [']', new Token(Token::BRACKET_R_TYPE, 0, 1, NULL)],
            ['{', new Token(Token::BRACE_L_TYPE, 0, 1, NULL)],
            ['}', new Token(Token::BRACE_R_TYPE, 0, 1, NULL)],
            ['|', new Token(Token::PIPE_TYPE, 0, 1, NULL)],
        ];
    }

    public function testReportsUsefulUnknownCharErrors()
    {
        // @todo Implement this after porting exceptions.
    }

    /**
     * @param string $body
     *
     * @return \Fubhy\GraphQL\Language\Token
     */
    protected function lexOne($body)
    {
        $lexer = new Lexer(new Source($body));
        return $lexer->readToken(0);
    }
}
