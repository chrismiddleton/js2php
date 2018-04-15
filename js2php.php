<?php

error_reporting(E_ALL);

require_once __DIR__ . "/lib/jsFileToPhp.php";

echo jsFileToPhp($argv[1]);