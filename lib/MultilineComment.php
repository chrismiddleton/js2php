<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/MultilineCommentToken.php";

class MultilineComment {
	public function __construct ($comment) {
		$this->comment = $comment;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$token = $tokens->current();
		if ($token && $token instanceof MultilineCommentToken) {
			debug("found multiline comment ('" . str_replace("\n", "\\n", substr($token->text, 0, 20)) . "...')");
			$tokens->next();
			return new self($token->text);
		}
	}
}