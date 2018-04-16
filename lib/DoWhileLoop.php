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
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeDoWhileLoop($this, $indents);
	}
}