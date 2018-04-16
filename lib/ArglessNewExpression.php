<?php

require_once __DIR__ . "/FunctionCallLevelExpression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class ArglessNewExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public static function fromJs (ArrayIterator $tokens) {
		if (!Symbol::fromJs($tokens, "new")) {
			return FunctionCallLevelExpression::fromJs($tokens);
		}
		$expression = self::fromJs($tokens);
		if (!$expression) throw new TokenException("Expected expression after 'new'");
		return new self($expression);
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeArglessNewExpression($this, $indents);
	}
}