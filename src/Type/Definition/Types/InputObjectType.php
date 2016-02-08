<?php

namespace Fubhy\GraphQL\Type\Definition\Types;

use Fubhy\GraphQL\Type\Definition\InputObjectField;

/**
 * Input Object Type Definition.
 *
 * An input object defines a structured collection of fields which may be
 * supplied to a field argument.
 *
 * Using `NonNull` will ensure that a value must be provided by the query.
 */
class InputObjectType extends Type implements
    InputTypeInterface,
    NullableTypeInterface,
    UnmodifiedTypeInterface
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * Constructor.
     *
     * @param string $name
     * @param array $fields
     * @param string|null $description
     */
    public function __construct($name, array $fields = [], $description = NULL)
    {
        parent::__construct($name, $description);

        $this->fields = $fields;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\InputObjectField[]
     */
    public function getFields()
    {
        if (!isset($this->fieldMap)) {
            $this->fieldMap = [];
            foreach ($this->fields as $name => $field) {
                if (!isset($field['name'])) {
                    $field['name'] = $name;
                }

                $this->fieldMap[$name] = new InputObjectField($field);
            }
        }

        return $this->fieldMap;
    }
}
