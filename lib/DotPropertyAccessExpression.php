<?php

require_once __DIR__ . "/Expression.php";

class DotPropertyAccessExpression extends Expression {
	public function __construct ($object, $property) {
		$this->object = $object;
		$this->property = $property;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeDotPropertyAccessExpression($this, $indents);
	}
}