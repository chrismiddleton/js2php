<?php

require_once __DIR__ . "/MultilineComment.php";
require_once __DIR__ . "/SingleLineComment.php";
require_once __DIR__ . "/Space.php";

class Comments {
	public function __construct ($comments) {
		$this->comments = $comments;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		$code = "";
		foreach ($this->comments as $comment) {
			$code .= $comment->write($writer, $indents);
		}
		return $code;
	}
}
