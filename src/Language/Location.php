<?php

namespace Fubhy\GraphQL\Language;

/**
 * Contains a range of UTF-8 character offsets that identify the region of the
 * source from which the AST derived.
 */
class Location
{
    /**
     * @var int
     */
    protected $start;

    /**
     * @var int
     */
    protected $end;

    /**
     * @var \Fubhy\GraphQL\Language\Source|null
     */
    public $source;

    /**
     * Constructor.
     *
     * @param $start
     * @param $end
     * @param \Fubhy\GraphQL\Language\Source|null $source
     */
    public function __construct($start, $end, Source $source = NULL)
    {
        $this->start = $start;
        $this->end = $end;
        $this->source = $source;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @return Source|null
     */
    public function getSource()
    {
        return $this->source;
    }
}
