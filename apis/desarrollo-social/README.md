# API REST Desarrollo Social

Este proyecto está construido en **Laravel 10**. Para su correcto funcionamiento se requieren los siguientes elementos:

## Lenguaje

- **PHP 8.3 TS**

## Extensiones del lenguaje requeridas

- `ctype`
- `curl`
- `exif`
- `fileinfo`
- `gd`
- `hash`
- `intl`
- `mbstring`
- `oci8`
- `openssl`
- `pcre`
- `session`
- `tokenizer`
- `xml`
- `xsl`
- `zip`
- `dom`

Las extensiones tienen que ser de la misma version del lenguaje.

## Dependencias adicionales

- **Composer** v2.8.5 o superior: Para el manejo de paquetes.
- **Oracle Instant Client** v23: Para la conexión con la base de datos.

---

## Guía de preparación del proyecto

Para utilizar el proyecto en producción, se deben ejecutar los siguientes comandos:

### 1. Instalar dependencias
 
    composer install
Instala todas las librerias del archivo composer.json

    php artisan key:generate
Genear auna nueva clave con la que se encripta el proyecto y modifica la variable de entorno `APP_KEY`

    php artisan storage:link
Crear un enlace simbolico en la carpeta public hacia la carpeta storage/app

## Crear llaves privadas y publicas

Primero se crea un directorio llamado keys dentro del directorio storage y se ingresa a el, despues de ejecutan los siguientes comandos para crear las llaves dentro del directorio anteriomente mencionado.

    openssl genpkey -algorithm RSA -out private_key.pem -aes256 
Crear la clave privada y necesita contraseña para encriptarla

    openssl rsa -in private_key.pem -pubout -out public_key.pem
Extrae la clave publica de la privada

los nombres de las llaves son:
- `private_key.pem`
- `public_key.pem`

## Permisos de lectura y escritura

Al proyecto se le dan permisos de lectura y escritura a las carpetas 

- `bootstrap y subdirectorios`
- `storage/logs/`

## Variables de entorno
Se crear archivo un `.env` que puede ser una copia del archivo llamado .env.example que viene en el proyecto en el cual modificaremos las siguientes variables de entrono.

    APP_DEBUG=false
Esta varible colocarla en false para que no muestre los errores de forma detallada al momento de estar en produccion.

    APP_URL=https://{hostname}/{ubicacion-del-proyecto}/public
En esta variable se coloca el hostname y la ubicacion del proyecto dentro del servidor ejemplo: https://gds.muniguate.com/desarrollo-social/public, esta misma url servira a los clientes front-ends para saber a donde a puntar para recibir los datos del API.

    JWT_SECRET={palabra-secreta}
Para la validación de los token de sesion se requiere de la palabra secreta que es la misma con la que se creo la llave privada y publica.

    JWT_EXPIRED_TOKEN=480
Este es el timpo en el que se expira el token de forma automatica en este caso 480 minutos u 8 horas.

    PRINCIPAL_HOST_NAME_RECEIVER=https://{hostname}
El api restrigue los clientes front-ends que se conecten a menos que se encuentren en una lista dentro de las configuraciones del proyecto en el archivo jwt, lo que signfica que si el front-end esta en el mismo servidor que el api entonces se coloca el host del servidor ejemplo: https://gds.muniguate.com/, para esto se utiliza esta variable de entorno en el caso en que todos los clientes provengan del mismo hostname.













