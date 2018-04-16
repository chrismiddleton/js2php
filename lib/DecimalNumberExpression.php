<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/JsIdentifierToken.php";
require_once __DIR__ . "/NumberToken.php";
require_once __DIR__ . "/SymbolToken.php";

class DecimalNumberExpression extends Expression {
	public function __construct ($pos, $int, $dec, $expPos, $exp) {
		$this->pos = $pos;
		$this->int = $int;
		$this->dec = $dec;
		$this->expPos = $expPos;
		$this->exp = $exp;
	}
	public static function fromJs (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		
		Comments::fromJs($tokens);
		
		$pos = true;
		$int = null;
		$dec = null;
		$expPos = true;
		$exp = null;
		
		$token = $tokens->current();
		if ($token && $token instanceof SymbolToken && $token->symbol === "-") {
			$pos = false;
			$tokens->next();
		} else if ($token && $token instanceof SymbolToken && $token->symbol === "+") {
			$tokens->next();
		}
		
		// read the int part
		$token = $tokens->current();
		if ($token && $token instanceof NumberToken) {
			$int = $token->text;
			$tokens->next();
		}
		
		$token = $tokens->current();
		if ($token && $token instanceof SymbolToken && $token->symbol === ".") {
			$tokens->next();
		} else if (!isset($int)) {
			// end of the number
			// we didn't find anything
			$tokens->seek($start);
			return;
		}
		
		// on to the decimal part
		$token = $tokens->current();
		if ($token && $token instanceof NumberToken) {
			$dec = $token->text;
			$tokens->next();
		} else if (!isset($int)) {
			// no int and no decimal part (even if we got a '.') is not a valid number
			$tokens->seek($start);
			return;
		}
			
		// on to the exponent part
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken && strtolower($token->text) === "e") {
			$tokens->next();
			$token = $tokens->current();
			if ($token && $token instanceof SymbolToken && $token->symbol === "-") {
				$expPos = false;
				$tokens->next();
			} else if ($token && $token instanceof SymbolToken && $token->symbol === "+") {
				$tokens->next();
			}
			$token = $tokens->current();
			if ($token && $token instanceof NumberToken) {
				$exp = $token->text;
				$tokens->next();
			}
		}
		$object = new self($pos, $int, $dec, $expPos, $exp);
		debug("found number " . $object->writeDefault(""));
		return $object;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeDecimalNumberExpression($this, $indents);
	}
	public function writeDefault ($indents) {
		$code = "";
		if (!$this->pos) $code .= "-";
		if (isset($this->int)) $code .= $this->int;
		if (isset($this->dec)) $code .= "." . $this->dec;
		if (isset($this->exp)) {			
			$code .= "e";
			if (!$this->expPos) $code .= "-";
			$code .= $this->exp;
		}
		return $code;
	}
}