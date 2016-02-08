<?php

namespace Fubhy\GraphQL\Type\Directives;

use Fubhy\GraphQL\Type\Definition\FieldArgument;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Used to conditionally include fields.
 */
class IncludeDirective extends Directive
{
    /**
     * @var string
     */
    protected $name = 'include';

    /**
     * @var string
     */
    protected $description = 'Directs the executor to omit this field if the argument provided is false.';

    /**
     * @var bool
     */
    protected $onOperation = FALSE;

    /**
     * @var bool
     */
    protected $onFragment = TRUE;

    /**
     * @var bool
     */
    protected $onField = TRUE;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->arguments = [
            new FieldArgument([
                'name' => 'if',
                'type' => new NonNullModifier(Type::booleanType()),
                'description' => 'Included if true.'
            ]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        if (!isset($this->type)) {
            $this->type = new NonNullModifier(Type::booleanType());
        }

        return $this->type;
    }
}
