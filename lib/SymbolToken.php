<?php

require_once __DIR__ . "/Token.php";

class SymbolToken extends Token {
	public function __construct ($parser, $symbol) {
		parent::__construct($parser);
		$this->symbol = $symbol;
	}
	public static function parse ($parser, $symbols) {
		foreach ($symbols as $symbol) {
			$str = $parser->readString($symbol);
			if ($str) return new self($parser, $str);
		}
	}
	public function __toString () {
		return $this->symbol;
	}
}