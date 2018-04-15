<?php

require_once __DIR__ . "/MultilineComment.php";
require_once __DIR__ . "/SingleLineComment.php";
require_once __DIR__ . "/Space.php";

class Comments {
	public function __construct ($comments) {
		$this->comments = $comments;
	}
	public static function fromJs ($tokens) {
		$comments = array();
		while ($tokens->valid()) {
			if (
				$comment = MultilineComment::fromJs($tokens) or
				$comment = SingleLineComment::fromJs($tokens) or
				$comment = Space::fromJs($tokens)
			) {
				$comments[] = $comment;
			} else {
				break;
			}
		}
		return count($comments) ? new self($comments) : null;
	}
}
