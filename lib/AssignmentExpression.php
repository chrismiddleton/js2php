<?php

require_once __DIR__ . "/AssignmentExpression.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TernaryExpression.php";
require_once __DIR__ . "/TokenException.php";

class AssignmentExpression extends Expression {
	public function __construct ($left, $symbol, $right) {
		$this->left = $left;
		$this->symbol = $symbol;
		$this->right = $right;
	}
	public static function fromJs ($tokens) {
		debug("looking for assignment expression");
		// TODO: verify that it's a valid LHS?
		$left = TernaryExpression::fromJs($tokens);
		if (!$left) return;
		if (!$tokens->valid()) return null;
		$afterLeft = $tokens->key();
		$symbols = array("=", "+=", "-=", "*=", "/=", "%=", "<<=", ">>=", ">>>=", "~=", "^=", "&=", "|=");
		foreach ($symbols as $symbol) {
			$symbolFound = Symbol::fromJs($tokens, $symbol);
			if ($symbolFound) break;
		}
		if (!$symbolFound) {
			$tokens->seek($afterLeft);
			return $left;
		}
		debug("found '{$symbolFound->symbol}' expression");
		$right = AssignmentExpression::fromJs($tokens);
		if (!$right) throw new TokenException($tokens, "Expected RHS of assignment");
		return new self($left, $symbolFound, $right);
	}
	public function toPhp ($indents) {
		return $this->left->toPhp($indents) . " {$this->symbol->symbol} " . $this->right->toPhp($indents);
	}
}