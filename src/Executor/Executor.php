<?php

namespace Fubhy\GraphQL\Executor;

use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Language\Node\Document;
use Fubhy\GraphQL\Language\Node\Field;
use Fubhy\GraphQL\Language\Node\FragmentDefinition;
use Fubhy\GraphQL\Language\Node\OperationDefinition;
use Fubhy\GraphQL\Language\Node\SelectionSet;
use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\FieldDefinition;
use Fubhy\GraphQL\Type\Definition\Types\EnumType;
use Fubhy\GraphQL\Type\Definition\Types\InterfaceType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\ScalarType;
use Fubhy\GraphQL\Type\Definition\Types\TypeInterface;
use Fubhy\GraphQL\Type\Definition\Types\UnionType;
use Fubhy\GraphQL\Type\Directives\Directive;
use Fubhy\GraphQL\Type\Introspection;
use Fubhy\GraphQL\Utility\TypeInfo;

/**
 * Terminology
 *
 * "Definitions" are the generic name for top-level statements in the document.
 * Examples of this include:
 * 1) Operations (such as a query)
 * 2) Fragments
 *
 * "Operations" are a generic name for requests in the document.
 * Examples of this include:
 * 1) query,
 * 2) mutation
 *
 * "Selections" are the statements that can appear legally and at
 * single level of the query. These include:
 * 1) field references e.g "a"
 * 2) fragment "spreads" e.g. "...c"
 * 3) inline fragment "spreads" e.g. "...on Type { a }"
 */
class Executor
{
    protected static $UNDEFINED;

    /**
     * @param \Fubhy\GraphQL\Schema $schema
     * @param $root
     * @param \Fubhy\GraphQL\Language\Node\Document $ast
     * @param string|null $operation
     * @param array|null $args
     *
     * @return array
     */
    public static function execute(Schema $schema, $root, Document $ast, $operation = NULL, array $args = NULL)
    {
        if (!self::$UNDEFINED) {
            self::$UNDEFINED = new \stdClass();
        }

        try {
            $errors = new \ArrayObject();
            $context = self::buildExecutionContext($schema, $root, $ast, $operation, $args, $errors);
            $data = self::executeOperation($context, $root, $context->operation);
        } catch (\Exception $e) {
            $errors[] = $e;
        }

        $result = ['data' => isset($data) ? $data : NULL];
        if (count($errors) > 0) {
            $result['errors'] = $errors->getArrayCopy();
        }

        return $result;
    }

    /**
     * Constructs a ExecutionContext object from the arguments passed to
     * execute, which we will pass throughout the other execution methods.
     *
     * @param Schema $schema
     * @param $root
     * @param Document $ast
     * @param string|null $operationName
     * @param array $args
     * @param $errors
     *
     * @return ExecutionContext
     *
     * @throws \Exception
     */
    protected static function buildExecutionContext(Schema $schema, $root, Document $ast, $operationName = NULL, array $args = NULL, &$errors)
    {
        $operations = [];
        $fragments = [];

        foreach ($ast->get('definitions') as $statement) {
            switch ($statement::KIND) {
                case Node::KIND_OPERATION_DEFINITION:
                    $operations[$statement->get('name') ? $statement->get('name')->get('value') : ''] = $statement;
                    break;

                case Node::KIND_FRAGMENT_DEFINITION:
                    $fragments[$statement->get('name')->get('value')] = $statement;
                    break;
            }
        }

        if (!$operationName && count($operations) !== 1) {
            throw new \Exception('Must provide operation name if query contains multiple operations');
        }

        $name = $operationName ?: key($operations);
        if (!isset($operations[$name])) {
            throw new \Exception('Unknown operation name: ' . $name);
        }

        $operation = $operations[$name];
        $variables = Values::getVariableValues($schema, $operation->get('variableDefinitions') ?: [], $args ?: []);
        $context = new ExecutionContext($schema, $fragments, $root, $operation, $variables, $errors);

        return $context;
    }

    /**
     * Implements the "Evaluating operations" section of the spec.
     */
    protected static function executeOperation(ExecutionContext $context, $root, OperationDefinition $operation)
    {
        $type = self::getOperationRootType($context->schema, $operation);
        $fields = self::collectFields($context, $type, $operation->get('selectionSet'), new \ArrayObject(), new \ArrayObject());
        if ($operation->get('operation') === 'mutation') {
            return self::executeFieldsSerially($context, $type, $root, $fields->getArrayCopy());
        }

        return self::executeFields($context, $type, $root, $fields);
    }


    /**
     * Extracts the root type of the operation from the schema.
     *
     * @param Schema $schema
     * @param OperationDefinition $operation
     *
     * @return ObjectType
     *
     * @throws \Exception
     */
    protected static function getOperationRootType(Schema $schema, OperationDefinition $operation)
    {
        switch ($operation->get('operation')) {
            case 'query':
                return $schema->getQueryType();
            case 'mutation':
                $mutationType = $schema->getMutationType();
                if (!$mutationType) {
                    throw new \Exception('Schema is not configured for mutations.');
                }

                return $mutationType;
        }

        throw new \Exception('Can only execute queries and mutations.');
    }

    /**
     * Implements the "Evaluating selection sets" section of the spec for "write" mode.
     */
    protected static function executeFieldsSerially(ExecutionContext $context, ObjectType $parent, $source, $fields)
    {
        $results = [];
        foreach ($fields as $response => $asts) {
            $result = self::resolveField($context, $parent, $source, $asts);

            if ($result !== self::$UNDEFINED) {
                // Undefined means that field is not defined in schema.
                $results[$response] = $result;
            }
        }

        return $results;
    }

    /**
     * Implements the "Evaluating selection sets" section of the spec for "read" mode.
     *
     * @param ExecutionContext $context
     * @param ObjectType $parent
     * @param $source
     * @param $fields
     *
     * @return array
     */
    protected static function executeFields(ExecutionContext $context, ObjectType $parent, $source, $fields)
    {
        return self::executeFieldsSerially($context, $parent, $source, $fields);
    }

    /**
     * Given a selectionSet, adds all of the fields in that selection to
     * the passed in map of fields, and returns it at the end.
     *
     * @param ExecutionContext $context
     * @param ObjectType $type
     * @param SelectionSet $set
     * @param $fields
     * @param $visited
     *
     * @return \ArrayObject
     */
    protected static function collectFields(ExecutionContext $context, ObjectType $type, SelectionSet $set, $fields, $visited)
    {
        $count = count($set->get('selections'));
        for ($i = 0; $i < $count; $i++) {
            $selection = $set->get('selections')[$i];
            switch ($selection::KIND) {
                case Node::KIND_FIELD:
                    if (!self::shouldIncludeNode($context, $selection->get('directives'))) {
                        continue;
                    }

                    $name = self::getFieldEntryKey($selection);
                    if (!isset($fields[$name])) {
                        $fields[$name] = new \ArrayObject();
                    }

                    $fields[$name][] = $selection;
                    break;

                case Node::KIND_INLINE_FRAGMENT:
                    if (!self::shouldIncludeNode($context, $selection->get('directives')) || !self::doesFragmentConditionMatch($context, $selection, $type)) {
                        continue;
                    }

                    self::collectFields(
                        $context,
                        $type,
                        $selection->get('selectionSet'),
                        $fields,
                        $visited
                    );

                    break;

                case Node::KIND_FRAGMENT_SPREAD:
                    $fragName = $selection->get('name')->get('value');
                    if (!empty($visited[$fragName]) || !self::shouldIncludeNode($context, $selection->get('directives'))) {
                        continue;
                    }

                    $visited[$fragName] = TRUE;
                    $fragment = isset($context->fragments[$fragName]) ? $context->fragments[$fragName] : NULL;
                    if (!$fragment || !self::shouldIncludeNode($context, $fragment->get('directives')) || !self::doesFragmentConditionMatch($context, $fragment, $type)) {
                        continue;
                    }

                    self::collectFields($context, $type, $fragment->get('selectionSet'), $fields, $visited);

                    break;
            }
        }

        return $fields;
    }

    /**
     * Determines if a field should be included based on @if and @unless directives.
     */
    protected static function shouldIncludeNode(ExecutionContext $exeContext, $directives)
    {
        $skip = Directive::skipDirective();
        $include = Directive::includeDirective();

        foreach ($directives as $directive) {
            if ($directive->get('name')->get('value') === $skip->getName()) {
                $values = Values::getArgumentValues($skip->getArguments(), $directive->get('arguments'), $exeContext->variables);
                return empty($values['if']);
            }

            if ($directive->get('name')->get('value') === $include->getName()) {
                $values = Values::getArgumentValues($skip->getArguments(), $directive->get('arguments'), $exeContext->variables);
                return !empty($values['if']);
            }
        }

        return TRUE;
    }

    /**
     * Determines if a fragment is applicable to the given type.
     *
     * @param ExecutionContext $context
     * @param $fragment
     * @param ObjectType $type
     *
     * @return bool
     */
    protected static function doesFragmentConditionMatch(ExecutionContext $context, $fragment, ObjectType $type)
    {
        $typeCondition = $fragment->get('typeCondition');
        if (!$typeCondition) {
            return TRUE;
        }

        $conditionalType = TypeInfo::typeFromAST($context->schema, $typeCondition);
        if (empty($conditionalType)) {
            return FALSE;
        }
        if ($conditionalType->getName() === $type->getName()) {
            return TRUE;
        }

        if ($conditionalType instanceof InterfaceType || $conditionalType instanceof UnionType) {
            return $conditionalType->isPossibleType($type);
        }

        return FALSE;
    }

    /**
     * Implements the logic to compute the key of a given field's entry
     *
     * @param Field $node
     *
     * @return string
     */
    protected static function getFieldEntryKey(Field $node)
    {
        return $node->get('alias') ? $node->get('alias')->get('value') : $node->get('name')->get('value');
    }

    /**
     * A wrapper function for resolving the field, that catches the error
     * and adds it to the context's global if the error is not rethrowable.
     *
     * @param ExecutionContext $context
     * @param ObjectType $parent
     * @param $source
     * @param $asts
     *
     * @return array|mixed|null|string
     *
     * @throws \Exception
     */
    protected static function resolveField(ExecutionContext $context, ObjectType $parent, $source, $asts)
    {
        $definition = self::getFieldDefinition($context->schema, $parent, $asts[0]);
        if (!$definition) {
            return self::$UNDEFINED;
        }

        // If the field type is non-nullable, then it is resolved without any
        // protection from errors.
        if ($definition->getType() instanceof NonNullModifier) {
            return self::resolveFieldOrError($context, $parent, $source, $asts, $definition);
        }

        // Otherwise, error protection is applied, logging the error and
        // resolving a null value for this field if one is encountered.
        try {
            $result = self::resolveFieldOrError($context, $parent, $source, $asts, $definition);
            return $result;
        } catch (\Exception $error) {
            $context->errors[] = $error;
            return NULL;
        }
    }

    /**
     * Resolves the field on the given source object.
     *
     * In particular, this figures out the object that the field returns using
     * the resolve function, then calls completeField to coerce scalars or
     * execute the sub selection set for objects.
     *
     * @param ExecutionContext $context
     * @param ObjectType $parent
     * @param $source
     * @param $asts
     * @param FieldDefinition $definition
     *
     * @return array|mixed|null|string
     *
     * @throws \Exception
     */
    protected static function resolveFieldOrError(ExecutionContext $context, ObjectType $parent, $source, $asts, FieldDefinition $definition)
    {
        $ast = $asts[0];
        $type = $definition->getType();
        $resolver = $definition->getResolveCallback() ?: [__CLASS__, 'defaultResolveFn'];
        $data = $definition->getResolveData();
        $args = Values::getArgumentValues($definition->getArguments(), $ast->get('arguments'), $context->variables);

        try {
            // @todo Change the resolver function syntax to use a value object.
            $result = call_user_func($resolver, $source, $args, $context->root, $ast, $type, $parent, $context->schema, $data);
        } catch (\Exception $error) {
            throw $error;
        }

        return self::completeField($context, $type, $asts, $result);
    }

    /**
     * Implements the instructions for completeValue as defined in the
     * "Field entries" section of the spec.
     *
     * If the field type is Non-Null, then this recursively completes the value
     * for the inner type. It throws a field error if that completion returns null,
     * as per the "Nullability" section of the spec.
     *
     * If the field type is a List, then this recursively completes the value
     * for the inner type on each item in the list.
     *
     * If the field type is a Scalar or Enum, ensures the completed value is a legal
     * value of the type by calling the `coerce` method of GraphQL type definition.
     *
     * Otherwise, the field type expects a sub-selection set, and will complete the
     * value by evaluating all sub-selections.
     *
     * @param ExecutionContext $context
     * @param TypeInterface $type
     * @param $asts
     * @param $result
     *
     * @return array|mixed|null|string
     *
     * @throws \Exception
     */
    protected static function completeField(ExecutionContext $context, TypeInterface $type, $asts, &$result)
    {
        // If field type is NonNullModifier, complete for inner type, and throw field error
        // if result is null.
        if ($type instanceof NonNullModifier) {
            $completed = self::completeField($context, $type->getWrappedType(), $asts, $result);

            if ($completed === NULL) {
                throw new \Exception('Cannot return null for non-nullable type.');
            }

            return $completed;
        }

        // If result is null-like, return null.
        if (!isset($result)) {
            return NULL;
        }

        // If field type is List, complete each item in the list with the inner type
        if ($type instanceof ListModifier) {
            $itemType = $type->getWrappedType();

            if (!(is_array($result) || $result instanceof \ArrayObject)) {
                throw new \Exception('User Error: expected iterable, but did not find one.');
            }

            $tmp = [];
            foreach ($result as $item) {
                $tmp[] = self::completeField($context, $itemType, $asts, $item);
            }

            return $tmp;
        }

        // If field type is Scalar or Enum, coerce to a valid value, returning
        // null if coercion is not possible.
        if ($type instanceof ScalarType || $type instanceof EnumType) {
            if (!method_exists($type, 'coerce')) {
                throw new \Exception('Missing coerce method on type.');
            }

            return $type->coerce($result);
        }

        // Field type must be Object, Interface or Union and expect
        // sub-selections.
        $objectType = $type instanceof ObjectType ? $type : ($type instanceof InterfaceType || $type instanceof UnionType ? $type->resolveType($result) : NULL);
        if (!$objectType) {
            return NULL;
        }

        // Collect sub-fields to execute to complete this value.
        $subFieldASTs = new \ArrayObject();
        $visitedFragmentNames = new \ArrayObject();

        $count = count($asts);
        for ($i = 0; $i < $count; $i++) {
            $selectionSet = $asts[$i]->get('selectionSet');

            if ($selectionSet) {
                $subFieldASTs = self::collectFields($context, $objectType, $selectionSet, $subFieldASTs, $visitedFragmentNames);
            }
        }

        return self::executeFields($context, $objectType, $result, $subFieldASTs);
    }

    /**
     * If a resolve function is not given, then a default resolve behavior is used
     * which takes the property of the source object of the same name as the field
     * and returns it as the result, or if it's a function, returns the result
     * of calling that function.
     *
     * @param $source
     * @param $args
     * @param $root
     * @param $ast
     *
     * @return mixed|null
     */
    public static function defaultResolveFn($source, $args, $root, $ast)
    {
        $property = NULL;
        $key = $ast->get('name')->get('value');

        if ((is_array($source) || $source instanceof \ArrayAccess) && isset($source[$key])) {
            $property = $source[$key];
        }
        else if (is_object($source) && property_exists($source, $key)) {
            if ($key !== 'ofType') {
                $property = $source->{$key};
            }
        }

        return is_callable($property) ? call_user_func($property, $source) : $property;
    }

    /**
     * This method looks up the field on the given type defintion.
     * It has special casing for the two introspection fields, __schema
     * and __typename. __typename is special because it can always be
     * queried as a field, even in situations where no other fields
     * are allowed, like on a Union. __schema could get automatically
     * added to the query type, but that would require mutating type
     * definitions, which would cause issues.
     *
     * @param Schema $schema
     * @param ObjectType $parent
     * @param Field $ast
     *
     * @return FieldDefinition
     */
    protected static function getFieldDefinition(Schema $schema, ObjectType $parent, Field $ast)
    {
        $name = $ast->get('name')->get('value');
        $schemaMeta = Introspection::schemaMetaFieldDefinition();
        $typeMeta = Introspection::typeMetaFieldDefinition();
        $typeNameMeta = Introspection::typeNameMetaFieldDefinition();

        if ($name === $schemaMeta->getName() && $schema->getQueryType() === $parent) {
            return $schemaMeta;
        } else if ($name === $typeMeta->getName() && $schema->getQueryType() === $parent) {
            return $typeMeta;
        } else if ($name === $typeNameMeta->getName()) {
            return $typeNameMeta;
        }

        $tmp = $parent->getFields();
        return isset($tmp[$name]) ? $tmp[$name] : NULL;
    }
}
