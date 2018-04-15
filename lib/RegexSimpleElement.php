<?php

require_once __DIR__ . "/RegexCharacter.php";
require_once __DIR__ . "/RegexEscapedCharacter.php";

// class RegexAnchor {
// 
// }
// 
// class RegexCharacterEscape {
// 
// }

abstract class RegexSimpleElement {
	public static function parse ($parser, $inCharacterClass = false) {
		// TODO: for now just parsing all as characters
		if ($parser->readEol()) {
			// invalid
			return null;
		}
		$c = $parser->peek();
		if ($c === "/") {
			// terminates the regex
			return null;
		} else if ($c === "\\") {
			$parser->advance();
			$c = $parser->read();
			if ($c == null) return null;
			return new RegexEscapedCharacter($c);
		} else if (!$inCharacterClass && strpos("|?*+()[]", $c) !== false) {
			// Those characters are interpreted specially when we are outside of a character class
			// (TODO: there are others too, but we aren't allowing for them at the moment)
			return null;
		} else if ($c !== null) {
			$parser->advance();
			return new RegexCharacter($c);
		} else {
			return null;
		}
		// if ($parser->readString("^")) {
// 			return RegexStartAnchor::instance();
// 		} else if ($parser->readString("$")) {
// 			return RegexEndAnchor::instance();
// 		} else if ($parser->readString(".")) {
// 			return RegexAny::instance();
// 		} else if ($parser->readString("\s")) {
// 			return RegexWhitespace::instance();
// 		} else if ($parser->readString("\S")) {
// 			return RegexNonWhitespace::instance();
// 		} else if ($parser->readString("\d")) {
// 			return RegexDigit::instance();
// 		} else if ($parser->readString("\D")) {
// 			return RegexNonDigit::instance();
// 		} else if ($parser->readString("\w")) {
// 			return RegexWordCharacter::instance();
// 		} else if ($parser->readString("\W")) {
// 			return RegexNonWordCharacter::instance();
// 		}
		// TODO: handle \uFEFF kind and \xa0 kind
	}
}