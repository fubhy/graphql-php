<?php

namespace Fubhy\GraphQL\Tests\Tests;

use Fubhy\GraphQL\GraphQL;
use Fubhy\GraphQL\Tests\StarWarsSchema;

class StarWarsIntrospectionTest extends \PHPUnit_Framework_TestCase
{
    use StarWarsSchema;

    /**
     * Helper function to test a query and the expected response.
     *
     * @param $query
     * @param $expected
     */
    protected function assertQuery($query, $expected)
    {
        $this->assertEquals(['data' => $expected], GraphQL::execute($this->getStarWarsSchema(), $query));
    }

    public function testAllowsQueryingTheSchemaForTypes()
    {
        $query = '
            query IntrospectionTypeQuery {
                __schema {
                    types {
                        name
                    }
                }
            }
        ';

        $expected = [
            '__schema' => [
                'types' => [
                    ['name' => 'Query'],
                    ['name' => 'Episode'],
                    ['name' => 'Character'],
                    ['name' => 'Human'],
                    ['name' => 'String'],
                    ['name' => 'Droid'],
                    ['name' => '__Schema'],
                    ['name' => '__Type'],
                    ['name' => '__TypeKind'],
                    ['name' => 'Boolean'],
                    ['name' => '__Field'],
                    ['name' => '__InputValue'],
                    ['name' => '__EnumValue'],
                    ['name' => '__Directive'],
                    ['name' => 'Float'],
                    ['name' => 'Id'],
                    ['name' => 'Int'],
                ],
            ],
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsQueryingTheSchemaForQueryType()
    {
        $query = '
            query IntrospectionQueryTypeQuery {
                __schema {
                    queryType {
                        name
                    }
                }
            }
        ';

        $expected = [
            '__schema' => [
                'queryType' => [
                    'name' => 'Query'
                ],
            ],
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsQueryingTheSchemaForASpecificType()
    {
        $query = '
            query IntrospectionDroidTypeQuery {
                __type(name: "Droid") {
                    name
                }
            }
        ';

        $expected = [
            '__type' => [
                'name' => 'Droid'
            ],
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsQueryingTheSchemaForAnObjectKind()
    {
        $query = '
            query IntrospectionDroidKindQuery {
                __type(name: "Droid") {
                    name
                    kind
                }
            }
        ';

        $expected = [
            '__type' => [
                'name' => 'Droid',
                'kind' => 'OBJECT'
            ],
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsQueryingTheSchemaForAnInterfaceKind()
    {
        $query = '
            query IntrospectionCharacterKindQuery {
                __type(name: "Character") {
                    name
                    kind
                }
            }
        ';

        $expected = [
            '__type' => [
                'name' => 'Character',
                'kind' => 'INTERFACE'
            ]
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsQueryingTheSchemaForObjectFields()
    {
        $query = '
            query IntrospectionDroidFieldsQuery {
                __type(name: "Droid") {
                    name
                    fields {
                        name
                        type {
                            name
                            kind
                        }
                    }
                }
            }
        ';

        $expected = [
            '__type' => [
                'name' => 'Droid',
                'fields' => [
                    [
                        'name' => 'id',
                        'type' => [
                            'name' => 'String!',
                            'kind' => 'NON_NULL'
                        ],
                    ],
                    [
                        'name' => 'name',
                        'type' => [
                            'name' => 'String',
                            'kind' => 'SCALAR'
                        ],
                    ],
                    [
                        'name' => 'friends',
                        'type' => [
                            'name' => '[Character]',
                            'kind' => 'LIST'
                        ],
                    ],
                    [
                        'name' => 'appearsIn',
                        'type' => [
                            'name' => '[Episode]',
                            'kind' => 'LIST'
                        ],
                    ],
                    [
                        'name' => 'primaryFunction',
                        'type' => [
                            'name' => 'String',
                            'kind' => 'SCALAR'
                        ],
                    ],
                ],
            ],
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsQueryingTheSchemaForNestedObjectFields()
    {
        $query = '
            query IntrospectionDroidNestedFieldsQuery {
                __type(name: "Droid") {
                    name
                    fields {
                        name
                        type {
                            name
                            kind
                            ofType {
                                name
                                kind
                            }
                        }
                    }
                }
            }
        ';

        $expected = [
            '__type' => [
                'name' => 'Droid',
                'fields' => [
                    [
                        'name' => 'id',
                        'type' => [
                            'name' => 'String!',
                            'kind' => 'NON_NULL',
                            'ofType' => [
                                'name' => 'String',
                                'kind' => 'SCALAR'
                            ]
                        ]
                    ],
                    [
                        'name' => 'name',
                        'type' => [
                            'name' => 'String',
                            'kind' => 'SCALAR',
                            'ofType' => NULL
                        ]
                    ],
                    [
                        'name' => 'friends',
                        'type' => [
                            'name' => '[Character]',
                            'kind' => 'LIST',
                            'ofType' => [
                                'name' => 'Character',
                                'kind' => 'INTERFACE'
                            ]
                        ]
                    ],
                    [
                        'name' => 'appearsIn',
                        'type' => [
                            'name' => '[Episode]',
                            'kind' => 'LIST',
                            'ofType' => [
                                'name' => 'Episode',
                                'kind' => 'ENUM'
                            ]
                        ]
                    ],
                    [
                        'name' => 'primaryFunction',
                        'type' => [
                            'name' => 'String',
                            'kind' => 'SCALAR',
                            'ofType' => NULL
                        ]
                    ]
                ]
            ]
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsQueryingTheSchemaForFieldArgs()
    {
        $query = '
            query IntrospectionQueryTypeQuery {
                __schema {
                    queryType {
                        fields {
                            name
                            args {
                                name
                                description
                                type {
                                    name
                                    kind
                                    ofType {
                                        name
                                        kind
                                    }
                                }
                                defaultValue
                            }
                        }
                    }
                }
            }
        ';

        $expected = [
            '__schema' => [
                'queryType' => [
                    'fields' => [
                        [
                            'name' => 'hero',
                            'args' => [
                                [
                                    'name' => 'episode',
                                    'description' => 'If omitted, returns the hero of the whole saga. If provided, returns the hero of that particular episode.',
                                    'type' => [
                                        'name' => 'Episode',
                                        'kind' => 'ENUM',
                                        'ofType' => NULL,
                                    ],
                                    'defaultValue' => NULL,
                                ],
                            ],
                        ],
                        [
                            'name' => 'human',
                            'args' => [
                                [
                                    'name' => 'id',
                                    'description' => 'The id of the human.',
                                    'type' => [
                                        'name' => 'String!',
                                        'kind' => 'NON_NULL',
                                        'ofType' => [
                                            'name' => 'String',
                                            'kind' => 'SCALAR',
                                        ],
                                    ],
                                    'defaultValue' => NULL,
                                ],
                            ],
                        ],
                        [
                            'name' => 'droid',
                            'args' => [
                                [
                                    'name' => 'id',
                                    'description' => 'The id of the droid.',
                                    'type' => [
                                        'name' => 'String!',
                                        'kind' => 'NON_NULL',
                                        'ofType' => [
                                            'name' => 'String',
                                            'kind' => 'SCALAR',
                                        ],
                                    ],
                                    'defaultValue' => NULL,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsQueryingTheSchemaForDocumentation()
    {
        $query = '
            query IntrospectionDroidDescriptionQuery {
                __type(name: "Droid") {
                    name
                    description
                }
            }
        ';

        $expected = [
            '__type' => [
                'name' => 'Droid',
                'description' => 'A mechanical creature in the Star Wars universe.'
            ]
        ];

        $this->assertQuery($query, $expected);
    }
}
