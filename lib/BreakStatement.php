<?php

require_once __DIR__ . "/Keyword.php";

class BreakStatement {
	// TODO: handle labeled break statements
	public function __construct () {}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeBreakStatement($this, $indents);
	}
}