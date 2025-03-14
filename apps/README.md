## CONJUNTO DE PROYECTOS QUE CONFORMAN LA PLATAFORMA DE DESARROLLO SOCIAL

Todos los proyectos estan echos usando tecnologias frameworks de front-end:

- `Vue 3`
- `Vue-Router`
- `Pinia-Store`
- `Tailwind Css`
- `Vite`

Y requiere para funcionar `node JS 23.0.0 y npm 11.1.0` instalado en el servidor, en el caso del npm ya viene por defecto en esta version de node pero la `version 10.1.0` solo se actualza a la version con el siguiente comando:

    npm install -g npm@11.1.0

### Nota:

Las instrucciones que a continuación se presenta se repite para cada proyecto iniciando por el proyecto llamado `plataforma-gds` ya que este es el proyecto principal o base, y posterior a esto sigue los demas proyectos
- `admin`
- `beneficiarios`
- `programas`


La `plataforma-gds` es un conjunto de proyectos de forma modular que pemirte el crear sub proyectos y que estos funciones como módulos de un solo proyecto principal. Una vez `empaquetados` los proyectos deberan tener la siguiente arquitectura de directorios:

<h2 id="arquitectura-directorios">Arquitectura de directorios de proyectos `empaquetados`</h2>
Esta es la forma en la que los proyectos empaquetados tanto el proyecto principal como los sub proyectos o módulos tiene que tener:

```
|- plataforma-gds (proyecto principal o base)
    |- apps
        |- admin
            |- assets
            |- docs
            |- img
            |- favicon.ico
            |- index.html
            |- .htaccess (archivo de manejo de rutas internas)

        |- beneficiarios
            |- assets
            |- docs
            |- img
            |- favicon.ico
            |- index.html
            |- .htaccess (archivo de manejo de rutas internas)

        |- programas
            |- assets
            |- docs
            |- img
            |- favicon.ico
            |- index.html
            |- .htaccess (archivo de manejo de rutas internas)

    |- assets
    |- docs
    |- img
    |- favicon.ico
    |- index.html
    |- .htaccess (archivo de manejo de rutas internas)
```
El proyecto `base o principal plataforma-gds` tiene una carpeta llamada apps donde almacena los diferentes sub proyectos empaquetados que conforman los módulos.

Los proyectos que conforman la plataforma-gds son:

    |- plataforma-gds //proyecto base
        |- apps
            |- admin // módulos
            |- programas // módulos
            |- beneficiarios // módulos

    |- participacion-ciudadana //este es otro proyecto base pero no tiene módulos

### Variables de entorno produccion ( .env.production )

En el archivo `.env.production`,  este archivo se almacenan las variables de entorno que se utilizan en cada proyecto.

    VITE_MY_APPNAME = 'PLATAFORMA-GDS'

La primera variable almacena el nombre del proyecto, usualmente escrita en mayuscula y para separar las palabras se utiliza el guion medio el valor de esta variable varia segun el proyecto o módulo.

    VITE_MY_API_URL_BASE = 'https://{hostname}/{proyecto-api}/public/api/'

Esta variable permite al proyecto o módulo saber hacia donde tienen que apuntar para obtener los datos del api aqui es muy importante que coloquemos la url donde esta el api y se le agregre al final `api/`. Este valor se repite en todos los proyectos o módulos ya que todos necesitan información del API

    VITE_MY_BASE = '/plataforma-gds/'

Esta variable le permite al proyecto saber la ubicación de si mismo dentro del servidor web. Y este valor varia dependiendo del proyecto ejemplo: /plataforma-gds/apps/eventos/ si se estra dentro del módulo de eventos.

    VITE_MY_URL = '/plataforma-gds/'

Y esta variable permite tener una url a donde queresmos que inicie el proyecto, esta misma url tiene que ser la misma tanto en el proyecto principal como en los proyectos módulos.

### Instalación de dependencias
Una vez configurado el proyecto de manera correcta procedemos a ejecutar el siguiente comando de terminal dentro del proyecto.

    npm install

Esto permite instalar todas las librerias contenidas en el archivo `package.json`

### Antes del empaquetado del proyecto

En el archivo `vite.config.js` en la propiedad `build` se agrega el valor `outDir` 

    build: {
        outDir: 'C:/laragon/www/{nombre del proyecto principal}/apps/eventos',
    },

y este el valor que es un path donde se quiere que el proyecto sea empaquetado. para efectos practicos y de colocarlos en produccion, esto puede ser comentariado o eliminado y que el proyecto utilice el path por defecto para empaquetarse que es dentro del mismo proyecto en la carpeta `dist` la cual puede ser movida hacia donde funcionara los proyectos o módulos y renombrada con el nombre del proyecto o módulo. Hay que recordar si es el proyecto base este ira en la raiz del servidor web y si es un módulo este ira dentro de del proyecto base en una carpeta llamada apps y dentro iran los proyectos empaquetados que funcionan como módulos. (ver arquitectura de directorios)

    base : '/plataforma-gds/apps/eventos'

Esta propiedad el permite crearle una url al proyecto de forma interna 
al momento de empaquetarlo por lo cual obedece a la misma para funcionar. En el caso de ser un proyecto principal esta quedaria como  `/plataforma-gds`. en el caso de ser un proyecto módulo quedaria de la siguiente manera `/plataforma-gds/apps/{nombre del módulo}`

### Empaquetado

Una vez instalada todas las libreria procedemos a empaquedarlo con el comando:

    npm run build

Para que en cada proyecto las rutas internas funcionen una vez empaquetados se debe crear una copia de los archivos .htaccess que estaran disponibles dentro de cada proyecto sin empaquetar hacia la raiz del proyecto empaquetado, [( ver arquitectura de directorios. )](#arquitectura-directorios)

En el caso de que el archivo se quiera crear de forma manual estos son los parametros de los archivos.

```
<IfModule mod_negotiation.c>
  Options -MultiViews
</IfModule>
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /plataforma-gds/
  RewriteRule ^index\.html$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /plataforma-gds/index.html [L]
</IfModule>

```

### Archivo .htaccess
Cada proyecto o módulo de forma individual debe contener un archivo `.htaccess` que permite el manejo de a las rutas interanas de cada proyecto o módulo. Las dos lineas que se modifican dependiendo de la localizacion del proyecto dentro del servidor.

    RewriteBase /plataforma-gds/
    RewriteRule . /plataforma-gds/index.html [L]

En este ejemplo podemos ver que el proyecto esta en la carpeta raiz ( / ) del servidor web si se utiliza `apache` usualmente en ***/var/www/html/*** donde este directorio representa el directorio raiz del servicio web. en el caso del proyecto principal o base es `/plataforma-gds/` y en el caso de los sub proyectos que fungen como módulos es : `/plataforma-gds/apps/{nombre del módulo}/`






