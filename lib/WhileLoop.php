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
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeWhileLoop($this, $indents);
	}
}