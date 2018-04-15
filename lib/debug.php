<?php

function debug ($msg) {
	$backtrace = debug_backtrace();
// 	echo str_repeat(" ", count($backtrace)) . $msg . "\n";
}