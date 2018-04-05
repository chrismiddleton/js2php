/**
 * @param {string} a
 * @param {string} b
**/
function foo (a, b) {
	var c = a.charAt(0);
	var x = 3.5;
	"blah blah blah\"blah";
	'blah blah bloo\\\'blah';
	if (c) {
		return true;
	} else if (b.slice(2, 4) === "fo") {
		return false;
	}
}