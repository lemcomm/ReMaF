<?php
/**
 * PowFunction ::= "POW" "(" ArithmeticExpression "," ArithmeticExpression ")"
 */

namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;


class Pow extends FunctionNode {

	public $firstExpression = null;
	public $secondExpression = null;

	/**
	 * @throws QueryException
	 */
	public function parse(Parser $parser): void {
		$parser->match(TokenType::T_IDENTIFIER);
		$parser->match(TokenType::T_OPEN_PARENTHESIS);
		$this->firstExpression = $parser->ArithmeticExpression();
		$parser->match(TokenType::T_COMMA);
		$this->secondExpression = $parser->ArithmeticExpression();
		$parser->match(TokenType::T_CLOSE_PARENTHESIS);
	}

	public function getSql(SqlWalker $sqlWalker): string {
		return 'POW(' .
			$this->firstExpression->dispatch($sqlWalker) . ', ' .
			$this->secondExpression->dispatch($sqlWalker) .
		')';
	}
}