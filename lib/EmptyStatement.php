<?php

require_once __DIR__ . "/Symbol.php";

class EmptyStatement {
	private static $instance = null;
	public static function fromJs ($tokens) {
		if (Symbol::fromJs($tokens, ";")) return self::instance();
	}
	public static function instance () {
		if (!isset(self::$instance)) self::$instance = new self();
		return self::$instance;
	}
	public function toPhp ($indents) {
		return ";";
	}
}