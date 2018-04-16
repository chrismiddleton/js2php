<?php

require_once __DIR__ . "/SingleLineCommentToken.php";

class SingleLineComment {
	public function __construct ($comment) {
		$this->comment = $comment;
	}
}