<?php

abstract class ProgramReader {
	abstract public function read (/* string */ $code, array $options = null);
	abstract public function readAdditiveExpression (ArrayIterator $tokens);
	abstract public function readArglessNewExpression (ArrayIterator $tokens);
	abstract public function readArrayExpression (ArrayIterator $tokens);
	abstract public function readAssignmentExpression (ArrayIterator $tokens);
	abstract public function readBitwiseAndExpression (ArrayIterator $tokens);
	abstract public function readBitwiseOrExpression (ArrayIterator $tokens);
	abstract public function readBitwiseShiftExpression (ArrayIterator $tokens);
	abstract public function readBitwiseXorExpression (ArrayIterator $tokens);
	abstract public function readBlock (ArrayIterator $tokens);
	abstract public function readBooleanExpression (ArrayIterator $tokens);
	abstract public function readBreakStatement (ArrayIterator $tokens);
	abstract public function readCommaExpression (ArrayIterator $tokens);
	abstract public function readComments (ArrayIterator $tokens);
	abstract public function readComparisonExpression (ArrayIterator $tokens);
	abstract public function readDecimalNumberExpression (ArrayIterator $tokens);
	abstract public function readDefaultSwitchCase (ArrayIterator $tokens);
	abstract public function readDocBlock (ArrayIterator $tokens);
	abstract public function readDoubleQuotedStringExpression (ArrayIterator $tokens);
	abstract public function readDoWhileLoop (ArrayIterator $tokens);
	abstract public function readEmptyStatement (ArrayIterator $tokens);
	abstract public function readEqualityExpression (ArrayIterator $tokens);
	abstract public function readExpression (ArrayIterator $tokens);
	abstract public function readExpressionStatement (ArrayIterator $tokens);
	abstract public function readForLoop (ArrayIterator $tokens);
	abstract public function readForInLoop (ArrayIterator $tokens);
	abstract public function readFunctionBody (ArrayIterator $tokens);
	abstract public function readFunctionCallLevelExpression (ArrayIterator $tokens);
	abstract public function readFunctionDeclaration (ArrayIterator $tokens);
	abstract public function readFunctionExpression (ArrayIterator $tokens);
	abstract public function readFunctionIdentifier (ArrayIterator $tokens);
	abstract public function readHexadecimalNumberExpression (ArrayIterator $tokens);
	abstract public function readIdentifier (ArrayIterator $tokens);
	abstract public function readIdentifierExpression (ArrayIterator $tokens);
	abstract public function readIfStatement (ArrayIterator $tokens);
	abstract public function readKeyword (ArrayIterator $tokens, $keyword);
	abstract public function readLogicalAndExpression (ArrayIterator $tokens);
	abstract public function readLogicalOrExpression (ArrayIterator $tokens);
	abstract public function readMultilineComment (ArrayIterator $tokens);
	abstract public function readMultiplicativeExpression (ArrayIterator $tokens);
	abstract public function readNotLevelExpression (ArrayIterator $tokens);
	abstract public function readNullExpression (ArrayIterator $tokens);
	abstract public function readObjectExpression (ArrayIterator $tokens);
	abstract public function readParenthesizedExpression (ArrayIterator $tokens);
	abstract public function readPostfixIncrementLevelExpression (ArrayIterator $tokens);
	abstract public function readProgram (ArrayIterator $tokens);
	abstract public function readPropertyIdentifier (ArrayIterator $tokens);
	abstract public function readReturnStatement (ArrayIterator $tokens);
	abstract public function readRegexExpression (ArrayIterator $tokens);
	// TODO: rename to ValueExpression?
	abstract public function readSimpleExpression (ArrayIterator $tokens);
	abstract public function readSingleLineComment (ArrayIterator $tokens);
	abstract public function readSingleQuotedStringExpression (ArrayIterator $tokens);
	abstract public function readSingleVarDeclaration (ArrayIterator $tokens);
	abstract public function readSpace (ArrayIterator $tokens);
	abstract public function readStatement (ArrayIterator $tokens);
	abstract public function readSwitchCase (ArrayIterator $tokens);
	abstract public function readSwitchStatement (ArrayIterator $tokens);
	abstract public function readSymbol (ArrayIterator $tokens, $symbol);
	abstract public function readTernaryExpression (ArrayIterator $tokens);
	abstract public function readThrowStatement (ArrayIterator $tokens);
	abstract public function readTryStatement (ArrayIterator $tokens);
	abstract public function readUndefinedExpression (ArrayIterator $tokens);
	abstract public function readVarDefinitionStatement (ArrayIterator $tokens);
	abstract public function readWhileLoop (ArrayIterator $tokens);
	abstract public function readYieldExpression (ArrayIterator $tokens);
}