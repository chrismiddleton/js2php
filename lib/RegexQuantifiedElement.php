<?php

require_once __DIR__ . "/NumberToken.php";
require_once __DIR__ . "/RegexCharacterClass.php";
require_once __DIR__ . "/RegexGroup.php";
require_once __DIR__ . "/RegexSimpleElement.php";

class RegexQuantifiedElement {

	public function __construct ($element, $quantifier) {
		$this->element = $element;
		$this->quantifier = $quantifier;
	}

	public static function parse ($parser) {
		$start = $parser->pos();
		// TODO: handle groups as well
		if (
			$element = RegexCharacterClass::parse($parser) or
			$element = RegexGroup::parse($parser) or
			$element = RegexSimpleElement::parse($parser)
		) {
			$simpleQuantifiers = array("*?", "*", "+?", "+", "?");
			foreach ($simpleQuantifiers as $quantifier) {
				if ($parser->readString($quantifier)) {
					return new self($element, $quantifier);
				}
			}
			if ($parser->readString("{")) {
				$from = NumberToken::parse($parser);
				// TODO: for now, treating invalid regex as not regex
				if (!$from) {
					$parser->seek($start);
					return null;
				}
				$comma = false;
				if ($parser->readString(",")) {
					$comma = true;
					$to = NumberToken::parse($parser);
				}
				if (!$parser->readString("}")) {
					$parser->seek($start);
					return null;
				}
				return new self($element, "\{$from" . ($comma ? ",$to" : ""));
			} else {
				// just return the element we found
				return $element;
			}
		}
	}
	public function __toString () {
		return $this->element . $this->quantifier;
	}
	
}