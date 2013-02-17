<?php

require __DIR__ . '/../Autoloader.php';
require __DIR__ . '/../../../BNET_KEYS.inc';

$dr = new \chardev\tools\DataRetriever();

$names = require __DIR__ . "/res/names.inc";

$i = 16;
$dr->retrieveProfiles( array_slice( $names, $i * 100, ( $i + 1 ) * 100 - 1));