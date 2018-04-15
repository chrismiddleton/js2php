<?php

require_once __DIR__ . "/RegexEmptyElement.php";
require_once __DIR__ . "/RegexSequence.php";

class RegexAlternation {

	public function __construct ($elements) {
		$this->elements = $elements;
	}
	
	public static function parse ($parser) {
		$elements = array();
		while (!$parser->isDone()) {
			if ($element = RegexSequence::parse($parser)) {
				$elements[] = $element;
			} else {
				// if we previously read a "|" but have no more text, then that can indicate an empty element
				// e.g. such as /a|/, which is allowed.
				if (count($elements)) {
					// empty element
					$elements[] = new RegexEmptyElement();
				}
			}
			if (!$parser->readString("|")) break;
		}
		if (!count($elements)) return null;
		// no alternations were found in this case
		if (count($elements) === 1) return $elements[0];
		return new self($elements);
	}
	public function __toString () {
		return implode("|", $this->elements);
	}

}