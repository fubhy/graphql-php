<?php

namespace Fubhy\GraphQL\Type\Definition\Types;

abstract class AbstractType extends Type implements AbstractTypeInterface {

    /**
     * @param mixed $value
     *
     * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType|null
     *
     * @throws \Exception
     */
    public function getTypeOf($value)
    {
        foreach ($this->getPossibleTypes() as $type) {
            if ($isTypeOf = $type->isTypeOf($value)) {
                return $type;
            }

            if (!isset($isTypeOf)) {
                throw new \Exception(sprintf(
                    'Non-Object Type %s does not implement resolveType and Object ' .
                    'Type %s does not implement isTypeOf. There is no way to ' .
                    'determine if a value is of this type.', $this->getName(), $type->getName()
                ));
            }
        }

        return NULL;
    }
}
