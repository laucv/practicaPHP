<?php
/**
 * PHP version 7.2
 * src\scripts\listadoUsuarios.php
 */

use TDW\GCuest\Entity\Usuario;

require 'inicio.php';

try {
    $entityManager = \TDW\GCuest\Utils::getEntityManager();
    $usuarios = $entityManager->getRepository(Usuario::class)->findAll();
    $entityManager->close();
} catch (\Doctrine\ORM\ORMException $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}

// Salida formato JSON
if (in_array('--json', $argv, false)) {
    echo json_encode($usuarios, JSON_PRETTY_PRINT);
    exit();
}

/** @var Usuario $usuario */
foreach ($usuarios as $usuario) {
    echo $usuario . PHP_EOL;
}
