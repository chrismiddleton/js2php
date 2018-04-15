<?php

require_once __DIR__ . "/Token.php";

class NumberToken extends Token {
	public function __construct ($parser, $text) {
		parent::__construct($parser);
		$this->text = $text;
	}
	public static function parse ($parser) {
		$number = "";
		while (!$parser->isDone()) {
			$c = $parser->peek();
			if (strpos("1234567890", $c) === false) break;
			$number .= $c;
			$parser->advance();
		}
		return strlen($number) ? new self($parser, $number) : null;
	}
	public function __toString () {
		return $this->text;
	}
}