<?php

namespace Fubhy\GraphQL\Language;

/**
 * Represents a location in a Source.
 */
class SourceLocation
{
    /**
     * @var int
     */
    protected $line;

    /**
     * @var int
     */
    protected $column;

    /**
     * Constructor.
     *
     * @param int $line
     * @param int $column
     */
    public function __construct($line, $column)
    {
        $this->line = $line;
        $this->column = $column;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return int
     */
    public function getColumn()
    {
        return $this->column;
    }
}
