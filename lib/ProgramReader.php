<?php

abstract class ProgramReader {
	abstract public function read (/* string */ $code, array $options = null);
	abstract public function readProgram (ArrayIterator $tokens);
}