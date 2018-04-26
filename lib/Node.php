<?php

require_once __DIR__ . "/Comments.php";

abstract class Node {
	public function write (ProgramWriter $writer, $indents = "") {
		if (!empty($this->comments) && $this->comments instanceof Comments) {
			return $this->comments->write($writer, $indents);
		}
		return "";
	}
}