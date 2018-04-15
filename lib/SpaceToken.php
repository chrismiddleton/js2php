<?php

require_once __DIR__ . "/Token.php";

class SpaceToken extends Token {
	public function __construct ($parser, $space) {
		parent::__construct($parser);
		$this->space = $space;
	}
	public static function parse ($parser) {
		$text = "";
		while (!$parser->isDone()) {
			$c = $parser->peek();
			if (!ctype_space($c)) break;
			$text .= $c;
			$parser->advance();
		}
		return strlen($text) ? new self($parser, $text) : null;
	}
	public function __toString () {
		return $this->space;
	}
}