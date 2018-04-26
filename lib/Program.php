<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/Node.php";
require_once __DIR__ . "/Statement.php";
require_once __DIR__ . "/TokenException.php";

class Program extends Node {
	public function __construct () {
		$this->children = array();
	}
	public function write (ProgramWriter $writer, $indents = "") {
		$code = parent::write($writer, $indents);
		$code .= $writer->writeProgram($this, $indents);
		return $code;
	}
}