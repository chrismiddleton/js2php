<?php

require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class DoWhileLoop {
	// TODO
	public function __construct ($block, $test) {
		$this->block = $block;
		$this->test = $test;
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for do-while loop");
		if (!Keyword::fromJs($tokens, "do")) return null;
		debug("found do-while loop start");
		// TODO: require braces?
		$block = Block::fromJs($tokens);
		if (!$block) throw new TokenException($tokens, "Expected do-while loop body");
		if (!Keyword::fromJs($tokens, "while")) {
			throw new TokenException($tokens, "Expected 'while' keyword after do-while loop body");
		}
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
		return new self($block, $test);
	}
	public function toPhp ($indents) {
		return "do " . $this->block->toPhp($indents) . " while (" . $this->test->toPhp($indents) . ")";
	}
}