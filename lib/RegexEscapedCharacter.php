<?php

class RegexEscapedCharacter {
	public function __construct ($character) {
		$this->character = $character;
	}
	public function __toString () {
		return "\\" . $this->character;
	}
}