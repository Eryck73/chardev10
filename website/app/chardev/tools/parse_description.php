<?php

require_once __DIR__ . '/../Autoloader.php';

$parser = new chardev\tools\SpellDescriptionParser();

$parser->parseAll();