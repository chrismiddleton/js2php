<?php

require_once __DIR__ . "/Program.php";

abstract class ProgramWriter {
	abstract public function write (Program $program);
	abstract public function writeAdditiveExpression (AdditiveExpression $expression, $indents);
	abstract public function writeArglessNewExpression (ArglessNewExpression $expression, $indents);
	abstract public function writeArrayExpression (ArrayExpression $expression, $indents);
	abstract public function writeAssignmentExpression (AssignmentExpression $expression, $indents);
	abstract public function writeAwaitExpression (AwaitExpression $expression, $indents);
	abstract public function writeBitwiseAndExpression (BitwiseAndExpression $expression, $indents);
	abstract public function writeBitwiseNotExpression (BitwiseNotExpression $expression, $indents);
	abstract public function writeBitwiseOrExpression (BitwiseOrExpression $expression, $indents);
	abstract public function writeBitwiseShiftExpression (BitwiseShiftExpression $expression, $indents);
	abstract public function writeBitwiseXorExpression (BitwiseXorExpression $expression, $indents);
	abstract public function writeBlock (Block $block, $indents);
	abstract public function writeBooleanExpression (BooleanExpression $expression, $indents);
	abstract public function writeBracketPropertyAccessExpression (BracketPropertyAccessExpression $expression, $indents);
	abstract public function writeBreakStatement (BreakStatement $statement, $indents);
	abstract public function writeCommaExpression (CommaExpression $commaExpression, $indents);
	abstract public function writeComparisonExpression (ComparisonExpression $expression, $indents);
	abstract public function writeDecimalNumberExpression (DecimalNumberExpression $expression, $indents);
	abstract public function writeDefaultSwitchCase (DefaultSwitchCase $switchCase, $indents);
	abstract public function writeDeleteExpression (DeleteExpression $expression, $indents);
	abstract public function writeDotPropertyAccessExpression (DotPropertyAccessExpression $expression, $indents);
	abstract public function writeDoubleQuotedStringExpression (DoubleQuotedStringExpression $expression, $indents);
	abstract public function writeDoWhileLoop (DoWhileLoop $loop, $indents);
	abstract public function writeExpressionStatement (ExpressionStatement $statement, $indents);
	abstract public function writeForInLoop (ForInLoop $loop, $indents);
	abstract public function writeFunctionCallExpression (FunctionCallExpression $expression, $indents);
	abstract public function writeFunctionDeclaration (FunctionDeclaration $declaration, $indents);
	abstract public function writeFunctionExpression (FunctionExpression $expression, $indents);
	abstract public function writeFunctionIdentifier (FunctionIdentifier $identifier, $indents);
	abstract public function writeIdentifier (Identifier $identifier, $indents);
	abstract public function writeIdentifierExpression (IdentifierExpression $expression, $indents);
	abstract public function writeIfStatement (IfStatement $statement, $indents);
	abstract public function writeIndexExpression (IndexExpression $expression, $indents);
	abstract public function writeLogicalAndExpression (LogicalAndExpression $expression, $indents);
	abstract public function writeLogicalOrExpression (LogicalOrExpression $expression, $indents);
	abstract public function writeNotExpression (NotExpression $expression, $indents);
	abstract public function writeNullExpression (NullExpression $expression, $indents);
	abstract public function writeObjectExpression (ObjectExpression $expression, $indents);
	abstract public function writePlusExpression (PlusExpression $expression, $indents);
	abstract public function writeProgram (Program $program, $indents);
	abstract public function writePropertyIdentifier (PropertyIdentifier $identifier, $indents);
	abstract public function writeRegexExpression (RegexExpression $expression, $indents);
	abstract public function writeSingleQuotedStringExpression (SingleQuotedStringExpression $expression, $indents);
	abstract public function writeSingleVarDeclaration (SingleVarDeclaration $declaration, $indents);
	abstract public function writeThrowStatement (ThrowStatement $statement, $indents);
	abstract public function writeTryStatement (TryStatement $statement, $indents);
	abstract public function writeTypeofExpression (TypeofExpression $expression, $indents);
	abstract public function writeUndefinedExpression (UndefinedExpression $expression, $indents);
	abstract public function writeVarDefinitionPiece (VarDefinitionPiece $piece, $indents);
	abstract public function writeVarDefinitionStatement (VarDefinitionStatement $statement, $indents);
	abstract public function writeVoidExpression (VoidExpression $expression, $indents);
	abstract public function writeWhileLoop (WhileLoop $loop, $indents);
	abstract public function writeYieldExpression (YieldExpression $expression, $indents);
}