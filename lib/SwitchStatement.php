<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/DefaultSwitchCase.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/SwitchCase.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class SwitchStatement {
	public function __construct ($test, $cases) {
		$this->test = $test;
		$this->cases = $cases;
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for switch statement");
		if (!Keyword::fromJs($tokens, "switch")) return null;
		debug("found start of switch statement");
		if (!Symbol::fromJs($tokens, "(")) return null;
		if (!($test = Expression::fromJs($tokens))) {
			throw new TokenException($tokens, "Expected switch test after '('");
		}
		if (!Symbol::fromJs($tokens, ")")) {
			throw new TokenException($tokens, "Expected ')' after switch test");
		}
		if (!Symbol::fromJs($tokens, "{")) {
			throw new TokenException($tokens, "Expected '{' to start switch body");
		}
		$cases = array();
		while ($tokens->valid()) {
			$switchCase = SwitchCase::fromJs($tokens) or
				$switchCase = DefaultSwitchCase::fromJs($tokens);
			if (!$switchCase) break;
			$cases[] = $switchCase;
		}
		if (!Symbol::fromJs($tokens, "}")) {
			throw new TokenException($tokens, "Expected '}' after switch body");
		}
		return new self($test, $cases);
	}
	public function write (ProgramWriter $writer, $indents) {
		$code = "switch (" . $this->test->write($writer, $indents) . ") {\n";
		foreach ($this->cases as $switchCase) {
			$code .= "$indents\t" . $switchCase->write($writer, $indents . "\t") . "\n";
		}
		$code .= "$indents}\n";
		return $code;
	}
}