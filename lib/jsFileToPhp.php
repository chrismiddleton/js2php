<?php

require_once __DIR__ . "/jsToPhp.php";

function jsFileToPhp ($file, $options = null) {
	$js = file_get_contents($file);
	return jsToPhp($js, $options);
}