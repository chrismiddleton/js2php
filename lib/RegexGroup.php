<?php

require_once __DIR__ . "/RegexAlternation.php";
require_once __DIR__ . "/StringParserException.php";

class RegexGroup {
	public function __construct ($modifier, $element) {
		$this->modifier = $modifier;
		$this->element = $element;
	}
	public static function parse ($parser) {
		if (!$parser->readString("(")) return null;
		$modifiers = array("?:", "?=", "?!");
		$ourModifier = null;
		foreach ($modifiers as $modifier) {
			if ($parser->readString($modifier)) {
				$ourModifier = $modifier;
				break;
			}
		}
		$element = RegexAlternation::parse($parser);
		if (!$element) {
			throw new StringParserException($parser, "Expected regex element inside of capture group");
		}
		if (!$parser->readString(")")) {
			echo substr($parser->str, $parser->i, 20); // fdo
			throw new StringParserException($parser, "Unterminated regex capture group");
		}
		return new self($ourModifier, $element);
	}
	public function __toString () {
		return "(" . ($this->modifier ? $this->modifier : "") . $this->element . ")";
	}
}