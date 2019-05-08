<?php
/**
 * PHP version 7.2
 * src\scripts\create_user_admin.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use TDW\GCuest\Entity\Usuario;
use TDW\GCuest\Utils;

// Carga las variables de entorno
Utils::loadEnv(__DIR__ . '/../..');

// Crear usuario
$user = new Usuario();
$user->setUsername($_ENV['ADMIN_USER_NAME']);
$user->setEmail($_ENV['ADMIN_USER_EMAIL']);
$user->setPassword($_ENV['ADMIN_USER_PASSWD']);
$user->setEnabled(true);
$user->setAdmin(true);
$user->setMaestro(true);

try {
    $em = Utils::getEntityManager();
    $em->persist($user);
    $em->flush();
} catch (\Exception $e) {
    die('ERROR: ' . $e->getMessage());
}
