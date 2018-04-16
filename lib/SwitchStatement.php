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
	public function write (ProgramWriter $writer, $indents) {
		$code = "switch (" . $this->test->write($writer, $indents) . ") {\n";
		foreach ($this->cases as $switchCase) {
			$code .= "$indents\t" . $switchCase->write($writer, $indents . "\t") . "\n";
		}
		$code .= "$indents}\n";
		return $code;
	}
}