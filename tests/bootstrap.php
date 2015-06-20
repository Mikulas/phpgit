<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/dump.php';

Tester\Environment::setup();

ini_set('xdebug.max_nesting_level', 3000);
date_default_timezone_set('Europe/Prague');
