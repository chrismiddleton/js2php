<?php

require_once __DIR__ . "/SpaceToken.php";

class Space {
	public function __construct ($space) {
		$this->space = $space;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		// TODO ? right now doing spacing automatically
// 		return $writer->writeSpace($this, $indents);
		return "";
	}
}