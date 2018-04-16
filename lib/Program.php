<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/Statement.php";
require_once __DIR__ . "/TokenException.php";

class Program {
	public function __construct () {
		$this->children = array();
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return $writer->writeProgram($this, $indents);
	}
}