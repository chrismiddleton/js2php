<?php

require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class IfStatement {
	public function __construct ($condition, $ifBlock, $elseBlock) {
		$this->condition = $condition;
		$this->ifBlock = $ifBlock;
		$this->elseBlock = $elseBlock;
	}
	public static function fromJs (ArrayIterator $tokens) {
		if (!Keyword::fromJs($tokens, "if")) return null;
		debug("found if statement");
		if (!Symbol::fromJs($tokens, "(")) {
			throw new TokenException($tokens, "Expected '(' after if");
		}
		$condition = Expression::fromJs($tokens);
		if (!Symbol::fromJs($tokens, ")")) {
			throw new TokenException($tokens, "Expected ')' after if condition");
		}
		$ifBlock = Block::fromJs($tokens);
		$elseBlock = null;
		if (Keyword::fromJs($tokens, "else")) {
			debug("found else");
			$elseBlock = Block::fromJs($tokens);
		}
		return new self ($condition, $ifBlock, $elseBlock);
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeIfStatement($this, $indents);
	}
}