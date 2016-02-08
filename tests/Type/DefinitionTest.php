<?php

namespace Fubhy\GraphQL\Tests\Type;

use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\Types\EnumType;
use Fubhy\GraphQL\Type\Definition\Types\InputObjectType;
use Fubhy\GraphQL\Type\Definition\Types\InterfaceType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;
use Fubhy\GraphQL\Type\Definition\Types\TypeInterface;
use Fubhy\GraphQL\Type\Definition\Types\UnionType;

class DefinitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    protected $blogImage;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    protected $blogAuthor;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    protected $blogArticle;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    protected $blogQuery;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    protected $blogMutation;

    public function setUp()
    {
        $this->blogImage = new ObjectType('Image', [
            'url' => ['type' => Type::stringType()],
            'width' => ['type' => Type::intType()],
            'height' => ['type' => Type::intType()],
        ]);

        $this->blogAuthor = new ObjectType('Author', [
            'id' => ['type' => Type::stringType()],
            'name' => ['type' => Type::stringType()],
            'pic' => [
                'args' => [
                    'width' => ['type' => Type::intType()],
                    'height' => ['type' => Type::intType()],
                ],
                'type' => $this->blogImage,
            ],
            'recentArticle' => ['type' => function () {
                return $this->blogArticle;
            }],
        ]);

        $this->blogArticle = new ObjectType('Article', [
            'id' => ['type' => Type::stringType()],
            'isPublished' => ['type' => Type::booleanType()],
            'author' => ['type' => $this->blogAuthor],
            'title' => ['type' => Type::stringType()],
            'body' => ['type' => Type::stringType()],
        ]);

        $this->blogQuery = new ObjectType('Query', [
            'article' => [
                'args' => [
                    'id' => ['type' => Type::stringType()],
                ],
                'type' => $this->blogArticle,
            ],
            'feed' => ['type' => new ListModifier($this->blogArticle)],
        ]);

        $this->blogMutation = new ObjectType('Mutation',  [
            'writeArticle' => ['type' => $this->blogArticle],
        ]);
    }

    public function testDefinesAQueryOnlySchema()
    {
        $blogSchema = new Schema($this->blogQuery);
        $this->assertSame($this->blogQuery, $blogSchema->getQueryType());

        /** @var \Fubhy\GraphQL\Type\Definition\FieldDefinition $articleField */
        /** @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType $articleFieldType */
        $articleField = $this->blogQuery->getFields()['article'];
        $articleFieldType = $articleField->getType();
        $this->assertEquals('article', $articleField->getName());
        $this->assertSame($this->blogArticle, $articleFieldType);
        $this->assertEquals('Article', $articleFieldType->getName());

        /** @var \Fubhy\GraphQL\Type\Definition\FieldDefinition $titleField */
        /** @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType $titleFieldType */
        $titleField = $articleFieldType->getFields()['title'];
        $titleFieldType = $titleField->getType();
        $this->assertEquals('title', $titleField->getName());
        $this->assertEquals(Type::stringType(), $titleFieldType);
        $this->assertEquals('String', $titleFieldType->getName());

        /** @var \Fubhy\GraphQL\Type\Definition\FieldDefinition $authorField */
        /** @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType $authorFieldType */
        /** @var \Fubhy\GraphQL\Type\Definition\FieldDefinition $recentArticleField */
        $authorField = $articleFieldType->getFields()['author'];
        $authorFieldType = $authorField->getType();
        $recentArticleField = $authorFieldType->getFields()['recentArticle'];
        $this->assertSame($this->blogArticle, $recentArticleField->getType());

        /** @var \Fubhy\GraphQL\Type\Definition\FieldDefinition $feedField */
        /** @var \Fubhy\GraphQL\Type\Definition\Types\ListModifier $feedFieldType */
        $feedField = $this->blogQuery->getFields()['feed'];
        $feedFieldType = $feedField->getType();
        $this->assertEquals('feed', $feedField->getName());
        $this->assertSame($this->blogArticle, $feedFieldType->getWrappedType());
    }

    public function testDefinesAMutationSchema()
    {
        $blogSchema = new Schema($this->blogQuery, $this->blogMutation);
        $this->assertSame($this->blogMutation, $blogSchema->getMutationType());

        /** @var \Fubhy\GraphQL\Type\Definition\FieldDefinition $writeMutation */
        /** @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType $writeMutationType */
        $writeMutation = $this->blogMutation->getFields()['writeArticle'];
        $writeMutationType = $writeMutation->getType();
        $this->assertEquals('writeArticle', $writeMutation->getName());
        $this->assertSame($this->blogArticle, $writeMutationType);
        $this->assertEquals('Article', $writeMutationType->getName());
    }

    public function testIncludesInterfacesSubtypesInTheTypeMap()
    {
        $someInterface = new InterfaceType('SomeInterface');
        $someSubType = new ObjectType('SomeSubType', [], [$someInterface]);
        $schema = new Schema($someInterface);

        $this->assertSame($someSubType, $schema->getTypeMap()['SomeSubType']);
    }

    public function testStringifiesSimpleTypes() {
        $this->assertEquals('Int', (string) Type::intType());
        $this->assertEquals('Article', (string) $this->blogArticle);
        $this->assertEquals('Interface', (string) new InterfaceType('Interface'));
        $this->assertEquals('Union', (string) new UnionType('Union'));
        $this->assertEquals('Enum', (string) new EnumType('Enum'));
        $this->assertEquals('InputObject', (string) new InputObjectType('InputObject'));
        $this->assertEquals('Int!', (string) new NonNullModifier(Type::intType()));
        $this->assertEquals('[Int]', (string) new ListModifier(Type::intType()));
        $this->assertEquals('[Int]!', (string) new NonNullModifier(new ListModifier(Type::intType())));
        $this->assertEquals('[Int!]', (string) new ListModifier(new NonNullModifier(Type::intType())));
        $this->assertEquals('[[Int]]', (string) new ListModifier(new ListModifier(Type::intType())));
    }

    /**
     * @dataProvider identifiesInputTypesProvider
     *
     * @param \Fubhy\GraphQL\Type\Definition\Types\TypeInterface $type
     * @param $answer
     */
    public function testIdentifiesInputTypes(TypeInterface $type, $answer) {
        $this->assertEquals($answer, $type->isInputType());
        $this->assertEquals($answer, (new ListModifier($type))->isInputType());
        $this->assertEquals($answer, (new NonNullModifier($type))->isInputType());
    }

    public function identifiesInputTypesProvider() {
        return [
            [Type::intType(), TRUE],
            [new ObjectType('Object'), FALSE],
            [new InterfaceType('Interface'), FALSE],
            [new UnionType('Union'), FALSE],
            [new EnumType('Enum'), TRUE],
            [new InputObjectType('InputObject'), TRUE],
        ];
    }

    /**
     * @dataProvider identifiesOutputTypesProvider
     *
     * @param \Fubhy\GraphQL\Type\Definition\Types\TypeInterface $type
     * @param bool $answer
     */
    public function testIdentifiesOutputTypes(TypeInterface $type, $answer) {
        $this->assertEquals($answer, $type->isOutputType($type));
        $this->assertEquals($answer, (new ListModifier($type))->isOutputType());
        $this->assertEquals($answer, (new NonNullModifier($type))->isOutputType());
    }

    public function identifiesOutputTypesProvider() {
        return [
            [Type::intType(), TRUE],
            [new ObjectType('Object'), TRUE],
            [new InterfaceType('Interface'), TRUE],
            [new UnionType('Union'), TRUE],
            [new EnumType('Enum'), TRUE],
            [new InputObjectType('InputObject'), FALSE],
        ];
    }

    /**
     * @dataProvider prohibitsPuttingNonObjcetTypesInUnionsProvider
     * @expectedException \LogicException
     * @expectedExceptionMessageRegExp /Union BadUnion may only contain object types, it cannot contain: .+\./
     *
     * @param \Fubhy\GraphQL\Type\Definition\Types\TypeInterface $type
     */
    public function testProhibitsPuttingNonObjcetTypesInUnions(TypeInterface $type) {
        new UnionType('BadUnion', [$type]);
    }

    public function prohibitsPuttingNonObjcetTypesInUnionsProvider() {
        return [
            [Type::intType()],
            [new NonNullModifier(Type::intType())],
            [new ListModifier(Type::intType())],
            [new InterfaceType('Interface')],
            [new UnionType('Union')],
            [new EnumType('Enum')],
            [new InputObjectType('InputObject')],
        ];
    }
}
