<?php

require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class SwitchCase {
	public function __construct ($value, $blocks) {
		$this->value = $value;
		$this->blocks = $blocks;
	}
	public function write (ProgramWriter $writer, $indents) {
		$code = "case " . $this->value->write($writer, $indents) . ":\n";
		foreach ($this->blocks as $block) {
			$code .= "$indents\t" . $block->write($writer, $indents . "\t");
		}
		return $code;
	}
}