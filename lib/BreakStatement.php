<?php

require_once __DIR__ . "/Keyword.php";

class BreakStatement extends Node {
	// TODO: handle labeled break statements
	public function __construct () {}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeBreakStatement($this, $indents);
	}
}