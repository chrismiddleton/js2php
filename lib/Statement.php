<?php

require_once __DIR__ . "/BreakStatement.php";
require_once __DIR__ . "/DefaultSwitchCase.php";
require_once __DIR__ . "/DoWhileLoop.php";
require_once __DIR__ . "/EmptyStatement.php";
require_once __DIR__ . "/ExpressionStatement.php";
require_once __DIR__ . "/ForLoop.php";
require_once __DIR__ . "/ForInLoop.php";
require_once __DIR__ . "/FunctionDeclaration.php";
require_once __DIR__ . "/IfStatement.php";
require_once __DIR__ . "/ReturnStatement.php";
require_once __DIR__ . "/SwitchCase.php";
require_once __DIR__ . "/SwitchStatement.php";
require_once __DIR__ . "/ThrowStatement.php";
require_once __DIR__ . "/TryStatement.php";
require_once __DIR__ . "/VarDefinitionStatement.php";
require_once __DIR__ . "/WhileLoop.php";

abstract class Statement {
	public static function fromJs (ArrayIterator $tokens) {
		$statement = EmptyStatement::fromJs($tokens) or
			$statement = VarDefinitionStatement::fromJs($tokens) or
			$statement = IfStatement::fromJs($tokens) or
			$statement = ReturnStatement::fromJs($tokens) or
			$statement = TryStatement::fromJs($tokens) or
			$statement = ThrowStatement::fromJs($tokens) or
			$statement = WhileLoop::fromJs($tokens) or
			$statement = DoWhileLoop::fromJs($tokens) or
			// for in loop first because the code in there allows for a 'for'
			// that is something else, but not vice versa
			$statement = ForInLoop::fromJs($tokens) or
			$statement = ForLoop::fromJs($tokens) or
			$statement = SwitchStatement::fromJs($tokens) or
			$statement = BreakStatement::fromJs($tokens) or
			$statement = SwitchCase::fromJs($tokens) or
			$statement = DefaultSwitchCase::fromJs($tokens) or
			$statement = FunctionDeclaration::fromJs($tokens) or
			$statement = ExpressionStatement::fromJs($tokens);
		// We parse these here so that we don't misinterpret them as identifier expression statements,
		// but they are not really statements so we return null.
		if ($statement instanceof SwitchCase || $statement instanceof DefaultSwitchCase) {
			return null;
		}
		return $statement;
	}
}