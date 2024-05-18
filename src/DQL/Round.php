<?php
/**
 * RoundFunction ::= "ROUND" "(" ArithmeticExpression ")"
 */

namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;


class Round extends FunctionNode {

    private $arithmeticExpression;

    public function parse(Parser $parser) {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->arithmeticExpression = $parser->SimpleArithmeticExpression();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker) {
        return 'ROUND(' .
            $this->arithmeticExpression->dispatch($sqlWalker) .
        ')';
    }
}
