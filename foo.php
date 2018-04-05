<?php
function foo ($a, $b) {
	$c = $a[0];
	$x = 3.5;
	"blah blah blah\"blah";
	'blah blah bloo\\\'blah';
	if ($c) {
		return true;
	} else if (substr(2, 4 - 2) === "fo") {
		return false;
	}
}