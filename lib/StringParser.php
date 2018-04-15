<?php

class StringParser {
	public function __construct ($str) {
		$this->str = $str;
		$this->i = 0;
		$this->len = strlen($str);
		$this->lineNum = 1;
		$this->colNum = 1;
		$this->readCR = false;
	}
	public function advance () {
		if ($this->i < $this->len) {
			if ($this->readCR) {
				$this->readCR = false;
			}
			$c = $this->str[$this->i];
			$this->i++;
			if ($c === "\n" || $c === "\r") {
				$this->lineNum++;
				$this->colNum = 0;
				if ($c === "\r") $this->readCR = true;
			} else {
				$this->colNum++;
			}
		}
	}
	public function isDone () {
		return $this->i >= $this->len;
	}
	public function getColNum () {
		return $this->colNum;	
	}
	public function getLineNum () {
		return $this->lineNum;
	}
	public function peek () {
		return $this->str[$this->i];
	}
	public function pos () {
		return $this->i;
	}
	public function read () {
		if ($this->i >= $this->len) return null;
		$c = $this->str[$this->i++];
		if ($c === "\r") {
			$this->lineNum++;
			$this->colNum = 0;
			$this->readCR = true;
		} else if ($c === "\n") {
			if ($this->readCR) {
				$this->readCR = false;
			} else {
				$this->lineNum++;
				$this->colNum = 0;
			}
		} else if ($this->readCR) {
			$this->readCR = false;
		}
		return $c;
	}
	public function readEol () {
		$start = $this->i;
		$c = $this->read();
		$eol = null;
		if ($c === "\r") {
			$start = $this->i;
			$c = $this->read();
			if ($c === "\n") {
				$eol = "\r\n";
			} else {
				$this->i = $start;
				$eol = "\r";
			}
		} else if ($c === "\n") {
			$eol = "\n";
		} else {
			$this->i = $start;
		}
		return $eol;
	}
	public function readString ($str) {
		$start = $this->i;
		$i = 0;
		$len = strlen($str);
		if (!($len > 0)) return null;
		for ($i = 0; $i < $len; $i++, $this->i++) {
			if ($this->i >= $this->len || $this->str[$this->i] !== $str[$i]) {
				$this->i = $start;
				return null;
			}
		}
		$parts = preg_split('/\r\n|\r|\n/', $str);
		$numEols = count($parts) - 1;
		if ($numEols > 0 && $this->readCR) {
			$numEols -= 1;
		}
		if ($numEols > 0) {
			$this->lineNum += $numEols;
			$this->colNum = 1 + strlen($parts[count($parts) - 1]);
		} else {
			$this->colNum += strlen($str);
		}
		if ($str[$len - 1] === "\r") {
			$this->readCR = true;
		} else {
			$this->readCR = false;
		}
		return $str;
	}
	public function seek ($pos) {
		$this->i = $pos;
	}
}