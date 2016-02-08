<?php

namespace Fubhy\GraphQL\Type\Definition\Types;

/**
 * Union Type Definition.
 *
 * When a field can return one of a heterogeneous set of types, a Union type is
 * used to describe what types are possible as well as providing a function to
 * determine which type is actually used when the field is resolved.
 */
class UnionType extends AbstractType implements
    OutputTypeInterface,
    CompositeTypeInterface,
    NullableTypeInterface,
    UnmodifiedTypeInterface
{
    /**
     * @var array
     */
    protected $types;

    /**
     * @var callable|null
     */
    protected $typeResolver;

    /**
     * Constructor.
     *
     * @param string $name
     * @param array $types
     * @param callable|null $typeResolver
     * @param string|null $description
     */
    public function __construct($name, array $types = [], callable $typeResolver = NULL, $description = NULL)
    {
        parent::__construct($name, $description);

        $nonObjectTypes = array_filter($types, function (TypeInterface $type) {
            return !($type instanceof ObjectType);
        });

        if (!empty($nonObjectTypes)) {
            $nonObjectTypes = implode(', ', array_map(function ($type) {
                return (string) $type;
            }, $nonObjectTypes));

            throw new \LogicException(sprintf('Union %s may only contain object types, it cannot contain: %s.', (string) $this, $nonObjectTypes));
        }

        $this->types = $types;
        $this->typeResolver = $typeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getPossibleTypes()
    {
        return $this->types;
    }

    /**
     * {@inheritdoc}
     */
    public function addPossibleType(ObjectType $type)
    {
        $this->types = array_unique($this->types + [$type]);
    }

    /**
     * {@inheritdoc}
     */
    public function isPossibleType(ObjectType $type)
    {
        return in_array($type, $this->types, TRUE);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveType($value)
    {
        if (isset($this->typeResolver)) {
            return call_user_func($this->typeResolver, $value);
        }

        return $this->getTypeOf($value);
    }
}
