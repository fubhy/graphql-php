<?php

namespace Fubhy\GraphQL\Tests\Executor;

use Fubhy\GraphQL\Executor\Executor;
use Fubhy\GraphQL\Language\Parser;
use Fubhy\GraphQL\Language\Source;
use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

class ExecutorSchemaTest extends \PHPUnit_Framework_TestCase
{
    public function testExecutesUsingASchema()
    {
        $blogArticle = NULL;
        $blogImage = new ObjectType('Image', [
            'url' => ['type' => Type::stringType()],
            'width' => ['type' => Type::intType()],
            'height' => ['type' => Type::intType()],
        ]);

        $blogAuthor = new ObjectType('Author', [
            'id' => ['type' => Type::stringType()],
            'name' => ['type' => Type::stringType()],
            'pic' => [
                'args' => ['width' => ['type' => Type::intType()], 'height' => ['type' => Type::intType()]],
                'type' => $blogImage,
                'resolve' => function ($obj, $args) {
                    return $obj['pic']($args['width'], $args['height']);
                }
            ],
            'recentArticle' => [
                'type' => function () use (&$blogArticle) {
                    return $blogArticle;
                }
            ],
        ]);

        $blogArticle = new ObjectType('Article', [
            'id' => ['type' => new NonNullModifier(Type::stringType())],
            'isPublished' => ['type' => Type::booleanType()],
            'author' => ['type' => $blogAuthor],
            'title' => ['type' => Type::stringType()],
            'body' => ['type' => Type::stringType()],
            'keywords' => ['type' => new ListModifier(Type::stringType())]
        ]);

        $blogQuery = new ObjectType('Query', [
            'article' => [
                'type' => $blogArticle,
                'args' => ['id' => ['type' => Type::idType()]],
                'resolve' => function ($_, $args) {
                    return $this->article($args['id']);
                }
            ],
            'feed' => [
                'type' => new ListModifier($blogArticle),
                'resolve' => function () {
                    return [
                        $this->article(1),
                        $this->article(2),
                        $this->article(3),
                        $this->article(4),
                        $this->article(5),
                        $this->article(6),
                        $this->article(7),
                        $this->article(8),
                        $this->article(9),
                        $this->article(10),
                    ];
                }
            ],
        ]);

        $blogSchema = new Schema($blogQuery);

        $request = '
            {
                feed {
                    id,
                    title
                },
                article(id: "1") {
                    ...articleFields,
                    author {
                        id,
                        name,
                        pic(width: 640, height: 480) {
                            url,
                            width,
                            height
                        },
                        recentArticle {
                            ...articleFields,
                            keywords
                        }
                    }
                }
            }

            fragment articleFields on Article {
                id,
                isPublished,
                title,
                body,
                hidden,
                notdefined
            }
        ';

        $expected = [
            'data' => [
                'feed' => [
                    ['id' => '1', 'title' => 'My Article 1'],
                    ['id' => '2', 'title' => 'My Article 2'],
                    ['id' => '3', 'title' => 'My Article 3'],
                    ['id' => '4', 'title' => 'My Article 4'],
                    ['id' => '5', 'title' => 'My Article 5'],
                    ['id' => '6', 'title' => 'My Article 6'],
                    ['id' => '7', 'title' => 'My Article 7'],
                    ['id' => '8', 'title' => 'My Article 8'],
                    ['id' => '9', 'title' => 'My Article 9'],
                    ['id' => '10', 'title' => 'My Article 10'],
                ],
                'article' => [
                    'id' => '1',
                    'isPublished' => TRUE,
                    'title' => 'My Article 1',
                    'body' => 'This is a post',
                    'author' => [
                        'id' => '123',
                        'name' => 'John Smith',
                        'pic' => [
                            'url' => 'cdn://123',
                            'width' => 640,
                            'height' => 480,
                        ],
                        'recentArticle' => [
                            'id' => '1',
                            'isPublished' => TRUE,
                            'title' => 'My Article 1',
                            'body' => 'This is a post',
                            'keywords' => ['foo', 'bar', '1', 'true', NULL],
                        ],
                    ],
                ],
            ],
        ];

        $parser = new Parser();
        $this->assertEquals($expected, Executor::execute($blogSchema, NULL, $parser->parse(new Source($request)), '', []));
    }

    protected function article($id)
    {
        $johnSmith = NULL;
        $article = function ($id) use (&$johnSmith) {
            return [
                'id' => $id,
                'isPublished' => 'true',
                'author' => $johnSmith,
                'title' => 'My Article ' . $id,
                'body' => 'This is a post',
                'hidden' => 'This data is not exposed in the schema',
                'keywords' => ['foo', 'bar', 1, TRUE, NULL],
            ];
        };

        $johnSmith = [
            'id' => 123,
            'name' => 'John Smith',
            'pic' => function ($width, $height) {
                return [
                    'url' => "cdn://123",
                    'width' => $width,
                    'height' => $height
                ];
            },
            'recentArticle' => $article(1),
        ];

        return $article($id);
    }
}
