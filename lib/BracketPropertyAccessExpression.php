<?php

require_once __DIR__ . "/Expression.php";

class BracketPropertyAccessExpression extends Expression {
	public function __construct ($object, $property) {
		$this->object = $object;
		$this->property = $property;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeBracketPropertyAccessExpression($this, $indents);
	}
}