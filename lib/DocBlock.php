<?php

require_once __DIR__ . "/MultilineCommentToken.php";

class DocBlock extends Node {
	public function __construct ($text) {
		// TODO: parse this
		$this->text = $text;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return $writer->writeDocBlock($this, $indents);
	}
}