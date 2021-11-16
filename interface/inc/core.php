<?php

session_start();

require_once __DIR__ . '/functions.php';

require_once __DIR__ . '/_classes/Database.php';
$DB = Database::getInstance();

require_once __DIR__ . '/_classes/DatabaseAbstraction.php';
DatabaseAbstraction::Init($DB);

_require_all(__DIR__ . '/_objects/');
_require_all(__DIR__ . '/_classes/');
