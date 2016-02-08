<?php

namespace Fubhy\GraphQL\Tests\Type;

use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\GraphQL;
use Fubhy\GraphQL\Type\Definition\Types\EnumType;
use Fubhy\GraphQL\Type\Definition\Types\InputObjectType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

class IntrospectionTest extends \PHPUnit_Framework_TestCase
{
    public function testExecutesAnIntrospectionQuery()
    {
        $emptySchema = new Schema(new ObjectType('QueryRoot'));

        $request = '
            query IntrospectionQuery {
                __schema {
                    queryType { name }
                    mutationType { name }
                    types {
                        ...FullType
                    }
                    directives {
                        name
                        args {
                            ...InputValue
                        }
                        onOperation
                        onFragment
                        onField
                    }
                }
            }

            fragment FullType on __Type {
                kind
                name
                fields {
                    name
                    args {
                        ...InputValue
                    }
                    type {
                        ...TypeRef
                    }
                    isDeprecated
                    deprecationReason
                }
                inputFields {
                    ...InputValue
                }
                interfaces {
                    ...TypeRef
                }
                enumValues {
                    name
                    isDeprecated
                    deprecationReason
                }
                possibleTypes {
                    ...TypeRef
                }
            }

            fragment InputValue on __InputValue {
                name
                type { ...TypeRef }
                defaultValue
            }

            fragment TypeRef on __Type {
                kind
                name
                ofType {
                    kind
                    name
                    ofType {
                        kind
                        name
                        ofType {
                            kind
                            name
                        }
                    }
                }
            }
        ';

        $expected = [
            'data' => [
                '__schema' => [
                    'mutationType' => NULL,
                    'queryType' => ['name' => 'QueryRoot'],
                        'types' => [
                            [
                                'kind' => 'OBJECT',
                                'name' => 'QueryRoot',
                                'inputFields' => NULL,
                                'interfaces' => [],
                                'enumValues' => NULL,
                                'possibleTypes' => NULL,
                                'fields' => []
                            ],
                            [
                                'kind' => 'OBJECT',
                                'name' => '__Schema',
                                'fields' => [
                                    [
                                        'name' => 'types',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => '[__Type!]!',
                                            'ofType' => [
                                                'kind' => 'LIST',
                                                'name' => '[__Type!]',
                                                'ofType' => [
                                                    'kind' => 'NON_NULL',
                                                    'name' => '__Type!',
                                                    'ofType' => [
                                                        'kind' => 'OBJECT',
                                                        'name' => '__Type',
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'queryType',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => '__Type!',
                                            'ofType' => [
                                                'kind' => 'OBJECT',
                                                'name' => '__Type',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'mutationType',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'OBJECT',
                                            'name' => '__Type',
                                            'ofType' => NULL,
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'directives',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => '[__Directive!]!',
                                            'ofType' => [
                                                'kind' => 'LIST',
                                                'name' => '[__Directive!]',
                                                'ofType' => [
                                                    'kind' => 'NON_NULL',
                                                    'name' => '__Directive!',
                                                    'ofType' => [
                                                        'kind' => 'OBJECT',
                                                        'name' => '__Directive',
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                ],
                                'inputFields' => NULL,
                                'interfaces' => [],
                                'enumValues' => NULL,
                                'possibleTypes' => NULL,
                            ],
                            [
                                'kind' => 'OBJECT',
                                'name' => '__Type',
                                'fields' => [
                                    [
                                        'name' => 'kind',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => '__TypeKind!',
                                            'ofType' => [
                                                'kind' => 'ENUM',
                                                'name' => '__TypeKind',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'name',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'SCALAR',
                                            'name' => 'String',
                                            'ofType' => NULL,
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'description',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'SCALAR',
                                            'name' => 'String',
                                            'ofType' => NULL,
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'fields',
                                        'args' => [
                                            [
                                                'name' => 'includeDeprecated',
                                                'type' => [
                                                    'kind' => 'SCALAR',
                                                    'name' => 'Boolean',
                                                    'ofType' => NULL,
                                                ],
                                                'defaultValue' => 'false',
                                            ],
                                        ],
                                        'type' => [
                                            'kind' => 'LIST',
                                            'name' => '[__Field!]',
                                            'ofType' => [
                                                'kind' => 'NON_NULL',
                                                'name' => '__Field!',
                                                'ofType' => [
                                                    'kind' => 'OBJECT',
                                                    'name' => '__Field',
                                                    'ofType' => NULL,
                                                ],
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'interfaces',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'LIST',
                                            'name' => '[__Type!]',
                                            'ofType' => [
                                                'kind' => 'NON_NULL',
                                                'name' => '__Type!',
                                                'ofType' => [
                                                    'kind' => 'OBJECT',
                                                    'name' => '__Type',
                                                    'ofType' => NULL,
                                                ],
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'possibleTypes',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'LIST',
                                            'name' => '[__Type!]',
                                            'ofType' => [
                                                'kind' => 'NON_NULL',
                                                'name' => '__Type!',
                                                'ofType' => [
                                                    'kind' => 'OBJECT',
                                                    'name' => '__Type',
                                                    'ofType' => NULL,
                                                ],
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'enumValues',
                                        'args' => [
                                            [
                                                'name' => 'includeDeprecated',
                                                'type' => [
                                                    'kind' => 'SCALAR',
                                                    'name' => 'Boolean',
                                                    'ofType' => NULL,
                                                ],
                                                'defaultValue' => 'false',
                                            ],
                                        ],
                                        'type' => [
                                            'kind' => 'LIST',
                                            'name' => '[__EnumValue!]',
                                            'ofType' => [
                                                'kind' => 'NON_NULL',
                                                'name' => '__EnumValue!',
                                                'ofType' => [
                                                    'kind' => 'OBJECT',
                                                    'name' => '__EnumValue',
                                                    'ofType' => NULL,
                                                ],
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'inputFields',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'LIST',
                                            'name' => '[__InputValue!]',
                                            'ofType' => [
                                                'kind' => 'NON_NULL',
                                                'name' => '__InputValue!',
                                                'ofType' => [
                                                    'kind' => 'OBJECT',
                                                    'name' => '__InputValue',
                                                    'ofType' => NULL,
                                                ],
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'ofType',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'OBJECT',
                                            'name' => '__Type',
                                            'ofType' => NULL,
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                ],
                                'inputFields' => NULL,
                                'interfaces' => [],
                                'enumValues' => NULL,
                                'possibleTypes' => NULL,
                            ],
                            [
                                'kind' => 'ENUM',
                                'name' => '__TypeKind',
                                'fields' => NULL,
                                'inputFields' => NULL,
                                'interfaces' => NULL,
                                'enumValues' => [
                                    [
                                        'name' => 'SCALAR',
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'OBJECT',
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'INTERFACE',
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'UNION',
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'ENUM',
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'INPUT_OBJECT',
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'LIST',
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'NON_NULL',
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                ],
                                'possibleTypes' => NULL,
                            ],
                            [
                                'kind' => 'SCALAR',
                                'name' => 'String',
                                'fields' => NULL,
                                'inputFields' => NULL,
                                'interfaces' => NULL,
                                'enumValues' => NULL,
                                'possibleTypes' => NULL,
                            ],
                            [
                                'kind' => 'SCALAR',
                                'name' => 'Boolean',
                                'fields' => NULL,
                                'inputFields' => NULL,
                                'interfaces' => NULL,
                                'enumValues' => NULL,
                                'possibleTypes' => NULL,
                            ],
                            [
                                'kind' => 'OBJECT',
                                'name' => '__Field',
                                'fields' => [
                                    [
                                        'name' => 'name',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => 'String!',
                                            'ofType' => [
                                                'kind' => 'SCALAR',
                                                'name' => 'String',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'description',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'SCALAR',
                                            'name' => 'String',
                                            'ofType' => NULL,
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'args',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => '[__InputValue!]!',
                                            'ofType' => [
                                                'kind' => 'LIST',
                                                'name' => '[__InputValue!]',
                                                'ofType' => [
                                                    'kind' => 'NON_NULL',
                                                    'name' => '__InputValue!',
                                                    'ofType' => [
                                                        'kind' => 'OBJECT',
                                                        'name' => '__InputValue',
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'type',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => '__Type!',
                                            'ofType' => [
                                                'kind' => 'OBJECT',
                                                'name' => '__Type',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'isDeprecated',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => 'Boolean!',
                                            'ofType' => [
                                                'kind' => 'SCALAR',
                                                'name' => 'Boolean',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'deprecationReason',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'SCALAR',
                                            'name' => 'String',
                                            'ofType' => NULL,
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                ],
                                'inputFields' => NULL,
                                'interfaces' => [],
                                'enumValues' => NULL,
                                'possibleTypes' => NULL,
                            ],
                            [
                                'kind' => 'OBJECT',
                                'name' => '__InputValue',
                                'fields' => [
                                    [
                                        'name' => 'name',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => 'String!',
                                            'ofType' => [
                                                'kind' => 'SCALAR',
                                                'name' => 'String',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'description',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'SCALAR',
                                            'name' => 'String',
                                            'ofType' => NULL,
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'type',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => '__Type!',
                                            'ofType' => [
                                                'kind' => 'OBJECT',
                                                'name' => '__Type',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'defaultValue',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'SCALAR',
                                            'name' => 'String',
                                            'ofType' => NULL,
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                ],
                                'inputFields' => NULL,
                                'interfaces' => [],
                                'enumValues' => NULL,
                                'possibleTypes' => NULL,
                            ],
                            [
                                'kind' => 'OBJECT',
                                'name' => '__EnumValue',
                                'fields' => [
                                    [
                                        'name' => 'name',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => 'String!',
                                            'ofType' => [
                                                'kind' => 'SCALAR',
                                                'name' => 'String',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'description',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'SCALAR',
                                            'name' => 'String',
                                            'ofType' => NULL,
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'isDeprecated',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => 'Boolean!',
                                            'ofType' => [
                                                'kind' => 'SCALAR',
                                                'name' => 'Boolean',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'deprecationReason',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'SCALAR',
                                            'name' => 'String',
                                            'ofType' => NULL,
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                ],
                                'inputFields' => NULL,
                                'interfaces' => [],
                                'enumValues' => NULL,
                                'possibleTypes' => NULL,
                            ],
                            [
                                'kind' => 'OBJECT',
                                'name' => '__Directive',
                                'fields' => [
                                    [
                                        'name' => 'name',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => 'String!',
                                            'ofType' => [
                                                'kind' => 'SCALAR',
                                                'name' => 'String',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'description',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'SCALAR',
                                            'name' => 'String',
                                            'ofType' => NULL,
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'args',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => '[__InputValue!]!',
                                            'ofType' => [
                                                'kind' => 'LIST',
                                                'name' => '[__InputValue!]',
                                                'ofType' => [
                                                    'kind' => 'NON_NULL',
                                                    'name' => '__InputValue!',
                                                    'ofType' => [
                                                        'kind' => 'OBJECT',
                                                        'name' => '__InputValue',
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'onOperation',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => 'Boolean!',
                                            'ofType' => [
                                                'kind' => 'SCALAR',
                                                'name' => 'Boolean',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'onFragment',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => 'Boolean!',
                                            'ofType' => [
                                                'kind' => 'SCALAR',
                                                'name' => 'Boolean',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                    [
                                        'name' => 'onField',
                                        'args' => [],
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => 'Boolean!',
                                            'ofType' => [
                                                'kind' => 'SCALAR',
                                                'name' => 'Boolean',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                        'isDeprecated' => FALSE,
                                        'deprecationReason' => NULL,
                                    ],
                                ],
                                'inputFields' => NULL,
                                'interfaces' => [],
                                'enumValues' => NULL,
                                'possibleTypes' => NULL,
                            ],
                            [
                                'kind' => 'SCALAR',
                                'name' => 'Float',
                                'fields' => NULL,
                                'inputFields' => NULL,
                                'interfaces' => NULL,
                                'enumValues' => NULL,
                                'possibleTypes' => NULL
                            ],
                            [
                                'kind' => 'SCALAR',
                                'name' => 'Id',
                                'fields' => NULL,
                                'inputFields' => NULL,
                                'interfaces' => NULL,
                                'enumValues' => NULL,
                                'possibleTypes' => NULL
                            ],
                            [
                                'kind' => 'SCALAR',
                                'name' => 'Int',
                                'fields' => NULL,
                                'inputFields' => NULL,
                                'interfaces' => NULL,
                                'enumValues' => NULL,
                                'possibleTypes' => NULL
                            ],
                        ],
                        'directives' => [
                            [
                                'name' => 'include',
                                'args' => [
                                    [
                                        'defaultValue' => NULL,
                                        'name' => 'if',
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => 'Boolean!',
                                            'ofType' => [
                                                'kind' => 'SCALAR',
                                                'name' => 'Boolean',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                    ],
                                ],
                                'onOperation' => FALSE,
                                'onFragment' => TRUE,
                                'onField' => TRUE,
                            ],
                            [
                                'name' => 'skip',
                                'args' => [
                                    [
                                        'defaultValue' => NULL,
                                        'name' => 'if',
                                        'type' => [
                                            'kind' => 'NON_NULL',
                                            'name' => 'Boolean!',
                                            'ofType' => [
                                                'kind' => 'SCALAR',
                                                'name' => 'Boolean',
                                                'ofType' => NULL,
                                            ],
                                        ],
                                    ],
                                ],
                                'onOperation' => FALSE,
                                'onFragment' => TRUE,
                                'onField' => TRUE,
                            ],
                        ],
                    ],
                ],
            ];

        $this->assertEquals($expected, GraphQL::execute($emptySchema, $request));
    }

    function testIntrospectsOnInputObject()
    {
        $testInputObject = new InputObjectType('TestInputObject', [
            'a' => ['type' => Type::stringType(), 'defaultValue' => 'foo'],
            'b' => ['type' => New ListModifier(Type::stringType())]
        ]);

        $testType = new ObjectType('TestType', [
            'field' => [
                'type' => Type::stringType(),
                'args' => ['complex' => ['type' => $testInputObject]],
                'resolve' => function ($source, $args) {
                    return json_encode($args['complex']);
                }
            ]
        ]);

        $schema = new Schema($testType);

        $request = '{
            __schema {
                types {
                    kind
                    name
                    inputFields {
                        name
                        type { ...TypeRef }
                        defaultValue
                    }
                }
            }
        }

        fragment TypeRef on __Type {
            kind
            name
            ofType {
                kind
                name
                ofType {
                    kind
                    name
                    ofType {
                        kind
                        name
                    }
                }
            }
        }';

        $expectedFragment = [
            'kind' => 'INPUT_OBJECT',
            'name' => 'TestInputObject',
            'inputFields' => [[
                'name' => 'a',
                'type' => [
                    'kind' => 'SCALAR',
                    'name' => 'String',
                    'ofType' => NULL,
                ],
                'defaultValue' => '"foo"',
            ], [
                'name' => 'b',
                'type' => [
                    'kind' => 'LIST',
                    'name' => '[String]',
                    'ofType' => [
                        'kind' => 'SCALAR',
                        'name' => 'String',
                        'ofType' => NULL,
                    ],
                ],
                'defaultValue' => NULL,
            ]],
        ];

        $result = GraphQL::execute($schema, $request);
        $this->assertEquals($expectedFragment, $result['data']['__schema']['types'][1]);
    }

    public function testSupportsTheTypeRootField()
    {
        $testType = new ObjectType('TestType', [
            'testField' => [
                'type' => Type::stringType(),
            ],
        ]);

        $schema = new Schema($testType);
        $request = '{
            __type(name: "TestType") {
                name
            }
        }';

        $expected = ['data' => [
            '__type' => [
                'name' => 'TestType',
            ],
        ]];

        $this->assertEquals($expected, GraphQL::execute($schema, $request));
    }

    public function testIdentifiesDeprecatedFields()
    {
        $testType = new ObjectType('TestType', [
            'nonDeprecated' => [
                'type' => Type::stringType(),
            ],
            'deprecated' => [
                'type' => Type::stringType(),
                'deprecationReason' => 'Removed in 1.0',
            ],
        ]);

        $schema = new Schema($testType);
        $request = '
            {
                __type(name: "TestType") {
                    name
                    fields(includeDeprecated: true) {
                        name
                        isDeprecated,
                        deprecationReason
                    }
                }
            }
        ';

        $expected = [
            'data' => [
                '__type' => [
                    'name' => 'TestType',
                    'fields' => [[
                        'name' => 'nonDeprecated',
                        'isDeprecated' => FALSE,
                        'deprecationReason' => NULL
                    ], [
                        'name' => 'deprecated',
                        'isDeprecated' => TRUE,
                        'deprecationReason' => 'Removed in 1.0'
                    ]],
                ],
            ],
        ];

        $this->assertEquals($expected, GraphQL::execute($schema, $request));
    }

    public function testRespectsTheIncludeDeprecatedParameterForFields()
    {
        $testType = new ObjectType('TestType', [
            'nonDeprecated' => [
                'type' => Type::stringType(),
            ],
            'deprecated' => [
                'type' => Type::stringType(),
                'deprecationReason' => 'Removed in 1.0'
            ],
        ]);

        $schema = new Schema($testType);
        $request = '
            {
                __type(name: "TestType") {
                    name
                    trueFields: fields(includeDeprecated: true) {
                        name
                    }
                    falseFields: fields(includeDeprecated: false) {
                        name
                    }
                    omittedFields: fields {
                        name
                    }
                }
            }
        ';

        $expected = [
            'data' => [
                '__type' => [
                    'name' => 'TestType',
                    'trueFields' => [[
                        'name' => 'nonDeprecated',
                    ], [
                        'name' => 'deprecated',
                    ]],
                    'falseFields' => [[
                        'name' => 'nonDeprecated',
                    ]],
                    'omittedFields' => [[
                        'name' => 'nonDeprecated',
                    ]],
                ],
            ],
        ];

        $this->assertEquals($expected, GraphQL::execute($schema, $request));
    }

    public function testIdentifiesDeprecatedEnumValues()
    {
        $testEnum = new EnumType('TestEnum', [
            'NONDEPRECATED' => ['value' => 0],
            'DEPRECATED' => ['value' => 1, 'deprecationReason' => 'Removed in 1.0'],
            'ALSONONDEPRECATED' => ['value' => 2],
        ]);

        $testType = new ObjectType('TestType', [
            'testEnum' => [
                'type' => $testEnum,
            ],
        ]);

        $schema = new Schema($testType);
        $request = '{
            __type(name: "TestEnum") {
                name
                enumValues(includeDeprecated: true) {
                    name
                    isDeprecated,
                    deprecationReason
                }
            }
        }';

        $expected = [
            'data' => [
                '__type' => [
                    'name' => 'TestEnum',
                    'enumValues' => [[
                        'name' => 'NONDEPRECATED',
                        'isDeprecated' => FALSE,
                        'deprecationReason' => NULL
                    ], [
                        'name' => 'DEPRECATED',
                        'isDeprecated' => TRUE,
                        'deprecationReason' => 'Removed in 1.0'
                    ], [
                        'name' => 'ALSONONDEPRECATED',
                        'isDeprecated' => FALSE,
                        'deprecationReason' => NULL
                    ]],
                ],
            ],
        ];

        $this->assertEquals($expected, GraphQL::execute($schema, $request));
    }

    public function testRespectsTheIncludeDeprecatedParameterForEnumValues()
    {
        $testEnum = new EnumType('TestEnum', [
            'NONDEPRECATED' => [
                'value' => 0
            ],
            'DEPRECATED' => [
                'value' => 1,
                'deprecationReason' => 'Removed in 1.0'
            ],
            'ALSONONDEPRECATED' => [
                'value' => 2
            ],
        ]);

        $testType = new ObjectType('TestType', [
            'testEnum' => [
                'type' => $testEnum,
            ],
        ]);

        $schema = new Schema($testType);
        $request = '
            {
                __type(name: "TestEnum") {
                    name
                    trueValues: enumValues(includeDeprecated: true) {
                        name
                    }
                    falseValues: enumValues(includeDeprecated: false) {
                        name
                    }
                    omittedValues: enumValues {
                        name
                    }
                }
            }
        ';

        $expected = [
            'data' => [
                '__type' => [
                    'name' => 'TestEnum',
                    'trueValues' => [
                        ['name' => 'NONDEPRECATED'],
                        ['name' => 'DEPRECATED'],
                        ['name' => 'ALSONONDEPRECATED']
                    ],
                    'falseValues' => [
                        ['name' => 'NONDEPRECATED'],
                        ['name' => 'ALSONONDEPRECATED']
                    ],
                    'omittedValues' => [
                        ['name' => 'NONDEPRECATED'],
                        ['name' => 'ALSONONDEPRECATED']
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, GraphQL::execute($schema, $request));
    }

    public function testExposesDescriptionsOnTypesAndFields()
    {
        $schema = new Schema(new ObjectType('QueryRoot'));

        $request = '
            {
                schemaType: __type(name: "__Schema") {
                    name,
                    description,
                    fields {
                        name,
                        description
                    }
                }
            }
        ';

        $expected = [
            'data' => [
                'schemaType' => [
                    'name' => '__Schema',
                    'description' => 'A GraphQL Schema defines the capabilities of a GraphQL server. It exposes all available types and directives on the server, as well as the entry points for query and mutation operations.',
                    'fields' => [[
                        'name' => 'types',
                        'description' => 'A list of all types supported by this server.'
                    ], [
                        'name' => 'queryType',
                        'description' => 'The type that query operations will be rooted at.'
                    ], [
                        'name' => 'mutationType',
                        'description' => 'If this server supports mutation, the type that mutation operations will be rooted at.'
                    ], [
                        'name' => 'directives',
                        'description' => 'A list of all directives supported by this server.'
                    ]],
                ],
            ],
        ];

        $result = GraphQL::execute($schema, $request);
        $this->assertEquals($expected, $result);
    }

    public function testExposesDescriptionsOnEnums()
    {
        $schema = new Schema(new ObjectType('QueryRoot'));

        $request = '
            {
                typeKindType: __type(name: "__TypeKind") {
                    name,
                    description,
                    enumValues {
                        name,
                        description
                    }
                }
            }
        ';

        $expected = [
            'data' => [
                'typeKindType' => [
                    'name' => '__TypeKind',
                    'description' => 'An enum describing what kind of type a given __Type is.',
                    'enumValues' => [[
                        'description' => 'Indicates this type is a scalar.',
                        'name' => 'SCALAR'
                    ], [
                        'description' => 'Indicates this type is an object. `fields` and `interfaces` are valid fields.',
                        'name' => 'OBJECT'
                    ], [
                        'description' => 'Indicates this type is an interface. `fields` and `possibleTypes` are valid fields.',
                        'name' => 'INTERFACE'
                    ], [
                        'description' => 'Indicates this type is a union. `possibleTypes` is a valid field.',
                        'name' => 'UNION'
                    ], [
                        'description' => 'Indicates this type is an enum. `enumValues` is a valid field.',
                        'name' => 'ENUM'
                    ], [
                        'description' => 'Indicates this type is an input object. `inputFields` is a valid field.',
                        'name' => 'INPUT_OBJECT'
                    ], [
                        'description' => 'Indicates this type is a list. `ofType` is a valid field.',
                        'name' => 'LIST'
                    ], [
                        'description' => 'Indicates this type is a non-null. `ofType` is a valid field.',
                        'name' => 'NON_NULL'
                    ]],
                ],
            ],
        ];

        $result = GraphQL::execute($schema, $request);
        $this->assertEquals($expected, $result);
    }
}
