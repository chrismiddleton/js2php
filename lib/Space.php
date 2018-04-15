<?php

require_once __DIR__ . "/SpaceToken.php";

class Space {
	public function __construct ($space) {
		$this->space = $space;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$token = $tokens->current();
		if ($token && $token instanceof SpaceToken) {
			$tokens->next();
			return new self($token->space);
		}
	}
}