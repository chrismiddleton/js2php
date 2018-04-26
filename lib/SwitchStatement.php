<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/DefaultSwitchCase.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/SwitchCase.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class SwitchStatement extends Node {
	public function __construct ($test, $cases) {
		$this->test = $test;
		$this->cases = $cases;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeSwitchStatement($this, $indents);
	}
}