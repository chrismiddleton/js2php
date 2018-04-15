<?php

require_once __DIR__ . "/DoubleQuotedStringToken.php";
require_once __DIR__ . "/HexadecimalNumberToken.php";
require_once __DIR__ . "/JsIdentifierToken.php";
require_once __DIR__ . "/MultilineCommentToken.php";
require_once __DIR__ . "/NumberToken.php";
require_once __DIR__ . "/RegexToken.php";
require_once __DIR__ . "/SingleLineCommentToken.php";
require_once __DIR__ . "/SingleQuotedStringToken.php";
require_once __DIR__ . "/SpaceToken.php";
require_once __DIR__ . "/StringParser.php";
require_once __DIR__ . "/StringParserException.php";
require_once __DIR__ . "/SymbolToken.php";
require_once __DIR__ . "/Token.php";

class JsTokenizer {
	public function __construct () {}
	public static function tokenize ($js, $options) {
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
			">>>=", ">>>", ">>=", ">>", ">=", ">",
			"~=", "~",
			"^=", "^",
			"&=", "&&", "&",
			"|=", "||", "|",
			"?", ":",
			"(", ")", "{", "}", "[", "]",
			";", ",", ".", "\"", "'", "`"
		);
		$identifierSymbols = array("instanceof", "typeof", "await", "yield*", "yield", "in", "void", "delete", "new");
		$lastRealToken = null;
		while (!$parser->isDone()) {
			if (
				$token = MultilineCommentToken::parse($parser) or
				$token = SingleLineCommentToken::parse($parser) or
				$token = SpaceToken::parse($parser)
			) {
				;
			} else if (
				(
					!isset($lastRealToken) ||
					(
						!($lastRealToken instanceof DoubleQuotedStringToken) &&
						!($lastRealToken instanceof SingleQuotedStringToken) &&
						(
							!($lastRealToken instanceof JsIdentifierToken) ||
							in_array($lastRealToken->name, $identifierSymbols)
						) &&
						!($lastRealToken instanceof HexadecimalNumberToken) &&
						!($lastRealToken instanceof NumberToken) &&
						!($lastRealToken instanceof RegexToken)
					)
				) &&
				$token = RegexToken::parse($parser)
			) {
				$lastRealToken = $token;
			} else if (
				$token = DoubleQuotedStringToken::parse($parser) or
				$token = SingleQuotedStringToken::parse($parser) or
				$token = SymbolToken::parse($parser, $symbols) or
				$token = JsIdentifierToken::parse($parser) or
				$token = HexadecimalNumberToken::parse($parser) or
				$token = NumberToken::parse($parser)
			) {
				$lastRealToken = $token;
			}
			if ($token) {
				if (!empty($options['dumpTokens'])) var_dump($token);
				$tokens[] = $token;
				continue;
			}
			throw new StringParserException($parser, "Unexpected token " . substr($parser->str, $parser->i));
		}
		return $tokens;
	}
}