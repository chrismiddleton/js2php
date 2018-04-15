<?php

class TokenException extends Exception {
	public function __construct ($tokens, $message) {
		$token = $tokens->current();
		if ($token) {
			$message .= " on line {$token->lineNum}, col {$token->colNum}, got " . 
				get_class($token) . " ($token) instead";
		} else {
			$message .= ", got end of file instead";
		}
		$this->message = $message;
	}
}