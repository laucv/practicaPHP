<?php
/**
 * PHP version 7.2
 * tests/tests_bootstrap.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

mt_srand();

// Create/update tables in the test database
TDW\GCuest\Utils::updateSchema();
