<?php

require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class WhileLoop {
	// TODO
	public function __construct ($test, $block) {
		$this->test = $test;
		$this->block = $block;
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for while loop");
		if (!Keyword::fromJs($tokens, "while")) return null;
		debug("found while loop start");
		if (!Symbol::fromJs($tokens, "(")) {
			throw new TokenException($tokens, "Expected '(' after 'while' keyword");
		}
		$test = Expression::fromJs($tokens);
		if (!$test) {
			throw new TokenException($tokens, "Expected while loop test after '('");
		}
		if (!Symbol::fromJs($tokens, ")")) {
			throw new TokenException($tokens, "Expected ')' after while loop test");
		}
		$block = Block::fromJs($tokens);
		if (!$block) throw new TokenException($tokens, "Expected while loop body");
		return new self($test, $block);
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeWhileLoop($this, $indents);
	}
}