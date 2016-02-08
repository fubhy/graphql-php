<?php

namespace Fubhy\GraphQL\Language;

/**
 * A representation of a lexed Token.
 */
class Token
{
    const EOF_TYPE = 1;
    const BANG_TYPE = 2;
    const DOLLAR_TYPE = 3;
    const PAREN_L_TYPE = 4;
    const PAREN_R_TYPE = 5;
    const SPREAD_TYPE = 6;
    const COLON_TYPE = 7;
    const EQUALS_TYPE = 8;
    const AT_TYPE = 9;
    const BRACKET_L_TYPE = 10;
    const BRACKET_R_TYPE = 11;
    const BRACE_L_TYPE = 12;
    const PIPE_TYPE = 13;
    const BRACE_R_TYPE = 14;
    const NAME_TYPE = 15;
    const VARIABLE_TYPE = 16;
    const INT_TYPE = 17;
    const FLOAT_TYPE = 18;
    const STRING_TYPE = 19;

    /**
     * The token type.
     *
     * @var int
     */
    protected $type;

    /**
     * The start position.
     *
     * @var int
     */
    protected $start;

    /**
     * The end position.
     *
     * @var int
     */
    protected $end;

    /**
     * The token value.
     *
     * @var string|null
     */
    protected $value;

    /**
     * Constructor.
     *
     * @param int $type
     *  The token type.
     * @param int $start
     *  The start position.
     * @param int $end
     *  The end position.
     * @param null|string $value
     *  The token value.
     */
    public function __construct($type, $start, $end, $value = NULL)
    {
        $this->type = $type;
        $this->start = $start;
        $this->end = $end;
        $this->value = $value;
    }

    /**
     * Gets the token type.
     *
     * @return int
     *  The token type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets the start position.
     *
     * @return int
     *  The start position.
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Gets the end position.
     *
     * @return int
     *  The end position.
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Gets the token value.
     *
     * @return string|null
     *  The token value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Gets the token value.
     *
     * @return string|null
     *  The token value
     */
    public function getDescription()
    {
        return self::typeToString($this->type) . ($this->value ? ' "' . $this->value  . '"' : '');
    }

    /**
     * Returns a string representation of the token.
     *
     * @return string
     *  A string representation of the token
     */
    public function __toString()
    {
        $description = self::typeToString($this->type);
        return $this->value ? sprintf('%s "%s"', $description, $this->value) : $description;
    }

    /**
     * Returns the constant representation (internal) of a given type.
     *
     * @param int $type
     *  The type as an integer
     *
     * @return string
     *  The string representation
     */
    public static function typeToString($type)
    {
        switch ($type) {
            case self::EOF_TYPE:
                return 'EOF';
            case self::BANG_TYPE:
                return '!';
            case self::DOLLAR_TYPE:
                return '$';
            case self::PAREN_L_TYPE:
                return '(';
            case self::PAREN_R_TYPE:
                return ')';
            case self::SPREAD_TYPE:
                return '...';
            case self::COLON_TYPE:
                return ':';
            case self::EQUALS_TYPE:
                return '=';
            case self::AT_TYPE:
                return '@';
            case self::BRACKET_L_TYPE:
                return '[';
            case self::BRACKET_R_TYPE:
                return ']';
            case self::BRACE_L_TYPE:
                return '{';
            case self::PIPE_TYPE:
                return '|';
            case self::BRACE_R_TYPE:
                return '}';
            case self::NAME_TYPE:
                return 'Name';
            case self::VARIABLE_TYPE:
                return 'Variable';
            case self::INT_TYPE:
                return 'Int';
            case self::FLOAT_TYPE:
                return 'Float';
            case self::STRING_TYPE:
                return 'String';
            default:
                throw new \LogicException(sprintf('Token of type "%s" does not exist.', $type));
        }
    }
}
