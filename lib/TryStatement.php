<?php

require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/Identifier.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class TryStatement {
	public function __construct ($tryBlock, $catchBlock, $catchParameter, $finallyBlock) {
		$this->tryBlock = $tryBlock;
		$this->catchBlock = $catchBlock;
		$this->catchParameter = $catchParameter;
		$this->finallyBlock = $finallyBlock;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeTryStatement($this, $indents);
	}
}