<?php
// src/Doctrine/PowerFunction.php

namespace App\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class PowerFunction extends FunctionNode
{
    private Node $base;
    private Node $exponent;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->base = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->exponent = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'POWER('
            . $this->base->dispatch($sqlWalker)
            . ', '
            . $this->exponent->dispatch($sqlWalker)
            . ')';
    }
}
