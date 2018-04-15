<?php

abstract class Token {
	public function __construct ($parser) {
		$this->lineNum = $parser->getLineNum();
		$this->colNum = $parser->getColNum();
	}
}