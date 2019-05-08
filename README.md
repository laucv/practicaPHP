TDW-UPM: Usuarios y Cuestiones REST API
======================================

[![MIT license](http://img.shields.io/badge/license-MIT-brightgreen.svg)](http://opensource.org/licenses/MIT)
[![Minimum PHP Version](https://img.shields.io/badge/php-%5E7.2-blue.svg)](http://php.net/)
> Desarrollo de una API REST con el framework Slim3 para la gestión de usuarios y cuestiones.

Esta aplicación implementa una interfaz de programación [REST][rest] desarrollada como ejemplo de
utilización del framework [Slim 3][slim]. La aplicación proporciona las operaciones
habituales para la gestión de usuarios y cuestiones. Este proyecto
utiliza el Framework Slim 3, [JWT][jwt] (JSON Web Tokens), el _logger_ [Monolog][monolog] y el [ORM Doctrine][doctrine].

Adicionalmente -para hacer más sencilla la gestión de los datos- se ha utilizado
el ORM [Doctrine][doctrine]. Doctrine 2 es un Object-Relational Mapper que proporciona
persistencia transparente para objetos PHP. Utiliza el patrón [Data Mapper][dataMapper]
con el objetivo de obtener un desacoplamiento completo entre la lógica de negocio y la
persistencia de los datos en un SGBD.

Por otra parte, la especificación de la API (OpenAPI 3.0) se ha elaborado empleando anotaciones en el código y
el editor [Swagger][swagger]. Además se incluye la interfaz de usuario (SwaggerUI) de esta
fenomenal herramienta que permite realizar pruebas interactivas de manera completa y elegante.


## Instalación de la aplicación

Para realizar la instalación de la aplicación se debe crear una copia del fichero `./.env` y renombrarla
como `./.env.local`. A continuación se debe editar dicho fichero para asignar los siguientes parámetros:

* Configuración del acceso a la base de datos (base de datos, usuario y contraseña)
* Nombre y contraseña del usuario administrador del sistema
* Palabra secreta para la generación de tókens

Una vez editado el anterior fichero y desde el directorio raíz del proyecto se debe ejecutar el comando:
```
$ composer install
```
Para lanzar el servidor con la aplicación en desarrollo se debe ejecutar el comando: 
```
$ composer start
```
o bien:
```
$ php -S 127.0.0.1:8000 -t public
```
Y realizar una petición con el navegador a la dirección [http://localhost:8000/][lh]

## Estructura del proyecto:

A continuación se describe el contenido y estructura del proyecto:

* Directorio raíz del proyecto `.`:
    - `cli-config.php`: infraestructura de la interfaz de comandos del ORM Doctrine
    - `phpunit.xml` configuración de la suite de pruebas
* Directorio `bin`:
    - Ejecutables (*doctrine*, *phpunit* y *openapi*)
* Directorio `src`:
    - Contiene la configuración de la aplicación y las rutas que proporciona (`routes.php` y
    `routes_user.php`)
    - Subdirectorio `src/Entity`: entidades PHP (incluyen anotaciones de mapeo del ORM)
    - Clases auxiliares (`Error`, `Utils`, `Install` y `swagger_def.php`)
* Directorio `logs`:
    - Ficheros de log (guarda los dos últimos días)
* Directorio `public`:
    - `index.php` es el controlador frontal de la aplicación. Inicializa la aplicación
    Slim, incluye las rutas especificadas en `/src/routes.php` y lanza la aplicación.
    - Subdirectorio `api-docs`: cliente [Swagger][swagger] y especificación de la API.
* Directorio `vendor`:
    - Componentes desarrollados por terceros (Slim, Doctrine, JWT, Monolog, Dotenv, Swagger, etc.)
* Directorio `tests`:
    - Conjunto de scripts para la ejecución de test con PHPUnit.

## Ejecución de pruebas

La aplicación incorpora un conjunto de herramientas para la ejecución de pruebas 
unitarias y de integración con [PHPUnit][phpunit]. Empleando este conjunto de herramientas es posible
comprobar de manera automática el correcto funcionamiento de la API completa
sin la necesidad de herramientas adicionales.

Para configurar el entorno de pruebas se debe crear una copia del fichero `./phpunit.xml.dist`
y renombrarla como `./phpunit.xml`. A continuación se debe editar dicho fichero y modificar los
mismos parámetros que en la fase de instalación con los valores apropiados. Para lanzar la suite de pruebas se debe ejecutar:
```
$ composer test
```
o bien:
```
$ ./bin/phpunit [--testdox]
```

## Generación de la especificación OpenApi 3.0

El código fuente incluye las anotaciones necesarias para generar la especificación [OpenAPI 3.0][openapi] en formato [YAML][yaml].
Para generar dicha especificación se deberá ejecutar el comando:
```
$ ./bin/openapi ./src --output ./public/api-docs/models/openapi.yaml
```

Como resultado de la ejecución de este comando se generará el fichero `openapi.yaml` en el
subdirectorio `./public/api-docs/models`, que es el directorio donde buscará la especificación 
el cliente de Swagger.


[dataMapper]: http://martinfowler.com/eaaCatalog/dataMapper.html
[doctrine]: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/
[jwt]: https://jwt.io/
[lh]: http://localhost:8000/
[monolog]: https://github.com/Seldaek/monolog
[openapi]: https://www.openapis.org/
[phpunit]: http://phpunit.de/manual/current/en/index.html
[rest]: http://www.restapitutorial.com/
[slim]: https://www.slimframework.com/
[swagger]: http://swagger.io/
[yaml]: https://yaml.org/
