<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/MultilineCommentToken.php";
require_once __DIR__ . "/Node.php";

class MultilineComment extends Node {
	public function __construct ($comment) {
		$this->text = $comment;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return $writer->writeMultilineComment($this, $indents);
	}
}