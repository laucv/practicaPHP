<?php
/**
 * PHP version 7.2
 * src\scripts\nuevoUsuario.php
 */

use TDW\GCuest\Entity\Usuario;

require 'inicio.php';

try {
    $num = random_int(0, 100000);
    $admin = (bool) ($num % 2);
    $nombre = 'user-' . $num;
    $entityManager = \TDW\GCuest\Utils::getEntityManager();
    $usuario = new Usuario($nombre, $nombre . '@example.com', $nombre, $admin, $admin);
    $entityManager->persist($usuario);
    $entityManager->flush();
    echo 'Creado usuario Id: ' . $usuario->getUsername() . PHP_EOL;
} catch (\Exception $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}
