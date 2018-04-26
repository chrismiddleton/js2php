<?php

require_once __DIR__ . "/Node.php";

abstract class Expression extends Node {
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents);
	}
}