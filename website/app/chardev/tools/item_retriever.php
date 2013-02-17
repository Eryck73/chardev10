<?php

require __DIR__ . '/../Autoloader.php';
require __DIR__ . '/../../../BNET_KEYS.inc';

$dr = new \chardev\tools\DataRetriever();
$dr->retrieveItems( $x, $y );