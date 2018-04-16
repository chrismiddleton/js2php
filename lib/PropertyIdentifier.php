<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Identifier.php";

class PropertyIdentifier extends Identifier {
	public static function fromJs (ArrayIterator $tokens) {
		$result = parent::fromJs($tokens);
		if (!$result) return null;
		debug("found property identifier {$result->name}");
		return new self($result->name);
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writePropertyIdentifier($this, $indents);
	}
}