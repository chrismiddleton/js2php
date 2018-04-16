<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";

class ThrowStatement {
	public function __construct ($value) {
		$this->value = $value;
	}
	public static function fromJs (ArrayIterator $tokens) {
		if (!Keyword::fromJs($tokens, "throw")) return null;
		debug("found throw statement");
		// can be null, that's OK
		$value = Expression::fromJs($tokens);
		// optional semicolon
		Symbol::fromJs($tokens, ";");
		// TODO: handle cutting off early when newline (e.g. "return 5\n+6" should just return 5 in JS)
		return new self($value);
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeThrowStatement($this, $indents);
	}
}