<?php

require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/EmptyStatement.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/ExpressionStatement.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";
require_once __DIR__ . "/VarDefinitionStatement.php";

class ForLoop {
	public function __construct ($init, $test, $update, $body) {
		// statement
		$this->init = $init;
		// statement
		$this->test = $test;
		// expression or null
		$this->update = $update;
		// block
		$this->body = $body;
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for 'for' loop");
		if (!Keyword::fromJs($tokens, "for")) return null;
		debug("found 'for' loop");
		if (!Symbol::fromJs($tokens, "(")) {
			throw new TokenException($tokens, "Expected '(' after 'for' keyword");
		}
		$init = VarDefinitionStatement::fromJs($tokens) or
			$init = ExpressionStatement::fromJs($tokens) or
			$init = EmptyStatement::fromJs($tokens);
		if (!$init) throw new TokenException($tokens, "Expected for loop initialization");
		$test = ExpressionStatement::fromJs($tokens) or
			$test = EmptyStatement::fromJs($tokens);
		if (!$test) throw new TokenException($tokens, "Expected for loop test");
		$update = Expression::fromJs($tokens);
		if (!Symbol::fromJs($tokens, ")")) {
			throw new TokenException($tokens, "Expected ')' after for loop header");
		}
		$body = Block::fromJs($tokens);
		if (!$body) throw new TokenException($tokens, "Expected for loop body");
		return new self($init, $test, $update, $body);
	}
	public function toPhp ($indents) {
		return "for (" .
			$this->init->toPhp($indents) .
			" " .
			$this->test->toPhp($indents) . 
			($this->update ? (" " . $this->update->toPhp($indents)) : "") . 
			") " . $this->body->toPhp($indents . "\t") . "\n";
		return $code;
	}
}