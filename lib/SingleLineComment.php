<?php

require_once __DIR__ . "/SingleLineCommentToken.php";

class SingleLineComment {
	public function __construct ($comment) {
		$this->comment = $comment;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$token = $tokens->current();
		if ($token && $token instanceof SingleLineCommentToken) {
			debug("found single line comment ('" . substr($token->text, 0, 20) . "...')");
			$tokens->next();
			return new self($token->text);
		}
	}
	
}