<?php

namespace Fubhy\GraphQL\Language;

use Fubhy\GraphQL\Language\Node\Argument;
use Fubhy\GraphQL\Language\Node\ArrayValue;
use Fubhy\GraphQL\Language\Node\BooleanValue;
use Fubhy\GraphQL\Language\Node\Directive;
use Fubhy\GraphQL\Language\Node\Document;
use Fubhy\GraphQL\Language\Node\EnumValue;
use Fubhy\GraphQL\Language\Node\Field;
use Fubhy\GraphQL\Language\Node\FloatValue;
use Fubhy\GraphQL\Language\Node\FragmentDefinition;
use Fubhy\GraphQL\Language\Node\FragmentSpread;
use Fubhy\GraphQL\Language\Node\InlineFragment;
use Fubhy\GraphQL\Language\Node\IntValue;
use Fubhy\GraphQL\Language\Node\ListType;
use Fubhy\GraphQL\Language\Node\Name;
use Fubhy\GraphQL\Language\Node\NamedType;
use Fubhy\GraphQL\Language\Node\NonNullType;
use Fubhy\GraphQL\Language\Node\ObjectField;
use Fubhy\GraphQL\Language\Node\ObjectValue;
use Fubhy\GraphQL\Language\Node\OperationDefinition;
use Fubhy\GraphQL\Language\Node\SelectionSet;
use Fubhy\GraphQL\Language\Node\StringValue;
use Fubhy\GraphQL\Language\Node\Variable;
use Fubhy\GraphQL\Language\Node\VariableDefinition;

class Parser
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Fubhy\GraphQL\Language\Source
     */
    protected $source;

    /**
     * @var \Fubhy\GraphQL\Language\Lexer
     */
    protected $lexer;

    /**
     * @var \Fubhy\GraphQL\Language\Token
     */
    protected $token;

    /**
     * @var int
     */
    protected $cursor;

    /**
     * Constructor.
     *
     * Returns the parser object that is used to store state throughout the
     * process of parsing.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @param \Fubhy\GraphQL\Language\Source $source
     *
     * @return \Fubhy\GraphQL\Language\Node\Document
     */
    public function parse(Source $source)
    {
        $this->source = $source;
        $this->lexer = new Lexer($source);
        $this->token = $this->lexer->readToken(0);
        $this->cursor = 0;

        return $this->parseDocument();
    }

    /**
     * Returns a location object, used to identify the place in the source that
     * created a given parsed object.
     *
     * @param int $start
     *
     * @return \Fubhy\GraphQL\Language\Location|null
     */
    protected function location($start)
    {
        if (!empty($this->options['noLocation'])) {
            return NULL;
        }

        if (!empty($this->options['noSource'])) {
            return new Location($start, $this->cursor);
        }

        return new Location($start, $this->cursor, $this->source);
    }


    /**
     * Moves the internal parser object to the next lexed token.
     */
    protected function advance()
    {
        $this->cursor = $this->token->getEnd();
        return $this->token = $this->lexer->readToken($this->cursor);
    }

    /**
     * Determines if the next token is of a given kind
     *
     * @param int $type
     *
     * @return bool
     */
    protected function peek($type)
    {
        return $this->token->getType() === $type;
    }

    /**
     * If the next token is of the given kind, return true after advancing the
     * parser. Otherwise, do not change the parser state and return false.
     *
     * @param int $type
     *
     * @return bool
     */
    protected function skip($type)
    {
        if ($match = ($this->token->getType() === $type)) {
            $this->advance();
        }

        return $match;
    }

    /**
     * If the next token is of the given kind, return that token after advancing
     * the parser. Otherwise, do not change the parser state and return false.
     *
     * @param int $type
     *
     * @return \Fubhy\GraphQL\Language\Token
     *
     * @throws \Exception
     */
    protected function expect($type)
    {
        if ($this->token->getType() !== $type) {
            throw new \Exception(sprintf('Expected %s, found %s', Token::typeToString($type), (string) $this->token));
        }

        $token = $this->token;
        $this->advance();
        return $token;
    }

    /**
     * If the next token is a keyword with the given value, return that token
     * after advancing the parser. Otherwise, do not change the parser state and
     * return false.
     *
     * @param string $value
     *
     * @return \Fubhy\GraphQL\Language\Token
     *
     * @throws \Exception
     */
    protected function expectKeyword($value)
    {
        if ($this->token->getType() !== Token::NAME_TYPE || $this->token->getValue() !== $value) {
            throw new \Exception(sprintf('Expected %s, found %s', $value, $this->token->getDescription()));
        }

        return $this->advance();
    }

    /**
     * Helper protected function for creating an error when an unexpected lexed token is
     * encountered.
     *
     * @param \Fubhy\GraphQL\Language\Token|null $atToken
     *
     * @return \Exception
     */
    protected function unexpected(Token $atToken = NULL)
    {
        $token = $atToken ?: $this->token;

        return new \Exception(sprintf('Unexpected %s', $token->getDescription()));
    }

    /**
     * Returns a possibly empty list of parse nodes, determined by the parseFn.
     *
     * This list begins with a lex token of openKind and ends with a lex token
     * of closeKind. Advances the parser to the next lex token after the closing
     * token.
     *
     * @param int $openKind
     * @param callable $parseFn
     * @param int $closeKind
     *
     * @return array
     */
    protected function any($openKind, $parseFn, $closeKind)
    {
        $this->expect($openKind);
        $nodes = [];

        while (!$this->skip($closeKind)) {
            array_push($nodes, $parseFn($this));
        }

        return $nodes;
    }

    /**
     * Returns a non-empty list of parse nodes, determined by the parseFn.
     *
     * This list begins with a lex token of openKind and ends with a lex token
     * of closeKind. Advances the parser to the next lex token after the closing
     * token.
     *
     * @param int $openKind
     * @param callable $parseFn
     * @param int $closeKind
     *
     * @return array
     */
    protected function many($openKind, $parseFn, $closeKind)
    {
        $this->expect($openKind);
        $nodes = [$parseFn($this)];

        while (!$this->skip($closeKind)) {
            array_push($nodes, $parseFn($this));
        }

        return $nodes;
    }

    /**
     * Converts a name lex token into a name parse node.
     *
     * @return \Fubhy\GraphQL\Language\Node\Name
     */
    protected function parseName()
    {
        $start = $this->token->getStart();
        $token = $this->expect(Token::NAME_TYPE);

        return new Name($token->getValue(), $this->location($start));
    }

    /**
     * Converts a fragment name lex token into a name parse node.
     *
     * @return \Fubhy\GraphQL\Language\Node\Name
     *
     * @throws \Exception
     */
    function parseFragmentName()
    {
        if ($this->token->getValue() === 'on') {
            throw $this->unexpected();
        }
        return $this->parseName();
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\Document
     *
     * @throws \Exception
     */
    protected function parseDocument()
    {
        $start = $this->token->getStart();
        $definitions = [];

        do {
            if ($this->peek(Token::BRACE_L_TYPE)) {
                $definitions[] = $this->parseOperationDefinition();
            } else if ($this->peek(Token::NAME_TYPE)) {
                $value = $this->token->getValue();
                if ($value === 'query' || $value === 'mutation') {
                    $definitions[] = $this->parseOperationDefinition();
                } else if ($value === 'fragment') {
                    $definitions[] = $this->parseFragmentDefinition();
                } else {
                    throw $this->unexpected();
                }
            } else {
                throw $this->unexpected();
            }
        } while (!$this->skip(Token::EOF_TYPE));

        return new Document($definitions, $this->location($start));
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\OperationDefinition
     */
    protected function parseOperationDefinition()
    {
        $start = $this->token->getStart();

        if ($this->peek(Token::BRACE_L_TYPE)) {
            return new OperationDefinition('query', NULL, [], [],
                $this->parseSelectionSet(),
                $this->location($start)
            );
        }

        $operationToken = $this->expect(Token::NAME_TYPE);
        $operation = $operationToken->getValue();

        return new OperationDefinition($operation,
            $this->parseName(),
            $this->parseVariableDefinitions(),
            $this->parseDirectives(),
            $this->parseSelectionSet(),
            $this->location($start)
        );
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\VariableDefinition[]
     */
    protected function parseVariableDefinitions()
    {
      return $this->peek(Token::PAREN_L_TYPE) ? $this->many(Token::PAREN_L_TYPE, [$this, 'parseVariableDefinition'], Token::PAREN_R_TYPE) : [];
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\VariableDefinition
     */
    protected function parseVariableDefinition()
    {
        $start = $this->token->getStart();
        $variable = $this->parseVariable();
        $this->expect(Token::COLON_TYPE);

        return new VariableDefinition(
            $variable,
            $this->parseType(),
            $this->skip(Token::EQUALS_TYPE) ? $this->parseValue(TRUE) : NULL,
            $this->location($start)
        );
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\Variable
     */
    protected function parseVariable() {
        $start = $this->token->getStart();
        $this->expect(Token::DOLLAR_TYPE);

        return new Variable($this->parseName(), $this->location($start));
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\SelectionSet
     */
    protected function parseSelectionSet()
    {
        $start = $this->token->getStart();

        return new SelectionSet(
            $this->many(Token::BRACE_L_TYPE, [$this, 'parseSelection'], Token::BRACE_R_TYPE),
            $this->location($start)
        );
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\SelectionInterface
     */
    protected function parseSelection()
    {
        return $this->peek(Token::SPREAD_TYPE) ? $this->parseFragment() : $this->parseField();
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\Field
     */
    protected function parseField()
    {
        $start = $this->token->getStart();
        $name = $this->parseName();
        $alias = NULL;

        if ($this->skip(Token::COLON_TYPE)) {
            $alias = $name;
            $name = $this->parseName();
        }

        return new Field($name, $alias,
            $this->parseArguments(),
            $this->parseDirectives(),
            $this->peek(Token::BRACE_L_TYPE) ? $this->parseSelectionSet() : NULL,
            $this->location($start)
        );
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\Argument[]
     */
    protected function parseArguments()
    {
        return $this->peek(Token::PAREN_L_TYPE) ? $this->many(Token::PAREN_L_TYPE, [$this, 'parseArgument'], Token::PAREN_R_TYPE) : [];
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\Argument
     */
    protected function parseArgument()
    {
        $start = $this->token->getStart();
        $name = $this->parseName();

        $this->expect(Token::COLON_TYPE);
        $value = $this->parseValue(FALSE);

        return new Argument($name, $value, $this->location($start));
    }

    /**
     * Corresponds to both FragmentSpread and InlineFragment in the spec.
     *
     * @return \Fubhy\GraphQL\Language\Node\FragmentSpread|\Fubhy\GraphQL\Language\Node\InlineFragment
     */
    protected function parseFragment()
    {
        $start = $this->token->getStart();
        $this->expect(Token::SPREAD_TYPE);

        if ($this->token->getValue() === 'on') {
            $this->advance();

            return new InlineFragment(
                $this->parseNamedType(),
                $this->parseDirectives(),
                $this->parseSelectionSet(),
                $this->location($start)
            );
        }

        return new FragmentSpread(
            $this->parseFragmentName(),
            $this->parseDirectives(),
            $this->location($start)
        );
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\FragmentDefinition
     */
    protected function parseFragmentDefinition()
    {
        $start = $this->token->getStart();
        $this->expectKeyword('fragment');
        $name = $this->parseName();
        $this->expectKeyword('on');
        $typeCondition = $this->parseNamedType();

        return new FragmentDefinition(
            $name,
            $typeCondition,
            $this->parseDirectives(),
            $this->parseSelectionSet(),
            $this->location($start)
        );
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\ValueInterface
     */
    protected function parseVariableValue()
    {
        return $this->parseValue(FALSE);
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\ValueInterface
     */
    protected function parseConstValue()
    {
        return $this->parseValue(TRUE);
    }

    /**
     * @param bool $isConst
     *
     * @return \Fubhy\GraphQL\Language\Node\ValueInterface
     *
     * @throws \Exception
     */
    protected function parseValue($isConst)
    {
        $start = $this->token->getStart();
        $token = $this->token;

        switch ($this->token->getType()) {
            case Token::BRACKET_L_TYPE:
                return $this->parseArray($isConst);
            case Token::BRACE_L_TYPE:
                return $this->parseObject($isConst);
            case Token::INT_TYPE:
                $this->advance();
                return new IntValue($token->getValue(), $this->location($start));
            case Token::FLOAT_TYPE:
                $this->advance();
                return new FloatValue($token->getValue(), $this->location($start));
            case Token::STRING_TYPE:
                $this->advance();
                return new StringValue($token->getValue(), $this->location($start));
            case Token::NAME_TYPE:
                $this->advance();
                switch ($value = $token->getValue()) {
                    case 'true':
                    case 'false':
                        return new BooleanValue($value === 'true', $this->location($start));
                }
                return new EnumValue($value, $this->location($start));
            case Token::DOLLAR_TYPE:
                if (!$isConst) {
                    return $this->parseVariable();
                }
                break;
        }

        throw $this->unexpected();
    }

    /**
     * @param bool $isConst
     *
     * @return \Fubhy\GraphQL\Language\Node\ArrayValue
     */
    protected function parseArray($isConst)
    {
        $start = $this->token->getStart();
        $item = $isConst ? 'parseConstValue' : 'parseVariableValue';

        return new ArrayValue(
            $this->any(Token::BRACKET_L_TYPE, [$this, $item], Token::BRACKET_R_TYPE),
            $this->location($start)
        );
    }

    /**
     * @param bool $isConst
     *
     * @return \Fubhy\GraphQL\Language\Node\ObjectValue
     */
    protected function parseObject($isConst)
    {
        $start = $this->token->getStart();
        $this->expect(Token::BRACE_L_TYPE);

        $fieldNames = [];
        $fields = [];
        while (!$this->skip(Token::BRACE_R_TYPE)) {
            array_push($fields, $this->parseObjectField($isConst, $fieldNames));
        }

        return new ObjectValue($fields, $this->location($start));
    }

    /**
     * @param bool $isConst
     * @param array $fieldNames
     *
     * @return \Fubhy\GraphQL\Language\Node\ObjectField
     *
     * @throws \Exception
     */
    protected function parseObjectField($isConst, &$fieldNames)
    {
        $start = $this->token->getStart();
        $name = $this->parseName();
        $value = $name->get('value');

        if (array_key_exists($value, $fieldNames)) {
            throw new \Exception(sprintf('Duplicate input object field %s.', $value));
        }

        $fieldNames[$value] = TRUE;
        $this->expect(Token::COLON_TYPE);

        return new ObjectField($name, $this->parseValue($isConst), $this->location($start));
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\Directive[]
     */
    protected function parseDirectives()
    {
        $directives = [];
        while ($this->peek(Token::AT_TYPE)) {
            array_push($directives, $this->parseDirective());
        }

        return $directives;
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\Directive
     *
     * @throws \Exception
     */
    protected function parseDirective()
    {
        $start = $this->token->getStart();
        $this->expect(Token::AT_TYPE);

        return new Directive(
            $this->parseName(),
            $this->parseArguments(),
            $this->location($start)
        );
    }

    /**
     * Handles the type: TypeName, ListType, and NonNullType parsing rules.
     *
     * @return \Fubhy\GraphQL\Language\Node\TypeInterface
     *
     * @throws \Exception
     */
    protected function parseType()
    {
        $start = $this->token->getStart();

        if ($this->skip(Token::BRACKET_L_TYPE)) {
            $type = $this->parseType();
            $this->expect(Token::BRACKET_R_TYPE);
            $type = new ListType($type, $this->location($start));
        } else {
            $type = $this->parseNamedType();
        }

        if ($this->skip(Token::BANG_TYPE)) {
            return new NonNullType($type, $this->location($start));
        }

        return $type;
    }

    /**
     * @return \Fubhy\GraphQL\Language\Node\NamedType
     */
    function parseNamedType()
    {
        $start = $this->token->getStart();

        return new NamedType($this->parseName(), $this->location($start));
    }
}
