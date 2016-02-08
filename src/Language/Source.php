<?php

namespace Fubhy\GraphQL\Language;

/**
 * A representation of source input to GraphQL.
 *
 * The name is optional, but is mostly useful for clients who store GraphQL
 * documents in source files; for example, if the GraphQL input is in a file
 * Foo.graphql, it might be useful for name to be "Foo.graphql".
 */
class Source
{
    /**
     * @var string
     */
    protected $body;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $length;

    /**
     * Constructor.
     *
     * @param string $body
     * @param string $name
     */
    public function __construct($body, $name = 'GraphQL')
    {
        $this->body = $body;
        $this->name = $name;
        $this->length = mb_strlen($body);
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Takes a Source and a UTF-8 character offset, and returns the
     * corresponding line and column as a SourceLocation.
     *
     * @param $position
     *
     * @return \Fubhy\GraphQL\Language\SourceLocation
     */
    public function getLocation($position)
    {
        $pattern = '/\r\n|[\n\r\u2028\u2029]/g';
        $subject = mb_substr($this->body, 0, $position, 'UTF-8');

        preg_match_all($pattern, $subject, $matches, PREG_OFFSET_CAPTURE);
        $location = array_reduce($matches[0], function ($carry, $match) use ($position) {
            return [
                $carry[0] + 1,
                $position + 1 - ($match[1] + mb_strlen($match[0], 'UTF-8'))
            ];
        }, [1, $position + 1]);

        return new SourceLocation($location[0], $location[1]);
    }
}
