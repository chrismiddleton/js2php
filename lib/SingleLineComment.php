<?php

require_once __DIR__ . "/SingleLineCommentToken.php";

class SingleLineComment extends Node {
	public function __construct ($comment) {
		$this->text = $comment;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return $writer->writeSingleLineComment($this, $indents);
	}
}