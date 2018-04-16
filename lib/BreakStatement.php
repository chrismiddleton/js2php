<?php

require_once __DIR__ . "/Keyword.php";

class BreakStatement {
	// TODO: handle labeled break statements
	public function __construct () {}
	public static function fromJs (ArrayIterator $tokens) {
		if (!Keyword::fromJs($tokens, "break")) return null;
		return new self();
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeBreakStatement($this, $indents);
	}
}