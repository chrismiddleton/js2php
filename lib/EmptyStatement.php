<?php

require_once __DIR__ . "/Symbol.php";

class EmptyStatement {
	private static $instance = null;
	public static function instance () {
		if (!isset(self::$instance)) self::$instance = new self();
		return self::$instance;
	}
	public function write ($indents) {
		return ";";
	}
}