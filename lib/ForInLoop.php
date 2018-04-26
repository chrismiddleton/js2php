<?php

require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/SingleVarDeclaration.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class ForInLoop extends Node {
	public function __construct ($declaration, $object, $body) {
		$this->declaration = $declaration;
		$this->object = $object;
		$this->body = $body;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeForInLoop($this, $indents);
	}
}