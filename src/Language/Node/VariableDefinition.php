<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class VariableDefinition extends Node
{
    const KIND = Node::KIND_VARIABLE_DEFINITION;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Node\Variable $variable
     * @param \Fubhy\GraphQL\Language\Node\TypeInterface $type
     * @param \Fubhy\GraphQL\Language\Node\ValueInterface $defaultValue
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct(
        Variable $variable,
        TypeInterface $type,
        ValueInterface $defaultValue = NULL,
        Location $location = NULL
    ) {
        parent::__construct($location, [
            'variable' => $variable,
            'type' => $type,
            'defaultValue' => $defaultValue,
        ]);
    }
}
