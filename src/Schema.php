<?php

namespace Fubhy\GraphQL;

use Fubhy\GraphQL\Type\Definition\FieldArgument;
use Fubhy\GraphQL\Type\Definition\Types\InterfaceType;
use Fubhy\GraphQL\Type\Definition\Types\ModifierInterface;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;
use Fubhy\GraphQL\Type\Definition\Types\TypeInterface;
use Fubhy\GraphQL\Type\Definition\Types\UnionType;
use Fubhy\GraphQL\Type\Directives\Directive;
use Fubhy\GraphQL\Type\Introspection;

/**
 * Schema Definition
 *
 * A Schema is created by supplying the root types of each type of operation,
 * query and mutation (optional). A schema definition is then supplied to the
 * validator and executor.
 */
class Schema
{
    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    protected $mutationType;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    protected $queryType;

    /**
     * @var \Fubhy\GraphQL\Type\Directives\DirectiveInterface[]
     */
    protected $directives;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Type\Definition\Types\TypeInterface $queryType
     * @param \Fubhy\GraphQL\Type\Definition\Types\TypeInterface|null $mutationType
     */
    public function __construct(TypeInterface $queryType, TypeInterface $mutationType = NULL)
    {
        $this->queryType = $queryType;
        $this->mutationType = $mutationType;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    public function getQueryType()
    {
        return $this->queryType;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType|null
     */
    public function getMutationType()
    {
        return $this->mutationType;
    }

    /**
     * @param string $name
     *
     * @return \Fubhy\GraphQL\Type\Directives\DirectiveInterface|null
     */
    public function getDirective($name)
    {
        foreach ($this->getDirectives() as $directive) {
            if ($directive->getName() === $name) {
                return $directive;
            }
        }
        return NULL;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Directives\DirectiveInterface[]
     */
    public function getDirectives()
    {
        if (!isset($this->directives)) {
            $include = Directive::includeDirective();
            $skip = Directive::skipDirective();

            $this->directives = [
                $include->getName() => $include,
                $skip->getName() => $skip,
            ];
        }

        return $this->directives;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface[]
     */
    public function getTypeMap()
    {
        if (!isset($this->typeMap)) {
            $input = [$this->getQueryType(), $this->getMutationType(), Introspection::schema()];
            $this->typeMap = array_reduce($input, [$this, 'typeMapReducer'], []);

            $this->typeMap['Boolean'] = Type::booleanType();
            $this->typeMap['Float'] = Type::floatType();
            $this->typeMap['Id'] = Type::idType();
            $this->typeMap['Integer'] = Type::intType();
            $this->typeMap['String'] = Type::stringType();
        }

        return $this->typeMap;
    }

    /**
     * @param string $name
     *
     * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface
     */
    public function getType($name)
    {
        $map = $this->getTypeMap();
        return isset($map[$name]) ? $map[$name] : NULL;
    }

    /**
     * @param \Fubhy\GraphQL\Type\Definition\Types\TypeInterface[] $map
     * @param mixed $type
     *
     * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface[]
     */
    protected function typeMapReducer(array $map, $type)
    {
        if ($type instanceof ModifierInterface) {
            return $this->typeMapReducer($map, $type->getWrappedType());
        }

        if (!$type instanceof TypeInterface || !empty($map[$type->getName()])) {
            return $map;
        }

        $reducedMap = array_merge($map, [$type->getName() => $type]);
        if ($type instanceof InterfaceType || $type instanceof UnionType) {
            $reducedMap = array_reduce(
                $type->getPossibleTypes(), [$this, 'typeMapReducer'], $reducedMap
            );
        }

        if ($type instanceof ObjectType) {
            $reducedMap = array_reduce(
                $type->getInterfaces(), [$this, 'typeMapReducer'], $reducedMap
            );
        }

        if ($type instanceof ObjectType || $type instanceof InterfaceType) {
            foreach ($type->getFields() as $fieldName => $field) {
                if (!($args = $field->getArguments())) {
                    // No arguments.
                }

                $reducedMap = array_reduce(array_map(function (FieldArgument $arg) {
                    return $arg->getType();
                }, $args), [$this, 'typeMapReducer'], $reducedMap);

                $reducedMap = $this->typeMapReducer($reducedMap, $field->getType());
            }
        }

        return $reducedMap;
    }
}
