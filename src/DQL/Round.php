<?php
/**
 * RoundFunction ::= "ROUND" "(" ArithmeticExpression ")"
 */

namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;


class Round extends FunctionNode {

    private $arithmeticExpression;

	/**
	 * @throws QueryException
	 */
	public function parse(Parser $parser): void {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->arithmeticExpression = $parser->SimpleArithmeticExpression();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string {
        return 'ROUND(' .
            $this->arithmeticExpression->dispatch($sqlWalker) .
        ')';
    }
}
