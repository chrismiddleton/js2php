<?php

require_once __DIR__ . "/Node.php";
require_once __DIR__ . "/Symbol.php";

class EmptyStatement extends Node {
	private static $instance = null;
	public function __construct () {}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . ";";
	}
}