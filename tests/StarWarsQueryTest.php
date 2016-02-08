<?php

namespace Fubhy\GraphQL\Tests\Tests;

use Fubhy\GraphQL\GraphQL;
use Fubhy\GraphQL\Tests\StarWarsSchema;

class StarWarsQueryTest extends \PHPUnit_Framework_TestCase
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
        $result = GraphQL::execute($this->getStarWarsSchema(), $query);
        $this->assertEquals(['data' => $expected], $result);
    }

    /**
     * Helper function to test a query with params and the expected response.
     *
     * @param $query
     * @param $params
     * @param $expected
     */
    protected function assertQueryWithParams($query, $params, $expected)
    {
        $result = GraphQL::execute($this->getStarWarsSchema(), $query, NULL, $params);
        $this->assertEquals(['data' => $expected], $result);
    }

    public function testCorrectlyIdentifiesR2D2AsTheHeroOfTheStarWarsSaga()
    {
        $query = '
            query HeroNameQuery {
                hero {
                    name
                }
            }
        ';

        $expected = [
            'hero' => [
                'name' => 'R2-D2'
            ]
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsUsToQueryForTheIDAndFriendsOfR2D2()
    {
        $query = '
            query HeroNameAndFriendsQuery {
                hero {
                    id
                    name
                    friends {
                        name
                    }
                }
            }
        ';

        $expected = [
            'hero' => [
                'id' => '2001',
                'name' => 'R2-D2',
                'friends' => [
                    ['name' => 'Luke Skywalker'],
                    ['name' => 'Han Solo'],
                    ['name' => 'Leia Organa'],
                ],
            ],
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsUsToQueryForTheFriendsOfFriendsOfR2D2()
    {
        $query = '
            query NestedQuery {
                hero {
                    name
                    friends {
                        name
                        appearsIn
                        friends {
                            name
                        }
                    }
                }
            }
        ';

        $expected = [
            'hero' => [
                'name' => 'R2-D2',
                'friends' => [
                    [
                        'name' => 'Luke Skywalker',
                        'appearsIn' => ['NEWHOPE', 'EMPIRE', 'JEDI'],
                        'friends' => [
                            ['name' => 'Han Solo'],
                            ['name' => 'Leia Organa'],
                            ['name' => 'C-3PO'],
                            ['name' => 'R2-D2'],
                        ],
                    ],
                    [
                        'name' => 'Han Solo',
                        'appearsIn' => ['NEWHOPE', 'EMPIRE', 'JEDI'],
                        'friends' => [
                            ['name' => 'Luke Skywalker'],
                            ['name' => 'Leia Organa'],
                            ['name' => 'R2-D2'],
                        ],
                    ],
                    [
                        'name' => 'Leia Organa',
                        'appearsIn' => ['NEWHOPE', 'EMPIRE', 'JEDI'],
                        'friends' => [
                            ['name' => 'Luke Skywalker'],
                            ['name' => 'Han Solo'],
                            ['name' => 'C-3PO'],
                            ['name' => 'R2-D2'],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsUsToQueryForLukeSkywalkerDirectlyUsingHisId()
    {
        $query = '
            query FetchLukeQuery {
                human(id: "1000") {
                    name
                }
            }
        ';

        $expected = [
            'human' => [
                'name' => 'Luke Skywalker',
            ]
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsUsToCreateAGenericQueryThenUseItToFetchLukeSkywalkerUsingHisId()
    {
        $query = '
            query FetchSomeIDQuery($someId: String!) {
                human(id: $someId) {
                    name
                }
            }
        ';

        $params = [
            'someId' => '1000',
        ];

        $expected = [
            'human' => [
                'name' => 'Luke Skywalker',
            ],
        ];

        $this->assertQueryWithParams($query, $params, $expected);
    }

    public function testAllowsUsToCreateAGenericQueryThenUseItToFetchHanSoloUsingHisId()
    {
        $query = '
            query FetchSomeIDQuery($someId: String!) {
                human(id: $someId) {
                    name
                }
            }
        ';

        $params = [
            'someId' => '1002',
        ];

        $expected = [
            'human' => [
                'name' => 'Han Solo',
            ],
        ];

        $this->assertQueryWithParams($query, $params, $expected);
    }

    public function testAllowsUsToCreateAGenericQueryThenPassAnInvalidIdToGetNullBack()
    {
        $query = '
            query humanQuery($id: String!) {
                human(id: $id) {
                    name
                }
            }
        ';

        $params = [
            'id' => 'not a valid id',
        ];

        $expected = [
            'human' => NULL
        ];

        $this->assertQueryWithParams($query, $params, $expected);
    }

    public function testAllowsUsToQueryForLukeChangingHisKeyWithAnAlias()
    {
        $query = '
            query FetchLukeAliased {
                luke: human(id: "1000") {
                    name
                }
            }
        ';

        $expected = [
            'luke' => [
                'name' => 'Luke Skywalker',
            ],
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsUsToQueryForBothLukeAndLeiaUsingTwoRootFieldsAndAnAlias()
    {
        $query = '
            query FetchLukeAndLeiaAliased {
                luke: human(id: "1000") {
                    name
                }
                leia: human(id: "1003") {
                    name
                }
            }
        ';

        $expected = [
            'luke' => [
                'name' => 'Luke Skywalker',
            ],
            'leia' => [
                'name' => 'Leia Organa',
            ],
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsUsToQueryUsingDuplicatedContent()
    {
        $query = '
            query DuplicateFields {
                luke: human(id: "1000") {
                    name
                    homePlanet
                }
                leia: human(id: "1003") {
                    name
                    homePlanet
                }
            }
        ';

        $expected = [
            'luke' => [
                'name' => 'Luke Skywalker',
                'homePlanet' => 'Tatooine',
            ],
            'leia' => [
                'name' => 'Leia Organa',
                'homePlanet' => 'Alderaan',
            ],
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsUsToUseAFragmentToAvoidDuplicatingContent()
    {
        $query = '
            query UseFragment {
                luke: human(id: "1000") {
                    ...HumanFragment
                }
                leia: human(id: "1003") {
                    ...HumanFragment
                }
            }

            fragment HumanFragment on Human {
                name
                homePlanet
            }
        ';

        $expected = [
            'luke' => [
                'name' => 'Luke Skywalker',
                'homePlanet' => 'Tatooine',
            ],
            'leia' => [
                'name' => 'Leia Organa',
                'homePlanet' => 'Alderaan',
            ],
        ];

        $this->assertQuery($query, $expected);
    }

    public function testAllowsUsToVerifyThatR2D2IsADroid()
    {
        $query = '
            query CheckTypeOfR2 {
                hero {
                    __typename
                    name
                }
            }
        ';

        $expected = [
            'hero' => [
                '__typename' => 'Droid',
                'name' => 'R2-D2'
            ],
        ];

        $this->assertQuery($query, $expected);
    }
}
