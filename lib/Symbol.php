<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/SymbolToken.php";

class Symbol {
	public function __construct ($symbol) {
		$this->symbol = $symbol;
	}
	public static function fromJs ($tokens, $symbol = null) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof SymbolToken && ($symbol === null || $symbol === $token->symbol)) {
			debug("found symbol '{$token->symbol}'");
			$tokens->next();
			return new self($token->symbol);
		}
		$tokens->seek($start);
	}
	public function write (ProgramWriter $writer, $indents) {
		// Note: this only used as throwaway, so it's OK that there's no conversion here
		return $this->symbol;
	}
}