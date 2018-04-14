<?php

function debug ($msg) {
	$backtrace = debug_backtrace();
	echo str_repeat(" ", count($backtrace)) . $msg . "\n";
}

class StringParser {
	public function __construct ($str) {
		$this->str = $str;
		$this->i = 0;
		$this->len = strlen($str);
		$this->lineNum = 1;
		$this->colNum = 1;
		$this->readCR = false;
	}
	public function advance () {
		if ($this->i < $this->len) {
			if ($this->readCR) {
				$this->readCR = false;
			}
			$c = $this->str[$this->i];
			$this->i++;
			if ($c === "\n" || $c === "\r") {
				$this->lineNum++;
				$this->colNum = 0;
				if ($c === "\r") $this->readCR = true;
			} else {
				$this->colNum++;
			}
		}
	}
	public function isDone () {
		return $this->i >= $this->len;
	}
	public function getColNum () {
		return $this->colNum;	
	}
	public function getLineNum () {
		return $this->lineNum;
	}
	public function peek () {
		return $this->str[$this->i];
	}
	public function pos () {
		return $this->i;
	}
	public function read () {
		if ($this->i >= $this->len) return null;
		$c = $this->str[$this->i++];
		if ($c === "\r") {
			$this->lineNum++;
			$this->colNum = 0;
			$this->readCR = true;
		} else if ($c === "\n") {
			if ($this->readCR) {
				$this->readCR = false;
			} else {
				$this->lineNum++;
				$this->colNum = 0;
			}
		} else if ($this->readCR) {
			$this->readCR = false;
		}
		return $c;
	}
	public function readEol () {
		$start = $this->i;
		$c = $this->read();
		$eol = null;
		if ($c === "\r") {
			$start = $this->i;
			$c = $this->read();
			if ($c === "\n") {
				$eol = "\r\n";
			} else {
				$this->i = $start;
				$eol = "\r";
			}
		} else if ($c === "\n") {
			$eol = "\n";
		} else {
			$this->i = $start;
		}
		return $eol;
	}
	public function readString ($str) {
		$start = $this->i;
		$i = 0;
		$len = strlen($str);
		if (!($len > 0)) return null;
		for ($i = 0; $i < $len; $i++, $this->i++) {
			if ($this->i >= $this->len || $this->str[$this->i] !== $str[$i]) {
				$this->i = $start;
				return null;
			}
		}
		$parts = preg_split('/\r\n|\r|\n/', $str);
		$numEols = count($parts) - 1;
		if ($numEols > 0 && $this->readCR) {
			$numEols -= 1;
		}
		if ($numEols > 0) {
			$this->linePos += $numEols;
			$this->charPos = 1 + strlen($parts[count($parts) - 1]);
		} else {
			$this->charPos += strlen($str);
		}
		if ($str[$len - 1] === "\r") {
			$this->readCR = true;
		} else {
			$this->readCR = false;
		}
		return $str;
	}
	public function seek ($pos) {
		$this->i = $pos;
	}
}

abstract class Token {}

class MultilineCommentToken extends Token {
	public function __construct ($text) {
		$this->text = $text;
	}
	public static function parse ($parser) {
		if (!$parser->readString("/*")) return;
		$text = "";
		while (!$parser->isDone()) {
			if ($parser->readString("*/")) break;
			$text .= $parser->read();
		}
		return new self($text);
	}
}

class SingleLineCommentToken extends Token {
	public function __construct ($text) {
		$this->text = $text;
	}
	public static function parse ($parser) {
		if (!$parser->readString("//")) return;
		$text = "";
		while (!$parser->isDone()) {
			if ($parser->readEol()) {
				break;
			}
			$text .= $parser->read();
		}
		return new self($text);
	}
}

class JsIdentifierToken extends Token {
	public function __construct ($name) {
		$this->name = $name;
	}
	public static function parse ($parser) {
		$c = $parser->peek();
		if ($c !== "_" && $c !== "$" && !ctype_alpha($c)) {
			return;
		}
		$name = "";
		while (!$parser->isDone()) {
			$start = $parser->pos();
			$c = $parser->peek();
			if ($c !== "_" && $c !== "$" && !ctype_alnum($c)) {
				break;
			}
			$name .= $c;
			$parser->advance();
		}
		return new self($name);
	}
}

class SymbolToken extends Token {
	public function __construct ($symbol) {
		$this->symbol = $symbol;
	}
	public static function parse ($parser, $symbols) {
		foreach ($symbols as $symbol) {
			$str = $parser->readString($symbol);
			if ($str) return new self($str);
		}
	}
}

class SpaceToken extends Token {
	public function __construct ($space) {
		$this->space = $space;
	}
	public static function parse ($parser) {
		$text = "";
		while (!$parser->isDone()) {
			$c = $parser->peek();
			if (!ctype_space($c)) break;
			$text .= $c;
			$parser->advance();
		}
		return strlen($text) ? new self($text) : null;
	}
}

class NumberToken extends Token {
	public function __construct ($text) {
		$this->text = $text;
	}
	public static function parse ($parser) {
		$number = "";
		while (!$parser->isDone()) {
			$c = $parser->peek();
			if (strpos("1234567890", $c) === false) break;
			$number .= $c;
			$parser->advance();
		}
		return strlen($number) ? new self($number) : null;
	}
}

class DoubleQuotedStringToken extends Token {
	public function __construct ($text) {
		$this->text = $text;
	}
	public static function parse ($parser) {
		$text = "";
		if (!$parser->readString('"')) return;
		while (!$parser->isDone()) {
			if ($parser->readString('"')) break;
			if ($parser->readString("\\")) {
				$nextChar = $parser->read();
				if (!strlen($nextChar)) throw new Exception("Expected character after '\\'");
				// (we don't interpret it here)
				$text .= "\\" . $nextChar;
			} else {
				$text .= $parser->read();
			}
		}
		return new self($text);
	}
}

class SingleQuotedStringToken extends Token {
	public function __construct ($text) {
		$this->text = $text;
	}
	public static function parse ($parser) {
		$text = "";
		if (!$parser->readString("'")) return;
		while (!$parser->isDone()) {
			if ($parser->readString("'")) break;
			if ($parser->readString("\\")) {
				$nextChar = $parser->read();
				if (!strlen($nextChar)) throw new Exception("Expected character after '\\'");
				// (we don't interpret it here)
				$text .= "\\" . $nextChar;
			} else {
				$text .= $parser->read();
			}
		}
		return new self($text);
	}
}

class RegexCharacter {
	public function __construct ($character) {
		$this->character = $character;
	}
}

// class RegexAnchor {
// 
// }
// 
// class RegexCharacterEscape {
// 
// }

abstract class RegexSimpleElement {
	public static function parse ($parser, $inCharacterClass = false) {
		// TODO: for now just parsing all as characters
		$c = $parser->peek();
		if ($c === "/") {
			// terminates the regex
			return null;
		} else if ($c === "\\") {
			$parser->advance();
			$c = $parser->read();
			if ($c == null) return null;
			return new RegexCharacter($c);
		} else if (!$inCharacterClass && strpos("|?*+", $c) !== false) {
			// Those characters are interpreted specially when we are outside of a character class
			// (TODO: there are others too, but we aren't allowing for them at the moment)
			return null;
		} else if ($c !== null) {
			$parser->advance();
			return new RegexCharacter($c);
		} else {
			return null;
		}
		// if ($parser->readString("^")) {
// 			return RegexStartAnchor::instance();
// 		} else if ($parser->readString("$")) {
// 			return RegexEndAnchor::instance();
// 		} else if ($parser->readString(".")) {
// 			return RegexAny::instance();
// 		} else if ($parser->readString("\s")) {
// 			return RegexWhitespace::instance();
// 		} else if ($parser->readString("\S")) {
// 			return RegexNonWhitespace::instance();
// 		} else if ($parser->readString("\d")) {
// 			return RegexDigit::instance();
// 		} else if ($parser->readString("\D")) {
// 			return RegexNonDigit::instance();
// 		} else if ($parser->readString("\w")) {
// 			return RegexWordCharacter::instance();
// 		} else if ($parser->readString("\W")) {
// 			return RegexNonWordCharacter::instance();
// 		}
		// TODO: handle \uFEFF kind and \xa0 kind
	}
}

class RegexCharacterClass {

	public function __construct ($negated, $elements) {
		$this->negated = $negated;
		$this->elements = $elements;
	}

	// TODO: handle ranges
	public static function parse ($parser) {
		$start = $parser->pos();
		$negated = false;
		if ($parser->readString("[")) {
			if ($parser->readString("^")) {
				$negated = true;
			}
			while (!$parser->isDone()) {
				$c = $parser->peek();
				if ($c === "]") {
					$foundEnd = true;
					$parser->advance();
					break;
				} else if ($c === "/") {
					// invalid
					break;
				} else if ($element = RegexSimpleElement::parse($parser, true)) {
					$elements[] = $element;
				}
			}
			if (!$foundEnd) {
				// backtrack
				$parser->seek($start);
				return null;
			}
			return new self($negated, $elements);
		} else if ($element = RegexSimpleElement::parse($parser)) {
			return $element;
		}
	
	}

}

class RegexQuantifiedElement {

	public function __construct ($element, $quantifier) {
		$this->element = $element;
		$this->quantifier = $quantifier;
	}

	public static function parse ($parser) {
		$start = $parser->pos();
		// TODO: handle groups as well
		if ($element = RegexCharacterClass::parse($parser)) {
			// let's see if we have a quantifier
			if ($parser->readString("+")) {
				return new self($element, "+");
			} else if ($parser->readString("?")) {
				return new self($element, "?");
			} else if ($parser->readString("*")) {
				return new self($element, "*");
			} else if ($parser->readString("{")) {
				$from = NumberToken::parse($parser);
				// TODO: for now, treating invalid regex as not regex
				if (!$from) {
					$parser->seek($start);
					return null;
				}
				$comma = false;
				if ($parser->readString(",")) {
					$comma = true;
					$to = NumberToken::parse($parser);
				}
				if (!$parser->readString("}")) {
					$parser->seek($start);
					return null;
				}
				return new self($element, "\{$from" . ($comma ? ",$to" : ""));
			} else {
				// just return the element we found
				return $element;
			}
			// TODO: handle all the other quantifier cases like *?, +?, etc.
		}
	}
	
}

class RegexSequence {

	public function __construct ($elements) {
		$this->elements = $elements;
	}
	
	public static function parse ($parser) {
		$elements = array();
		while (!$parser->isDone()) {
			if ($element = RegexQuantifiedElement::parse($parser)) {
				$elements[] = $element;
			} else {
				break;
			}
		}
		if (count($elements) > 1) {
			return new self($elements);
		} else if (count($elements) === 1) {
			return $elements[0];
		} else {
			return null;
		}
	}

}

class RegexEmptyElement {}

class RegexAlternation {

	public function __construct ($elements) {
		$this->elements = $elements;
	}
	
	public static function parse ($parser) {
		$elements = array();
		while (!$parser->isDone()) {
			if ($element = RegexSequence::parse($parser)) {
				$elements[] = $element;
			} else {
				// if we previously read a "|" but have no more text, then that can indicate an empty element
				// e.g. such as /a|/, which is allowed.
				if (count($elements)) {
					// empty element
					$elements[] = new RegexEmptyElement();
				}
			}
			if (!$parser->readString("|")) break;
		}
		if (!count($elements)) return null;
		// no alternations were found in this case
		if (count($elements) === 1) return $elements[0];
		return new self($elements);
	}

}

// TODO maybe?: it would really be better perhaps to tokenize the regular expression first,
// ending when we come upon an ending slash, which would prevent us from having to tell the lower regex elements
// about things like "|" and "?", etc
class RegexToken extends Token {
	public function __construct ($elements, $flags) {
		$this->elements = $elements;
		$this->flags = $flags;
	}
	// TODO: fix this to prevent it from detecting regex literals which are really division
	// by making a pipeline from the tokenizer to the parser and have the parser inform the tokenizer 
	// about what literals are valid at that point in the program, e.g. instead of an ArrayIterator,
	// we'll need a TokenizerIterator?
	public static function parse ($parser) {
		$elements = array();
		$start = $parser->pos();
		if (!$parser->readString("/")) return;
		$c = $parser->peek();
		// single line comment case
		if ($c === "/") {
			$parser->seek($start);
			return;
		}
		while (!$parser->isDone()) {
			if ($parser->readString("/")) {
				// done reading the regex
				break;
			} else if ($element = RegexAlternation::parse($parser)) {
				$elements[] = $element;
			} else {
				// we didn't see the end, but we failed to read anything, so let's assume we were wrong about this being a regex
				$elements = array();
				break;
			}
		}
		// TODO: failed to read a regex, so assume it must be division or something for now
		if (!count($elements)) {
			$parser->seek($start);
			return null;
		}
		// read any flags
		$flags = "";
		while (!$parser->isDone()) {
			$c = $parser->peek();
			if (!ctype_alpha($c)) break;
			$flags .= $c;
			$parser->advance();
		}
		return new self($elements, $flags);
	}
}

class JsTokenizer {
	public function __construct () {}
	public static function tokenize ($js) {
		$tokens = array();
		$parser = new StringParser($js);
		$symbols = array(
			"!==", "!=", "!",
			"===", "==", "=",
			"+=", "++", "+",
			"-=", "--", "-",
			"*=", "**", "*",
			"/=", "/",
			"%=", "%",
			"<<=", "<<", "<=", "<",
			">>=", ">>", ">", ">=",
			"~=", "~",
			"^=", "^",
			"&=", "&&", "&",
			"|=", "||", "|",
			"?", ":",
			"(", ")", "{", "}", "[", "]",
			";", ",", ".", "\"", "'", "`"
		);
		while (!$parser->isDone()) {
			if (
				$token = MultilineCommentToken::parse($parser) or
				$token = SingleLineCommentToken::parse($parser) or
				$token = RegexToken::parse($parser) or
				$token = DoubleQuotedStringToken::parse($parser) or
				$token = SingleQuotedStringToken::parse($parser) or
				$token = SpaceToken::parse($parser) or
				$token = SymbolToken::parse($parser, $symbols) or
				$token = JsIdentifierToken::parse($parser) or
				$token = NumberToken::parse($parser)
			) {
				$tokens[] = $token;
				continue;
			}
			throw new Exception("Unexpected token " . substr($parser->str, $parser->i));
		}
		return $tokens;
	}
}

class DocBlock {
	public function __construct () {}
	public static function fromJs (ArrayIterator $tokens) {
		$token = $tokens->current();
		if ($token && $token instanceof MultilineCommentToken && $token->text[0] === "*") {
			// TODO: parse comment
			$tokens->next();
			return new self();
		}
	}
}

class Space {
	public function __construct ($space) {
		$this->space = $space;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$token = $tokens->current();
		if ($token && $token instanceof SpaceToken) {
			$tokens->next();
			return new self($token->space);
		}
	}
}

class SingleLineComment {
	public function __construct ($comment) {
		$this->comment = $comment;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$token = $tokens->current();
		if ($token && $token instanceof SingleLineCommentToken) {
			debug("found single line comment ('" . substr($token->text, 0, 20) . "...')");
			$tokens->next();
			return new self($token->text);
		}
	}
	
}

class MultilineComment {
	public function __construct ($comment) {
		$this->comment = $comment;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$token = $tokens->current();
		if ($token && $token instanceof MultilineCommentToken) {
			debug("found multiline comment ('" . str_replace("\n", "\\n", substr($token->text, 0, 20)) . "...')");
			$tokens->next();
			return new self($token->text);
		}
	}
}

class Comments {
	public function __construct ($comments) {
		$this->comments = $comments;
	}
	public static function fromJs ($tokens) {
		$comments = array();
		while ($tokens->valid()) {
			if (
				$comment = MultilineComment::fromJs($tokens) or
				$comment = SingleLineComment::fromJs($tokens) or
				$comment = Space::fromJs($tokens)
			) {
				$comments[] = $comment;
			} else {
				break;
			}
		}
		return count($comments) ? new self($comments) : null;
	}
}

class Identifier {
	public function __construct ($name) {
		$this->name = $name;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$start = $tokens->key();
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			debug("found identifier '{$token->name}'");
			$tokens->next();
			return new self($token->name);
		}
		$token = $tokens->current();
	}
	public function toPhp ($indents) {
		return "$" . $this->name;
	}
}

class PropertyIdentifier extends Identifier {
	public static function fromJs (ArrayIterator $tokens) {
		$result = parent::fromJs($tokens);
		if (!$result) return null;
		debug("found property identifier {$result->name}");
		return new self($result->name);
	}
	public function toPhp ($indents) {
		// no "$"
		return $this->name;
	}
}

class Symbol {
	public function __construct ($symbol) {
		$this->symbol = $symbol;
	}
	public static function fromJs ($tokens, $symbol = null) {
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
	public function toPhp ($indents) {
		// Note: this only used as throwaway, so it's OK that there's no conversion here
		return $this->symbol;
	}
}

class Keyword {
	public function __construct ($name) {
		$this->name = $name;
	}
	public static function fromJs (ArrayIterator $tokens, $keyword = null) {
		$start = $tokens->key();
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken && ($keyword === null || $keyword === $token->name)) {
			debug("found keyword '{$token->name}'");
			$tokens->next();
			return new self($token->name);
		}
		$tokens->seek($start);
	}
	public function toPhp ($indents) {
		return $this->name;
	}
}

// TODO: better name for this
class VarDefinitionPiece {
	public function __construct (Identifier $name, Expression $val = null) {
		$this->name = $name;
		$this->val = $val;
	}
	public function toPhp ($indents) {
		return $this->name->toPhp($indents) . ($this->val ? (" = " . $this->val->toPhp($indents)) : "");
	}
}

class ParenthesizedExpression extends Expression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$start = $tokens->key();
		if (!Symbol::fromJs($tokens, "(")) {
			$tokens->seek($start);
			return null;
		}
		debug("found parenthesized expression start");
		$expression = Expression::fromJs($tokens);
		if (!$expression) {
			throw new Exception("Expected expression after '('");
		}
		if (!Symbol::fromJs($tokens, ")")) {
			throw new Exception("Expected ')' after expression");
		}
		return new self($expression);
	}
}

abstract class Expression {
	public function __construct () {}
	public static function fromJs (ArrayIterator $tokens) {
		return AssignmentExpression::fromJs($tokens);
	}
}

class IdentifierExpression extends Expression {
	public function __construct ($identifier) {
		$this->identifier = $identifier;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$identifier = Identifier::fromJs($tokens);
		if (!$identifier) return null;
		// TODO: better way to do this?
		if (in_array($identifier->name, array(
			"function"
		))) {
			return null;
		}
		debug("found identifier expression '{$identifier->name}'");
		return new self($identifier);
	}
	public function toPhp ($indents) {
		return $this->identifier->toPhp($indents);
	}
}

class BooleanExpression extends Expression {
	public function __construct ($val) {
		$this->val = $val;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$start = $tokens->key();
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			if ($token->name === "true") {
				$tokens->next();
				debug("found true");
				return new self(true);
			} else if ($token->name === "false") {
				$tokens->next();
				debug("found false");
				return new self(false);
			}
		}
		$tokens->seek($start);
	}
	public function toPhp ($indents) {
		return $this->val ? "true" : "false";
	}
}

class NullExpression extends Expression {
	public function __construct () {}
	public static function fromJs (ArrayIterator $tokens) {
		$start = $tokens->key();
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			if ($token->name === "null") {
				$tokens->next();
				debug("found null");
				return new self();
			}
		}
		$tokens->seek($start);
	}
	public function toPhp ($indents) {
		return "null";
	}
}

class UndefinedExpression extends Expression {
	public function __construct () {}
	public static function fromJs (ArrayIterator $tokens) {
		$start = $tokens->key();
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			if ($token->name === "undefined") {
				$tokens->next();
				debug("found undefined");
				return new self();
			}
		}
		$tokens->seek($start);
	}
	public function toPhp ($indents) {
		// TODO: handling of difference somehow?
		return "null";
	}
}

class DoubleQuotedStringExpression extends Expression {
	public function __construct ($text) {
		$this->text = $text;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$start = $tokens->key();
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if (!$token || !($token instanceof DoubleQuotedStringToken)) {
			$tokens->seek($start);
			return;
		}		
		debug("found string \"{$token->text}\"");
		$tokens->next();
		return new self($token->text);
	}
	public function toPhp ($indents) {
		// TODO: this needs to be fixed since JS and PHP have different quoting (and variable interpolation)
		return '"' . $this->text . '"';
	}
}

class SingleQuotedStringExpression extends Expression {
	public function __construct ($text) {
		$this->text = $text;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$start = $tokens->key();
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if (!$token || !($token instanceof SingleQuotedStringToken)) {
			$tokens->seek($start);
			return;
		}
		debug("found string '{$token->text}'");
		$tokens->next();
		return new self($token->text);
	}
	public function toPhp ($indents) {
		// TODO: this needs to be fixed since JS and PHP have different quoting
		return "'" . $this->text . "'";
	}
}

class DecimalNumberExpression extends Expression {
	public function __construct ($pos, $int, $dec, $expPos, $exp) {
		$this->pos = $pos;
		$this->int = $int;
		$this->dec = $dec;
		$this->expPos = $expPos;
		$this->exp = $exp;
	}
	public static function fromJs (ArrayIterator $tokens) {
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
		debug("found number " . $object->toPhp());
		return $object;
		
	}
	public function toPhp ($indents) {
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

class ArrayExpression extends Expression {
	public function __construct ($elements) {
		$this->elements = $elements;
	}
	public static function fromJs ($tokens) {
		debug("looking for array expression");
		if (!Symbol::fromJs($tokens, "[")) {
			return;
		}
		while ($tokens->valid()) {
			if (!($element = Expression::fromJs($tokens))) break;
			$elements[] = $element;
			if (!Symbol::fromJs($tokens, ",")) break;
		}
		if (!Symbol::fromJs($tokens, "]")) {
			throw new Exception("Expected ']' after array expression");
		}
		debug("found array expression");
		return new self($elements);
	}
	public function toPhp ($indents) {
		$elementStrs = array();
		foreach ($this->elements as $element) {
			$elementStrs[] = $element->toPhp($indents);
		}
		// TODO: array() vs []
		return "array(" . implode(", ", $elementStrs) . ")";
	}
}

class ObjectPair {
	public function __construct ($key, $val) {
		$this->key = $key;
		$this->val = $val;
	}
}

class ObjectExpression extends Expression {
	public function __construct ($pairs) {
		$this->pairs = $pairs;
	}
	public static function fromJs ($tokens) {
		debug("looking for object expression");
		$start = $tokens->key();
		if (!Symbol::fromJs($tokens, "{")) {
			debug("no '{' found");
			return;
		}
		$pairs = array();
		// TODO: support newer forms of objects
		while ($tokens->valid()) {
			if (!($key = PropertyIdentifier::fromJs($tokens))) {
				break;
			}
			// if we don't find a ':', assume we misparsed a block as an object
			if (!Symbol::fromJs($tokens, ":")) {
				$tokens->seek($start);
				return null;
			}
			if (!($val = Expression::fromJs($tokens))) {
				throw new Exception("Expected value after ':' in object");
			}
			$pairs[] = new ObjectPair($key, $val);
			if (!Symbol::fromJs($tokens, ",")) break;
		}
		if (!Symbol::fromJs($tokens, "}")) {
			throw new Exception("Expected closing '}' after object");
		}
		debug("found object expression");
		return new self($pairs);
	}
	public function toPhp ($indents) {
		$kvStrs = array();
		foreach ($this->pairs as $pair) {
			$kvStrs[] = var_export($pair->key, true) . " => " . $pair->val->toPhp($indents);
		}
		return "array(" . implode(", ", $kvStrs) . ")";
	}
}

abstract class SimpleExpression extends Expression {
	public static function fromJs (ArrayIterator $tokens) {
		$expression = ArrayExpression::fromJs($tokens) or
			$expression = ObjectExpression::fromJs($tokens) or
			$expression = BooleanExpression::fromJs($tokens) or
			$expression = NullExpression::fromJs($tokens) or
			$expression = UndefinedExpression::fromJs($tokens) or
			$expression = FunctionExpression::fromJs($tokens) or
			$expression = IdentifierExpression::fromJs($tokens) or
			$expression = DecimalNumberExpression::fromJs($tokens) or
// 			$expression = HexadecimalNumberExpression::fromJs($tokens) or
			$expression = DoubleQuotedStringExpression::fromJs($tokens) or
			$expression = SingleQuotedStringExpression::fromJs($tokens)
// 			or $expression = RegexLiteralExpression::fromJs($tokens)
		;
		return $expression;
	}
}

class NotExpression extends Expression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public static function fromJs (ArrayIterator $tokens) {
		if (!Symbol::fromJs($tokens, "!")) {
			return SimpleExpression::fromJs($tokens);
		}
		debug("found '!' expression");
		$expression = self::fromJs($tokens);
		if (!$expression) throw new Exception("Expected expression after '!'");
		return new self($expression);
	}
	public function toPhp ($indents) {
		return "!" . $this->expression->toPhp($indents);
	}
}

class BracketPropertyAccessExpression extends Expression {
	public function __construct ($object, $property) {
		$this->object = $object;
		$this->property = $property;
	}
	public function toPhp ($indents) {
		// TODO: this isn't quite right
		return $this->object->toPhp($indents) . "->" . $this->property->toPhp($indents);
	}
}

class DotPropertyAccessExpression extends Expression {
	public function __construct (Expression $object, PropertyIdentifier $property) {
		$this->object = $object;
		$this->property = $property;
	}
	public function toPhp ($indents) {
		return $this->object->toPhp($indents) . "->" . $this->property->toPhp($indents);
	}
}

class SubtractExpression extends Expression {
	public function __construct ($a, $b) {
		$this->a = $a;
		$this->b = $b;
	}
	// TODO: fromJs
	public function toPhp ($indents = "") {
		return $this->a->toPhp($indents) . " - " . $this->b->toPhp($indents);
	}
}

class IndexExpression extends Expression {
	public function __construct ($object, $index) {
		$this->object = $object;
		$this->index = $index;
	}
	// TODO: fromJs
	public function toPhp ($indents = "") {
		return $this->object->toPhp($indents) . "[" . $this->index->toPhp($indents) . "]";
	}
}

class FunctionIdentifier extends Identifier {
	public static function fromJs (ArrayIterator $tokens) {
		$result = parent::fromJs($tokens);
		if (!$result) return null;
		debug("found function identifier {$result->name}");
		return new self($result->name);
	}
	public function toPhp ($indents) {
		// no "$"
		return $this->name;
	}
}

abstract class FunctionCallLevelExpression extends Expression {
	public static function fromJs ($tokens) {
		debug("looking for function call level expression");
		$expression = ParenthesizedExpression::fromJs($tokens) or
			$expression = NotExpression::fromJs($tokens);
		if (!$expression) return;
		while ($tokens->valid()) {
			if (Symbol::fromJs($tokens, "(")) {
				debug("found function call");
				// parse the args
				$args = array();
				while ($tokens->valid()) {
					$token = $tokens->current();
					$arg = Expression::fromJs($tokens);
					if (!$arg) break;
					$args[] = $arg;
					if (!Symbol::fromJs($tokens, ",")) break;
				}
				if (!Symbol::fromJs($tokens, ")")) {
					throw new Exception("Expected ')' after function arguments");
				}
				$expression = new FunctionCallExpression("js", $expression, $args);
			} else if (Symbol::fromJs($tokens, ".")) {
				debug("found property access with '.'");
				// identifier expected
				$property = PropertyIdentifier::fromJs($tokens);
				$token = $tokens->current();
				$expression = new DotPropertyAccessExpression($expression, $property);
			} else if (Symbol::fromJs($tokens, "[")) {
				debug("found property access with '[]'");
				$property = Expression::fromJs($tokens);
				if (!Symbol::fromJs($tokens, "]")) {
					throw new Exception("Expected ']' after property expression");
				}
				$expression = new BracketPropertyAccessExpression($expression, $property);
			} else {
				return $expression;
			}
		}
	}
}

// (2 * 2)().b()
class FunctionCallExpression extends Expression {
	public function __construct ($source, $func, $params) {
		$this->source = $source;
		$this->func = $func;
		$this->params = $params;
	}
	public function toPhp ($indents) {
		$func = $this->func;
		$params = $this->params;
		// TODO: make this more solid
		if ($this->source === "js") {
			if ($func instanceof DotPropertyAccessExpression) {
				if ($func->property->name === "charAt") {
					$expression = new IndexExpression(
						$func->object,
						$params[0]
					);
					return $expression->toPhp();
				} else if ($func->property->name === "slice") {
					$expression = new FunctionCallExpression(
						"php",
						// TODO: should probably change this to instead be FunctionIdentifierExpression on the outside, if it works
						new IdentifierExpression(new FunctionIdentifier("substr")),
						// TODO: handle all the cases of different numbers of params correctly
						array(
							$params[0],
							new SubtractExpression(
								$params[1],
								$params[0]
							)
						)
					);
					return $expression->toPhp($indents);
				}
			}
		}
		$paramStrs = array();
		foreach ($params as $param) {
			$paramStrs[] = $param->toPhp($indents);
		}
		return $func->toPhp($indents) . "(" . implode(", ", $paramStrs) . ")";
	}
}

class TypeofExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public static function fromJs ($tokens) {
		$start = $tokens->key();
		$identifier = Identifier::fromJs($tokens);
		if (!$identifier || $identifier->name !== 'typeof') {
			$tokens->seek($start);
			return FunctionCallLevelExpression::fromJs($tokens);
		}
		debug("found typeof expression");
		$expression = TypeofExpression::fromJs($tokens);
		if (!$expression) throw new Exception("Expected expression after 'typeof'");
		return new self($expression);
	}
	public function toPhp ($indents) {
		// TODO: handle the different cases here
		return "gettype(" . $this->expression->toPhp($indents) . ")";
	}
}

function parseLeftAssociativeBinaryExpression ($class, $parseSubexpression, $symbols, $parseSymbol) {
	$start = $tokens->key();
	$a = $parseSubexpression($tokens);
	if (!$a) return;
	$symbols = self::get
	while ($tokens->valid()) {
		$symbolFound = null;
		foreach ($symbols as $symbol) {
			if ($parseSymbol($tokens, $symbol)) {
				$symbolFound = $symbol;
				break;
			}
		}
		if (!$symbolFound) break;
		debug("found '$symbolFound' expression");
		$b = $parseSubexpression($tokens);
		if (!$b) throw new Exception("Expected right-hand side after '$symbolFound'");
		$a = new $class($a, $symbolFound, $b);
	}
	return $a;
}

class ComparisonExpression extends Expression {
	public static function fromJs ($tokens) {
		return parseLeftAssociativeBinaryExpression(
			__CLASS__,
			array('InExpression', 'fromJs'),
			array('===', '!==', '==', '!='),
			array('Symbol', 'fromJs')
		);
	}
	public function toPhp ($indents) {
		return $this->a->toPhp($indents) . " {$this->symbol} " . $this->b->toPhp($indents);
	}
}

class EqualityExpression extends Expression {
	public function __construct ($a, $symbol, $b) {
		$this->a = $a;
		$this->symbol = $symbol;
		$this->b = $b;
	}
	public static function fromJs ($tokens) {
		$start = $tokens->key();
		$a = TypeofExpression::fromJs($tokens);
		if (!$a) return;
		$start = $tokens->key();
		$symbols = array("===", "!==", "==", "!=");
		$symbolFound = null;
		foreach ($symbols as $symbol) {
			if (Symbol::fromJs($tokens, $symbol)) {
				$symbolFound = $symbol;
				break;
			}
		}
		if (!$symbolFound) {
			$tokens->seek($start);
			return $a;
		}
		debug("found '$symbolFound' expression");
		$b = TypeofExpression::fromJs($tokens);
		if (!$b) throw new Exception("Expected right-hand side after '$symbolFound'");
		return new self ($a, $symbolFound, $b);
	}
	public function toPhp ($indents) {
		return $this->a->toPhp($indents) . " {$this->symbol} " . $this->b->toPhp($indents);
	}
}

class BooleanOperationExpression extends Expression {
	public function __construct ($a, $operator, $b) {
		$this->a = $a;
		$this->operator = $operator;
		$this->b = $b;
	}
	public static function fromJs ($tokens) {
		$start = $tokens->key();
		$a = EqualityExpression::fromJs($tokens);
		if (!$a) return;
		$start = $tokens->key();
		$operators = array("&&", "||");
		$operatorFound = null;
		foreach ($operators as $operator) {
			if (Symbol::fromJs($tokens, $operator)) {
				$operatorFound = $operator;
			}
		}
		if (!$operatorFound) {
			$tokens->seek($start);
			return $a;
		}
		debug("found '$operatorFound' expression");
		$b = EqualityExpression::fromJs($tokens);
		if (!$b) throw new Exception("Expected right-hand side after '&&'");
		return new self ($a, $operatorFound, $b);
	}
	public function toPhp ($indents) {
		return $this->a->toPhp($indents) . " {$this->operator} " . $this->b->toPhp($indents);
	}
}

class TernaryExpression extends Expression {
	public function __construct ($test, $yes, $no) {
		$this->test = $test;
		$this->yes = $yes;
		$this->no = $no;
	}
	public static function fromJs ($tokens) {
		$test = BooleanOperationExpression::fromJs($tokens);
		if (!$test) return;
		$afterTest = $tokens->key();
		if (!Symbol::fromJs($tokens, "?")) {
			$tokens->seek($afterTest);
			return $test;
		}
		debug("found ternary expression");
		if (!($yes = BooleanOperationExpression::fromJs($tokens))) {
			throw new Exception("Expected 'yes' value after start of ternary ('?')");
		}
		if (!Symbol::fromJs($tokens, ":")) {
			throw new Exception("Expected ':' after yes value in ternary");
		}
		if (!($no = BooleanOperationExpression::fromJs($tokens))) {
			throw new Exception("Expected 'no' value after ':' in ternary expression");
		}
		return new self($test, $yes, $no);
	}
	public function toPhp ($indents) {
		// parens due to php precedence difference
		return $this->test->toPhp($indents) . " ? (" . $this->yes->toPhp($indents) . ") : (" . $this->no->toPhp($indents) . ")";
	}
}

class AssignmentExpression extends Expression {
	public function __construct ($left, $symbol, $right) {
		$this->left = $left;
		$this->symbol = $symbol;
		$this->right = $right;
	}
	public static function fromJs ($tokens) {
		// TODO: verify that it's a valid LHS?
		$left = TernaryExpression::fromJs($tokens);
		if (!$left) return;
		$afterLeft = $tokens->key();
		$symbols = array("=", "+=", "-", "*=", "/=", "%=", "<<=", ">>=", "~=", "^=", "&=", "|=");
		foreach ($symbols as $symbol) {
			$symbolFound = Symbol::fromJs($tokens, $symbol);
			if ($symbolFound) break;
		}
		if (!$symbolFound) {
			$tokens->seek($afterLeft);
			return $left;
		}
		debug("found '{$symbolFound->symbol}' expression");
		$right = AssignmentExpression::fromJs($tokens);
		if (!$right) throw new Exception("Expected RHS of assignment");
		return new self($left, $symbol, $right);
	}
	public function toPhp ($indents) {
		return $left->toPhp($indents) . " {$this->symbol->symbol} " . $right->toPhp($indents);
	}
}

class SingleVarDeclaration {
	public function __construct ($declarator, $identifier) {
		$this->declarator = $declarator;
		$this->identifier = $identifier;
	}
	public static function fromJs ($tokens) {
		debug("looking for single var declaration");
		$declarator = null;
		if (Keyword::fromJs($tokens, "var")) {
			$declarator = "var";
		}
		if (!($identifier = Identifier::fromJs($tokens))) {
			if ($declarator) {
				throw new Exception("Expected identifier after '$declarator'");
			}
			return null;
		}
		debug("found single var declaration");
		return new self($declarator, $identifier);
	}
	public function toPhp ($indents) {
		return ($declarator ? ("$declarator ") : "") . $this->identifier->toPhp($indents);
	}
}

class VarDefinitionStatement {
	public function __construct ($pieces) {
		$this->pieces = $pieces;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$start = $tokens->key();
		if (!Keyword::fromJs($tokens, "var")) {
			$tokens->seek($start);
			return;
		}
		debug("found var declaration");
		// get the multiple expressions
		$pieces = array();
		while ($tokens->valid()) {
			// TODO: move some of this into VarDefinitionPiece?
			$name = Identifier::fromJs($tokens);
			if (!$name) break;
			$val = null;
			debug("found var name {$name->name}");
			if (Symbol::fromJs($tokens, "=")) {
				$val = Expression::fromJs($tokens);
			}
			$pieces[] = new VarDefinitionPiece($name, $val);
			if (!Symbol::fromJs($tokens, ",")) {
				debug("end of var declaration");
				break;
			}
		}
		// optionally, eat semicolon
		Symbol::fromJs($tokens, ";");
		return new self($pieces);
	}
	public function toPhp ($indents) {
		$codePieces = array();
		// Can't do multiple on the same line in PHP
		foreach ($this->pieces as $piece) {
			$codePieces []= $piece->toPhp($indents) . ";";
		}
		return implode("\n" . $indents, $codePieces) . "\n";
	}
}

class Block {
	public function __construct ($statements, $brace) {
		$this->statements = $statements;
		$this->brace = $brace;
	}
	public static function fromJs ($tokens) {
		debug("looking for block start");
		$brace = false;
		$temp = array_slice($tokens->getArrayCopy(), $tokens->key(), 10); var_dump($temp); // fdo
		if (Symbol::fromJs($tokens, "{")) {
			debug("found brace block start");
			$brace = true;
		}
		$statements = array();
		while ($tokens->valid()) {
			$statement = Statement::fromJs($tokens);
			if (!$statement) break;
			$statements[] = $statement;
			if (!$brace) break;
		}
		if ($brace) {
			if (!Symbol::fromJs($tokens, "}")) throw new Exception("Expected closing '}' after block");
		}
		return new self($statements, $brace);
	}
	public function toPhp ($indents) {
		if (!$this->brace) return $this->statements[0]->toPhp($indents);
		$code = "{\n";
		foreach ($this->statements as $statement) {
			$code .= $indents . "\t" . $statement->toPhp($indents . "\t");
		}
		$code .= $indents . "}\n";
		return $code;
	}
}

class ReturnStatement {
	public function __construct ($value) {
		$this->value = $value;
	}
	public static function fromJs ($tokens) {
		$start = $tokens->key();
		if (!Keyword::fromJs($tokens, "return")) {
			$tokens->seek($start);
			return;
		}
		debug("found return statement");
		// can be null, that's OK
		$value = Expression::fromJs($tokens);
		// optional semicolon
		Symbol::fromJs($tokens, ";");
		// TODO: handle cutting off early when newline (e.g. "return 5\n+6" should just return 5 in JS)
		return new self($value);
	}
	public function toPhp ($indents) {
		return "return " . $this->value->toPhp($indents) . ";\n";
	}
}

class IfStatement {
	public function __construct ($condition, $ifBlock, $elseBlock) {
		$this->condition = $condition;
		$this->ifBlock = $ifBlock;
		$this->elseBlock = $elseBlock;
	}
	public static function fromJs ($tokens) {
		$start = $tokens->key();
		if (!Keyword::fromJs($tokens, "if")) {
			$tokens->seek($start);
			return;
		}
		debug("found if statement");
		if (!Symbol::fromJs($tokens, "(")) {
			throw new Exception("Expected '(' after if");
		}
		$condition = Expression::fromJs($tokens);
		if (!Symbol::fromJs($tokens, ")")) {
			throw new Exception("Expected ')' after if condition");
		}
		$ifBlock = Block::fromJs($tokens);
		if (Keyword::fromJs($tokens, "else")) {
			debug("found else");
			$elseBlock = Block::fromJs($tokens);
		}
		return new self ($condition, $ifBlock, $elseBlock);
	}
	public function toPhp ($indents) {
		$code = "if (" . $this->condition->toPhp($indents) . ") ";
		$code .= $this->ifBlock->toPhp($indents);
		if ($this->elseBlock) {
			// remove final EOL - todo: better way to do this?
			$code = substr($code, 0, -1);
			$code .= " else " . $this->elseBlock->toPhp($indents);
		}
		return $code;
	}
}

class ThrowStatement {
	public function __construct ($value) {
		$this->value = $value;
	}
	public static function fromJs ($tokens) {
		$start = $tokens->key();
		if (!Keyword::fromJs($tokens, "throw")) {
			$tokens->seek($start);
			return;
		}
		debug("found throw statement");
		// can be null, that's OK
		$value = Expression::fromJs($tokens);
		// optional semicolon
		Symbol::fromJs($tokens, ";");
		// TODO: handle cutting off early when newline (e.g. "return 5\n+6" should just return 5 in JS)
		return new self($value);
	}
	public function toPhp ($indents) {
		return "throw " . $this->value->toPhp($indents) . ";\n";
	}
}

class ExpressionStatement {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public static function fromJs ($tokens) {
		debug("looking for expression statement");
		if (!($expression = Expression::fromJs($tokens))) return;
		debug("found expression statement");
		// TODO: make it either eat a semicolon or a newline
		// semicolon optional
		Symbol::fromJs($tokens, ";");
		return new self($expression);
	}
	public function toPhp ($indents) {
		return $this->expression->toPhp($indents) . ";\n";
	}
}

class ForLoop {
	public function __construct ($init, $test, $update, $body) {
		// statement
		$this->init = $init;
		// statement
		$this->test = $test;
		// expression or null
		$this->update = $update;
		// block
		$this->body = $body;
	}
	public static function fromJs ($tokens) {
		debug("looking for 'for' loop");
		if (!Keyword::fromJs($tokens, "for")) return;
		debug("found 'for' loop");
		if (!Symbol::fromJs($tokens, "(")) {
			throw new Exception("Expected '(' after 'for' keyword");
		}
		$init = VarDefinitionStatement::fromJs($tokens) or
			$init = ExpressionStatement::fromJs($tokens) or
			$init = EmptyStatement::fromJs($tokens);
		if (!$init) throw new Exception("Expected for loop initialization");
		$test = ExpressionStatement::fromJs($tokens) or
			$test = EmptyStatement::fromJs($tokens);
		if (!$test) throw new Exception("Expected for loop test");
		$update = Expression::fromJs($tokens);
		if (!Symbol::fromJs($tokens, ")")) {
			throw new Exception("Expected ')' after for loop header");
		}
		$body = Block::fromJs($tokens);
		if (!$body) throw new Exception("Expected for loop body");
		return new self($init, $test, $update, $body);
	}
	public function toPhp ($indents) {
		return "for (" .
			$this->init->toPhp($indents) .
			" " .
			$this->test->toPhp($indents) . 
			($this->update ? (" " . $this->update->toPhp($indents)) : "") . 
			") " . $this->body->toPhp($indents . "\t") . "\n";
		return $code;
	}
}

class EmptyStatement {
	private $instance = null;
	public static function fromJs ($tokens) {
		if (Symbol::fromJs($tokens, ";")) return self::instance();
	}
	public function instance () {
		if (!isset($this->instance)) $this->instance = new self();
		return $this->instance;
	}
	public function toPhp ($indents) {
		return ";";
	}
}


class WhileLoop {
	// TODO
}

class ForInLoop {
	public function __construct ($declaration, $object, $body) {
		$this->declaration = $declaration;
		$this->object = $object;
		$this->body = $body;
	}
	public static function fromJs ($tokens) {
		debug("looking for for...in loop");
		$start = $tokens->key();
		if (!Keyword::fromJs($tokens, "for")) return null;
		if (!Symbol::fromJs($tokens, "(")) {
			throw new Exception("Expected '(' after 'for' keyword");
		}
		if (!($declaration = SingleVarDeclaration::fromJs($tokens))) {
			$tokens->seek($start);
			return null;
		}
		if (!Keyword::fromJs($tokens, "in")) {
			$tokens->seek($start);
			return null;
		}
		debug("found for...in loop");
		if (!($object = Expression::fromJs($tokens))) {
			throw new Exception("Expected object after 'in' keyword");
		}
		if (!Symbol::fromJs($tokens, ")")) {
			throw new Exception("Expected ')' after for...in loop object");
		}
		if (!($block = Block::fromJs($tokens))) {
			throw new Exception("Expected block after for...in loop header");
		}
		return new self($declaration, $object, $body);
	}
	public function toPhp ($indents) {
		return "for (" . 
			$this->declaration->toPhp($indents) . 
			" in " . 
			$this->object->toPhp($indents) . 
			") " . 
			$this->body->toPhp($indents . "\t") . "\n";
	}
}

class ForOfLoop {
	// TODO
}

class DoWhileLoop {
	// TODO
}

abstract class Statement {
	public static function fromJs ($tokens) {
		$statement = EmptyStatement::fromJs($tokens) or
			$statement = VarDefinitionStatement::fromJs($tokens) or
			$statement = IfStatement::fromJs($tokens) or
			$statement = ReturnStatement::fromJs($tokens) or
			$statement = ThrowStatement::fromJs($tokens) or
			// for in loop first because the code in there allows for a 'for'
			// that is something else, but not vice versa
			$statement = ForInLoop::fromJs($tokens) or
			$statement = ForLoop::fromJs($tokens) or
			$statement = FunctionDeclaration::fromJs($tokens) or
			$statement = ExpressionStatement::fromJs($tokens);
		return $statement;
	}
}

class FunctionBody {
	public function __construct ($statements) {
		$this->statements = $statements;
	}
	public static function fromJs ($tokens) {
		debug("parsing function body");
		$statements = array();
		while ($tokens->valid()) {
			if (
				$statement = Statement::fromJs($tokens)
			) {
				$statements[] = $statement;
				continue;
			}
			break;
		}
		return new self($statements);
	}
	public function toPhp ($indents) {
		$code = "";
		foreach ($this->statements as $statement) {
			$code .= $indents . $statement->toPhp($indents);
		}
		return $code;
	}
}

class FunctionDeclaration {
	public function __construct ($name, $params, $body) {
		$this->name = $name;
		$this->params = $params;
		$this->body = $body;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$start = $tokens->key();
		// get last doc block
		while ($tokens->valid()) {
			if ($docBlock = DocBlock::fromJs($tokens)) continue;
			if (MultilineComment::fromJs($tokens)) continue;
			if (SingleLineComment::fromJs($tokens)) continue;
			if (Space::fromJs($tokens)) continue;
			break;
		}
		if (!Keyword::fromJs($tokens, "function")) {
			$tokens->seek($start);
			return;
		}
		debug("found function declaration start");
		$name = Identifier::fromJs($tokens);
		if (!$name) {
			$tokens->seek($start);
			return;
		}
		if (!Symbol::fromJs($tokens, "(")) {
			$tokens->seek($start);
			return;
		}
		// parse parameters
		$params = array();
		while ($tokens->valid()) {
			$param = Identifier::fromJs($tokens);
			$params[] = $param;
			debug("found param " . $param->name);
			if (!Symbol::fromJs($tokens, ",")) break;
		}
		if (!Symbol::fromJs($tokens, ")")) {
			throw new Exception("Expected closing ')' after function parameters");
		}
		if (!Symbol::fromJs($tokens, "{")) {
			throw new Exception("Expected opening '{' after function parameters");
		}
		$body = FunctionBody::fromJs($tokens);
		$token = $tokens->current();
		if (!Symbol::fromJs($tokens, "}")) {
			throw new Exception("Expected closing '}' after function body");
		}
		return new self($name, $params, $body);
	}
	public function toPhp ($indents = "") {
		$code = $indents . "function {$this->name->name} (";
		$paramStrs = array();
		foreach ($this->params as $param) {
			$paramStrs []= $param->toPhp($indents);
		}
		$code .= implode(", ", $paramStrs);
		$code .= ") {\n";
		$code .= $this->body->toPhp($indents . "\t");
		$code .= $indents . "}\n";
		return $code;
	}
}

// TODO: unify the FunctionExpression and FunctionDeclaration classes more since mostly duplicate code?
class FunctionExpression extends Expression {
	public function __construct ($name, $params, $body) {
		$this->name = $name;
		$this->params = $params;
		$this->body = $body;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$start = $tokens->key();
		// get last doc block
		while ($tokens->valid()) {
			if ($docBlock = DocBlock::fromJs($tokens)) continue;
			if (MultilineComment::fromJs($tokens)) continue;
			if (SingleLineComment::fromJs($tokens)) continue;
			if (Space::fromJs($tokens)) continue;
			break;
		}
		if (!Keyword::fromJs($tokens, "function")) {
			$tokens->seek($start);
			return;
		}
		debug("found function expression start");
		// name is optional
		$name = Identifier::fromJs($tokens);
		if (!Symbol::fromJs($tokens, "(")) {
			$tokens->seek($start);
			return;
		}
		// parse parameters
		$params = array();
		while ($tokens->valid()) {
			$params[] = Identifier::fromJs($tokens);
			if (!Symbol::fromJs($tokens, ",")) break;
		}
		if (!Symbol::fromJs($tokens, ")")) {
			throw new Exception("Expected closing ')' after function parameters");
		}
		if (!Symbol::fromJs($tokens, "{")) {
			throw new Exception("Expected opening '{' after function parameters");
		}
		$body = FunctionBody::fromJs($tokens);
		$token = $tokens->current();
		if (!Symbol::fromJs($tokens, "}")) {
			throw new Exception("Expected closing '}' after function body");
		}
		return new self($name, $params, $body);
	}
	public function toPhp ($indents = "") {
		$code = $indents . "function " . ($this->name ? "{$this->name->name} " : "") . "(";
		$paramStrs = array();
		foreach ($this->params as $param) {
			$paramStrs []= $param->toPhp($indents);
		}
		$code .= implode(", ", $paramStrs);
		$code .= ") {\n";
		$code .= $this->body->toPhp($indents . "\t");
		$code .= $indents . "}\n";
		return $code;
	}
}

class Program {
	public function __construct () {
		$this->children = array();
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for program");
		$program = new Program();
		while ($tokens->valid()) {
			try {
				if ($child = FunctionDeclaration::fromJs($tokens)) {
					$program->children[] = $child;
					continue;
				} else if ($child = Expression::fromJs($tokens)) {
					$program->children[] = $child;
				} else if (Comments::fromJs($tokens)) {
					;
				} else {
					$token = $tokens->current();
					throw new Exception("Unexpected token: " . var_export($token, true));
				}
			} catch (Exception $e) {
// 				var_dump($program); // fdo
// 				$array = array_slice($tokens->getArrayCopy(), $tokens->key(), 5); // fdo
// 				var_dump($array); // fdo
				throw $e;
			}
		}
		return $program;
	}
	public function toPhp ($indents = "") {
		$code = "<?php\n";
		foreach ($this->children as $child) {
			$code .= $child->toPhp($indents);
		}
		return $code;
	}
}

function jsToPhp ($js) {
	$tokens = JsTokenizer::tokenize($js);
	$program = Program::fromJs(new ArrayIterator($tokens));
	return $program->toPhp();
}

function jsFileToPhp ($file) {
	$js = file_get_contents($file);
	return jsToPhp($js);
}

echo jsFileToPhp("foo.js");