<?php
/**
 * PHP version 7.2
 * src\scripts\eliminaCuestion.php
 */

use TDW\GCuest\Entity\Cuestion;

require 'inicio.php';

if ($argc !== 2) {
    $texto = <<< ____MOSTRAR_USO
    *> Empleo: {$argv[0]} <idCuestion>
    Elimina la cuestión indicada por <idCuestion>

____MOSTRAR_USO;
    die($texto);
}

try {
    $idCuestion = filter_var($argv[1], FILTER_VALIDATE_INT);
    $entityManager = \TDW\GCuest\Utils::getEntityManager();
    $cuestion = $entityManager
        ->find(Cuestion::class, $idCuestion);
    if (null === $cuestion) {
        die('Cuestión [' . $idCuestion . '] no existe.' .PHP_EOL);
    }
    $entityManager->remove($cuestion);
    $entityManager->flush();
} catch (\Exception $e) {
    exit('ERROR (' . $e->getCode() . '): ' . $e->getMessage());
}
