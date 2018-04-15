<?php

require_once __DIR__ . "/RegexQuantifiedElement.php";

class RegexSequence {

	public function __construct ($elements) {
		$this->elements = $elements;
	}
	
	public static function parse ($parser) {
		$elements = array();
		while (!$parser->isDone()) {
			if ($element = RegexQuantifiedElement::parse($parser)) {
				$elements[] = $element;
			} else {
				break;
			}
		}
		if (count($elements) > 1) {
			return new self($elements);
		} else if (count($elements) === 1) {
			return $elements[0];
		} else {
			return null;
		}
	}
	
	public function __toString () {
		return implode("", $this->elements);
	}

}