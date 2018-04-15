<?php

require_once __DIR__ . "/MultilineCommentToken.php";

class DocBlock {
	public function __construct () {}
	public static function fromJs (ArrayIterator $tokens) {
		$token = $tokens->current();
		if ($token && $token instanceof MultilineCommentToken && $token->text[0] === "*") {
			// TODO: parse comment
			$tokens->next();
			return new self();
		}
	}
}