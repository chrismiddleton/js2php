<?php

require_once __DIR__ . "/RegexSimpleElement.php";

class RegexCharacterClass {

	public function __construct ($negated, $elements) {
		$this->negated = $negated;
		$this->elements = $elements;
	}

	// TODO: handle ranges
	public static function parse ($parser) {
		$start = $parser->pos();
		$negated = false;
		if ($parser->readString("[")) {
			if ($parser->readString("^")) {
				$negated = true;
			}
			while (!$parser->isDone()) {
				if ($parser->readEol()) {
					// invalid
					break;
				}
				$c = $parser->peek();
				if ($c === "]") {
					$foundEnd = true;
					$parser->advance();
					break;
				} else if ($c === "/") {
					// invalid
					break;
				} else if ($element = RegexSimpleElement::parse($parser, true)) {
					$elements[] = $element;
				}
			}
			if (!$foundEnd) {
				// backtrack
				$parser->seek($start);
				return null;
			}
			return new self($negated, $elements);
		}
	
	}
	public function __toString () {
		return "[" . ($this->negated ? "^" : "") . implode("", $this->elements) . "]";
	}

}