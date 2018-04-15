<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";

class ReturnStatement {
	public function __construct ($value) {
		$this->value = $value;
	}
	public static function fromJs ($tokens) {
		if (!Keyword::fromJs($tokens, "return")) return;
		debug("found return statement");
		// can be null, that's OK
		$value = Expression::fromJs($tokens);
		// optional semicolon
		Symbol::fromJs($tokens, ";");
		// TODO: handle cutting off early when newline (e.g. "return 5\n+6" should just return 5 in JS)
		return new self($value);
	}
	public function toPhp ($indents) {
		return "return " . ($this->value ? $this->value->toPhp($indents) : "") . ";\n";
	}
}