<?php

namespace Fubhy\GraphQL\Tests;

/**
 * This is designed to be an end-to-end test, demonstrating
 * the full GraphQL stack.
 *
 * We will create a GraphQL schema that describes the major
 * characters in the original Star Wars trilogy.
 *
 * NOTE: This may contain spoilers for the original Star
 * Wars trilogy.
 */
use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\Types\EnumType;
use Fubhy\GraphQL\Type\Definition\Types\InterfaceType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Using our shorthand to describe type systems, the type system for our
 * Star Wars example is:
 *
 * enum Episode { NEWHOPE, EMPIRE, JEDI }
 *
 * interface Character {
 *   id: String!
 *   name: String
 *   friends: [Character]
 *   appearsIn: [Episode]
 * }
 *
 * type Human : Character {
 *   id: String!
 *   name: String
 *   friends: [Character]
 *   appearsIn: [Episode]
 *   homePlanet: String
 * }
 *
 * type Droid : Character {
 *   id: String!
 *   name: String
 *   friends: [Character]
 *   appearsIn: [Episode]
 *   primaryFunction: String
 * }
 *
 * type Query {
 *   hero: Character
 *   human(id: String!): Human
 *   droid(id: String!): Droid
 * }
 *
 * We begin by setting up our schema.
 */

trait StarWarsSchema
{
    use StarWarsData;

    protected function getStarWarsSchema()
    {
        /**
         * The original trilogy consists of three movies.
         *
         * This implements the following type system shorthand:
         *   enum Episode { NEWHOPE, EMPIRE, JEDI }
         */
        $episodeEnum = new EnumType('Episode', [
            'NEWHOPE' => [
                'value' => 4,
                'description' => 'Released in 1977.',
            ],
            'EMPIRE' => [
                'value' => 5,
                'description' => 'Released in 1980.',
            ],
            'JEDI' => [
                'value' => 6,
                'description' => 'Released in 1983.',
            ],
        ], 'One of the films in the Star Wars Trilogy');

        $humanType = NULL;
        $droidType = NULL;

        /**
         * Characters in the Star Wars trilogy are either humans or droids.
         *
         * This implements the following type system shorthand:
         *   interface Character {
         *     id: String!
         *     name: String
         *     friends: [Character]
         *     appearsIn: [Episode]
         *   }
         */
        $characterInterface = new InterfaceType('Character', [
            'id' => [
                'type' => new NonNullModifier(Type::stringType()),
                'description' => 'The id of the character.',
            ],
            'name' => [
                'type' => Type::stringType(),
                'description' => 'The name of the character.',
            ],
            'friends' => [
                'type' => function () use (&$characterInterface) {
                    return new ListModifier($characterInterface);
                },
                'description' => 'The friends of the character, or an empty list if they have none.',
            ],
            'appearsIn' => [
                'type' => new ListModifier($episodeEnum),
                'description' => 'Which movies they appear in.',
            ],
        ], function ($obj) use (&$humanType, &$droidType) {
            $humans = $this->getHumans();
            if (isset($humans[$obj['id']])) {
                return $humanType;
            }

            $droids = $this->getDroids();
            if (isset($droids[$obj['id']])) {
                return $droidType;
            }

            return NULL;
        }, 'A character in the Star Wars Trilogy');

        /**
         * We define our human type, which implements the character interface.
         *
         * This implements the following type system shorthand:
         *   type Human : Character {
         *     id: String!
         *     name: String
         *     friends: [Character]
         *     appearsIn: [Episode]
         *   }
         */
        $humanType = new ObjectType('Human', [
            'id' => [
                'type' => new NonNullModifier(Type::stringType()),
                'description' => 'The id of the human.',
            ],
            'name' => [
                'type' => Type::stringType(),
                'description' => 'The name of the human.',
            ],
            'friends' => [
                'type' => new ListModifier($characterInterface),
                'description' => 'The friends of the human, or an empty list if they have none.',
                'resolve' => function ($human) {
                    return $this->getStarWarsFriends($human);
                },
            ],
            'appearsIn' => [
                'type' => new ListModifier($episodeEnum),
                'description' => 'Which movies they appear in.',
            ],
            'homePlanet' => [
                'type' => Type::stringType(),
                'description' => 'The home planet of the human, or null if unknown.',
            ],
        ], [$characterInterface], NULL, 'A humanoid creature in the Star Wars universe.');

        /**
         * The other type of character in Star Wars is a droid.
         *
         * This implements the following type system shorthand:
         *   type Droid : Character {
         *     id: String!
         *     name: String
         *     friends: [Character]
         *     appearsIn: [Episode]
         *     primaryFunction: String
         *   }
         */
        $droidType = new ObjectType('Droid', [
            'id' => [
                'type' => new NonNullModifier(Type::stringType()),
                'description' => 'The id of the droid.',
            ],
            'name' => [
                'type' => Type::stringType(),
                'description' => 'The name of the droid.',
            ],
            'friends' => [
                'type' => new ListModifier($characterInterface),
                'description' => 'The friends of the droid, or an empty list if they have none.',
                'resolve' => function ($droid) {
                    return $this->getStarWarsFriends($droid);
                },
            ],
            'appearsIn' => [
                'type' => new ListModifier($episodeEnum),
                'description' => 'Which movies they appear in.',
            ],
            'primaryFunction' => [
                'type' => Type::stringType(),
                'description' => 'The primary function of the droid.',
            ]
        ], [$characterInterface], NULL, 'A mechanical creature in the Star Wars universe.');

        /**
         * This is the type that will be the root of our query, and the
         * entry point into our schema. It gives us the ability to fetch
         * objects by their IDs, as well as to fetch the undisputed hero
         * of the Star Wars trilogy, R2-D2, directly.
         *
         * This implements the following type system shorthand:
         *   type Query {
         *     hero: Character
         *     human(id: String!): Human
         *     droid(id: String!): Droid
         *   }
         *
         */
        $queryType = new ObjectType('Query', [
            'hero' => [
                'type' => $characterInterface,
                'args' => [
                    'episode' => [
                        'description' => 'If omitted, returns the hero of the whole saga. If provided, returns the hero of that particular episode.',
                        'type' => $episodeEnum
                    ],
                ],
                'resolve' => function () {
                    return $this->getArtoo();
                },
            ],
            'human' => [
                'type' => $humanType,
                'args' => [
                    'id' => [
                        'name' => 'id',
                        'description' => 'The id of the human.',
                        'type' => new NonNullModifier(Type::stringType()),
                    ],
                ],
                'resolve' => function ($root, array $args) {
                    $humans = $this->getHumans();
                    return isset($humans[$args['id']]) ? $humans[$args['id']] : NULL;
                }
            ],
            'droid' => [
                'type' => $droidType,
                'args' => [
                    'id' => [
                        'name' => 'id',
                        'description' => 'The id of the droid.',
                        'type' => new NonNullModifier(Type::stringType()),
                    ],
                ],
                'resolve' => function ($root, array $args) {
                    $droids = $this->getDroids();
                    return isset($droids[$args['id']]) ? $droids[$args['id']] : NULL;
                }
            ]
        ]);

        return new Schema($queryType);
    }
}
