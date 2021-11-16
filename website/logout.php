<?php

require_once __DIR__ . '/inc/core.php';
session_destroy();

redirect('/home.php');
exit;