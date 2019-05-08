<?php
/**
 * PHP version 7.2
 * ./cli-config.php
 */

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use TDW\GCuest\Utils;

// Load env variables from .env + (.docker ||.local )
Utils::loadEnv(__DIR__);

$entityManager = Utils::getEntityManager();

return ConsoleRunner::createHelperSet($entityManager);
