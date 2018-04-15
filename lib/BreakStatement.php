<?php

require_once __DIR__ . "/Keyword.php";

class BreakStatement {
	// TODO: handle labeled break statements
	public function __construct () {}
	public static function fromJs ($tokens) {
		if (!Keyword::fromJs($tokens, "break")) return null;
		return new self();
	}
	public function toPhp ($indents) {
		return "break;";
	}
}