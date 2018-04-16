<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/MultilineCommentToken.php";

class MultilineComment {
	public function __construct ($comment) {
		$this->comment = $comment;
	}
}