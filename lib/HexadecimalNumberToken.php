<?php

require_once __DIR__ . "/Token.php";

class HexadecimalNumberToken extends Token {
	public function __construct ($parser, $text) {
		parent::__construct($parser);
		// includes the hex digits only
		$this->text = $text;
	}
	public static function parse ($parser) {
		$number = "";
		if (!$parser->readString("0x")) return null;
		while (!$parser->isDone()) {
			$c = $parser->peek();
			if (stripos("0123456789abcdef", $c) === false) break;
			$number .= $c;
			$parser->advance();
		}
		if (!strlen($number)) throw new StringParserException($parser, "Expected hexadecimal number after '0x'");
		return new self($parser, $number);
	}
	public function __toString () {
		return "0x" . $this->text;
	}
}