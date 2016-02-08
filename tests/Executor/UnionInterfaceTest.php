<?php

namespace Fubhy\GraphQL\Tests\Executor;

use Fubhy\GraphQL\Executor\Executor;
use Fubhy\GraphQL\Language\Parser;
use Fubhy\GraphQL\Language\Source;
use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\Types\InterfaceType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;
use Fubhy\GraphQL\Type\Definition\Types\UnionType;

class UnionInterfaceTest extends \PHPUnit_Framework_TestCase
{
    protected $schema;
    protected $garfield;
    protected $odie;
    protected $liz;
    protected $john;

    protected function setUp()
    {
        $namedType = new InterfaceType('Named', [
            'name' => ['type' => Type::stringType()],
        ]);

        $dogType = new ObjectType('Dog', [
            'name' => ['type' => Type::stringType()],
            'barks' => ['type' => Type::booleanType()],
        ], [$namedType], function ($value) {
            return $value instanceof Dog;
        });

        $catType = new ObjectType('Cat', [
            'name' => ['type' => Type::stringType()],
            'meows' => ['type' => Type::booleanType()],
        ], [$namedType], function ($value) {
            return $value instanceof Cat;
        });

        $petType = new UnionType('Pet', [$dogType, $catType], function ($value) use ($dogType, $catType) {
            if ($value instanceof Dog) {
                return $dogType;
            }

            if ($value instanceof Cat) {
                return $catType;
            }

            return NULL;
        });

        $personType = new ObjectType('Person', [
            'name' => ['type' => Type::stringType()],
            'pets' => ['type' => new ListModifier($petType)],
            'friends' => ['type' => new ListModifier($namedType)],
        ], [$namedType], function ($value) {
            return $value instanceof Person;
        });

        $this->schema = new Schema($personType);
        $this->garfield = new Cat('Garfield', FALSE);
        $this->odie = new Dog('Odie', TRUE);
        $this->liz = new Person('Liz');
        $this->john = new Person('John', [$this->garfield, $this->odie], [$this->liz, $this->odie]);

    }

    public function testCanIntrospectOnUnionAndIntersectionTypes()
    {
        $parser = new Parser();
        $ast = $parser->parse(new Source('
            {
                Named: __type(name: "Named") {
                    kind
                    name
                    fields { name }
                    interfaces { name }
                    possibleTypes { name }
                    enumValues { name }
                    inputFields { name }
                }
                Pet: __type(name: "Pet") {
                    kind
                    name
                    fields { name }
                    interfaces { name }
                    possibleTypes { name }
                    enumValues { name }
                    inputFields { name }
                }
            }
        '));

        $expected = [
            'data' => [
                'Named' => [
                    'kind' => 'INTERFACE',
                    'name' => 'Named',
                    'fields' => [
                        ['name' => 'name'],
                    ],
                    'interfaces' => NULL,
                    'possibleTypes' => [
                        ['name' => 'Dog'],
                        ['name' => 'Cat'],
                        ['name' => 'Person'],
                    ],
                    'enumValues' => NULL,
                    'inputFields' => NULL,
                ],
                'Pet' => [
                    'kind' => 'UNION',
                    'name' => 'Pet',
                    'fields' => NULL,
                    'interfaces' => NULL,
                    'possibleTypes' => [
                        ['name' => 'Dog'],
                        ['name' => 'Cat'],
                    ],
                    'enumValues' => NULL,
                    'inputFields' => NULL,
                ],
            ],
        ];

        $this->assertEquals($expected, Executor::execute($this->schema, NULL, $ast));
    }

    public function testExecutesUsingUnionTypes()
    {
        $parser = new Parser();
        $ast = $parser->parse(new Source('
            {
                __typename
                name
                pets {
                    __typename
                    name
                    barks
                    meows
                }
            }
        '));

        $expected = [
            'data' => [
                '__typename' => 'Person',
                'name' => 'John',
                'pets' => [
                    ['__typename' => 'Cat', 'name' => 'Garfield', 'meows' => FALSE],
                    ['__typename' => 'Dog', 'name' => 'Odie', 'barks' => TRUE],
                ],
            ],
        ];

        $this->assertEquals($expected, Executor::execute($this->schema, $this->john, $ast));
    }

    public function testExecutesUnionTypesWithInlineFragments()
    {
        $parser = new Parser();
        $ast = $parser->parse(new Source('
            {
                __typename
                name
                pets {
                    __typename
                    ... on Dog {
                        name
                        barks
                    }
                    ... on Cat {
                        name
                        meows
                    }
                }
            }
        '));

        $expected = [
            'data' => [
                '__typename' => 'Person',
                'name' => 'John',
                'pets' => [
                    ['__typename' => 'Cat', 'name' => 'Garfield', 'meows' => FALSE],
                    ['__typename' => 'Dog', 'name' => 'Odie', 'barks' => TRUE],
                ],
            ],
        ];

        $this->assertEquals($expected, Executor::execute($this->schema, $this->john, $ast));
    }

    public function testExecutesUsingInterfaceTypes()
    {
        $parser = new Parser();
        $ast = $parser->parse(new Source('
            {
                __typename
                name
                friends {
                    __typename
                    name
                    barks
                    meows
                }
            }
        '));

        $expected = [
            'data' => [
                '__typename' => 'Person',
                'name' => 'John',
                'friends' => [
                    ['__typename' => 'Person', 'name' => 'Liz'],
                    ['__typename' => 'Dog', 'name' => 'Odie', 'barks' => TRUE]
                ],
            ],
        ];

        $this->assertEquals($expected, Executor::execute($this->schema, $this->john, $ast));
    }

    public function testExecutesInterfaceTypesWithInlineFragments()
    {
        $parser = new Parser();
        $ast = $parser->parse(new Source('
            {
                __typename
                name
                friends {
                    __typename
                    name
                    ... on Dog {
                        barks
                    }
                    ... on Cat {
                        meows
                    }
                }
            }
        '));

        $expected = [
            'data' => [
                '__typename' => 'Person',
                'name' => 'John',
                'friends' => [
                    ['__typename' => 'Person', 'name' => 'Liz'],
                    ['__typename' => 'Dog', 'name' => 'Odie', 'barks' => TRUE],
                ],
            ],
        ];

        $this->assertEquals($expected, Executor::execute($this->schema, $this->john, $ast));
    }

    public function testAllowsFragmentConditionsToBeAbstractTypes()
    {
        $parser = new Parser();
        $ast = $parser->parse(new Source('
            {
                __typename
                name
                pets { ...PetFields }
                friends { ...FriendFields }
            }

            fragment PetFields on Pet {
                __typename
                ... on Dog {
                    name
                    barks
                }
                ... on Cat {
                    name
                    meows
                }
            }

            fragment FriendFields on Named {
                __typename
                name
                ... on Dog {
                    barks
                }
                ... on Cat {
                    meows
                }
            }
        '));

        $expected = [
            'data' => [
                '__typename' => 'Person',
                'name' => 'John',
                'pets' => [
                    ['__typename' => 'Cat', 'name' => 'Garfield', 'meows' => FALSE],
                    ['__typename' => 'Dog', 'name' => 'Odie', 'barks' => TRUE],
                ],
                'friends' => [
                    ['__typename' => 'Person', 'name' => 'Liz'],
                    ['__typename' => 'Dog', 'name' => 'Odie', 'barks' => TRUE],
                ],
            ],
        ];

        $this->assertEquals($expected, Executor::execute($this->schema, $this->john, $ast));
    }
}

class Dog
{
    public $name;
    public $barks;

    function __construct($name, $barks)
    {
        $this->name = $name;
        $this->barks = $barks;
    }
}

class Cat
{
    public $name;
    public $meows;

    function __construct($name, $meows)
    {
        $this->name = $name;
        $this->meows = $meows;
    }
}

class Person
{
    public $name;
    public $pets;
    public $friends;

    function __construct($name, $pets = NULL, $friends = NULL)
    {
        $this->name = $name;
        $this->pets = $pets;
        $this->friends = $friends;
    }
}
