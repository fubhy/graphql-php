<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class NonNullType extends Node implements TypeInterface
{
    const KIND = Node::KIND_NON_NULL_TYPE;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Node\TypeInterface $type
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct(TypeInterface $type, Location $location = NULL)
    {
        if (!($type instanceof NamedType || $type instanceof ListType)) {
            throw new \InvalidArgumentException(sprintf('Invalid type %s.', get_class($type)));
        }

        parent::__construct($location, ['type' => $type]);
    }
}
