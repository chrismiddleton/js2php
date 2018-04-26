<?php

require_once __DIR__ . "/SpaceToken.php";

class Space {
	public function __construct ($space) {
		$this->space = $space;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		// TODO ? right now doing spacing automatically, but allowing newlines through since a multiline comment may or may not have an important space
		// TODO: other line endings here?
// 		return str_replace("\n", "\n$indents", preg_replace('/ \t/', '', $this->space));
		return "";
	}
}