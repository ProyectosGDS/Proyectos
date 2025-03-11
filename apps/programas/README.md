<p align="center"><a href="https://vuejs.org/guide/introduction.html" target="_blank"><img src="http://172.23.25.52/nvasquez/assets-wiki-projects/-/raw/main/img/logo-vue-pinia-tailwind-vite.png" width="300" alt="Vue 3 Logo"></a></p>

## App-DCAI (Vue 3, Pinia store, Tailwind Css y Vite ++ )
**Descripción**

Este es un proyecto base con un conjunto de librerías y componentes, que te ayudaran a la creación de aplicaciones web de forma facil y eficiente.

**Listado de librerias instaladas :**

- Vue 3
- Pinia
- Tailwindcss
- Tailwindcss-animated
- Vue Router
- Vite js
- Fortawesome
- Vue-datepicker
- Vue Use-core
- Apex charts
- Axios
  
**Generalidades del proyecto**

Al analizar el proyecto, te daras cuenta que esta relizando utilizando composition API de VUE3 como la utilizacion de estado usando PINIA

## INDICE
**Generalidades**

- [Estructura del proyecto](Estructura del proyecto)
- [Autentificacion](Autentificacion)
- [Gestion de peticiones](Gestion de peticiones)
- [Variables de entorno](Variables de entorno)

**Componetes**

- [BackTop](BackTop)
- [Button](Button)
- [Calendar](Calendar)
- [CardCollapse](CardCollapse)
- [DataTable](DataTable)
- [Input](Input)
- [LoadingBar](LoadingBar)
- [Modal](Modal)
- [ProgressBar](ProgressBar)
- [Spiner](Spiner)
- [Tabs](Tabs)
- [TimeLine](TimeLine)
- [TitlePage](TitlePage)
- [Toast](Toast)
- [ToolTip](ToolTip)
- [UserCard](UserCard)
- [UserPhoto](UserPhoto)
- [ValidateErrors](ValidateErrors)

<!-- ESTRUCTURA DEL PROYECTO -->

## Estructura del proyecto

[<- Regresar al home ](home)

Los componentes mas utilziados fueron importado de forma global para que puedan utilizarlo en cualquier vista y asi minimizar la cantidad de importaciones y de lineas de codigo.

La estructura del proyecto esta formada de la misma manera en la que se realiza una nueva instalacion de vue3, pero adicionalmente se le coloco una carpeta de layouts y components.

## Layouts

La renderizacion de la vista se debe a la estructura padre de la ruta Home que llama al componente Default y los Childrens se renderizan en ```<RouteView />```

**Default.vue**

```
    <script setup>
        import SideBar from './SideBar.vue'
        import Footer from './Footer.vue'
        import TitlePage from '../components/TitlePage.vue'
        import { useGlobalStore } from '../stores/global';

        const store = useGlobalStore()
    </script>

    <template>
        <toast v-if="store.toast.message.length > 0" :message="store.toast.message" />
        <div>
            <SideBar/>
            <TitlePage />
            <main class="px-2 md:px-5 lg:px-10 transition-[margin] duration-500 pt-20" :class=" store.openSidebar ? 'ml-52' : 'ml-20'">
                <RouterView />
            </main>
            <Footer />
        </div>
    </template>
```

Se importan los componentes esto con el fin de que se utilice una sola vez en el proyecto y sirva como plantilla.

- [SideBar](SideBar)
- [Footer](Footer)
- [TitlePage](TitlePage)
- [Toast](Toast)
- [GlobalStore](GlobalStore)

En el la etiqueta ```<main><main>``` vamos a  visualizar que ya tiene interaccion con el sidebar a travez de global store ya que dependiendo del estado de ```openSidebar``` el margen se aumentara o se diminuira esto para dar el efecto al **SideBar** de ser responsivo.

El componente **Toast** tiene esta misma logica, se puede utilizar desde cualquier parte del proyecto para mostrar un mensaje, interactuando con el **GlobalStore** para la actualizacion de los estados.


## Components

Dentro de esta carpeta podremos encontrar varios compostables lo cuales siguen la misma logica de pasarle parametros a travez de props. Dependiendo del componente se podra omitir la etiqueta de cierre esto con el fin de minizar el codigo y que este sea lo mas legible.

>NOTA: Se puede modificar los estilos basicos (color de fondo, color de texto, bordes, sombras, etc.. ) de los componentes segun las necesidades, y se puede realizar haciendo uso del atributo ```class=""``` de cada componente o bien modificando el codigo del componente en si.

> Ejemplo: ```<Button text="presione aquí" class=bnt-primary" />```

>**NO SE RECOMIENDA MODIFICAR EL CODIGO DEL COMPONENTE PARA QUE NO SE PIERDA EL DISEÑO Y LA FUNCIONABILIDAD**

**Componentes**

- [BackTop](BackTop)
- [Button](Button)
- [Calendar](Calendar)
- [CardCollapse](CardCollapse)
- [DataTable](DataTable)
- [Input](Input)
- [LoadingBar](LoadingBar)
- [Modal](Modal)
- [ProgressBar](ProgressBar)
- [Spiner](Spiner)
- [Tabs](Tabs)
- [TimeLine](TimeLine)
- [TitlePage](TitlePage)
- [Toast](Toast)
- [ToolTip](ToolTip)
- [UserCard](UserCard)
- [UserPhoto](UserPhoto)
- [ValidateErrors](ValidateErrors)



## Autentificación

[<- Regresar al home ](home)

El metodo para interactuar con la autentificacion y poder tener acceso a datos de la persona que inicio sesión, se explica de la siguiente manera:

Hay un store llamado **auth** en el cual se tiene los siguientes estado:
    
```
    const user = ref([])
    const roles = ref([])
    const permissions = ref([])
    const loadingPage = ref(false)
    const rolesApp = import.meta.env.VITE_MY_APPNAME
```

El **user** se utilza para almacenar los datos del usuairo autentificado y contiene informacion básica del mismo.

Para la comodidad de la persona que utilice el proyecto los roles y permisos se almacenaran por ceparado en los estados de **roles** y **permissions**, pero a que roles y permisos nos referimos, esto son los que tenga el usuario almacenado en el esquema de RRHH en la tabla de **rh_role y rh_permisos haciendo pivotes con las tablas rh_user_has_role y rh_role_has_permiso**.

El estado loadingPage  es un estado bandera para determinar en que momento la inicia y termina la peticion de logueo y asi poder utilizar el componente **loadingBar** y mostrar una barra de carga en la parte superior de las paginas.

Y el estado **roleApp**, este almacena el role y permisos de una aplicacion en especifica, ya que si una persona tiene diferentes roles y permisos en cada aplicacion realizada se pueda lograr distinguir los mismos a utilizar. este es llenado con una variable de entorno llamada **VITE_MY_APPNAME**, esta la puedes modificar en el archivo de variables.

Se realiza una peticion de tipo POST llamada login a la url de la api que se define en el archivo de variables de entorno

```
    function login() {
    loadingPage.value = true
    axios.post('login', {
      app: rolesApp
    })
      .then(response => {
        if (!response.data.error) {

          user.value = response.data
          roles.value = response.data.roles.map(role => role.nombre)
          permissions.value = response.data.roles.map(role => role.permissions.map(permission => permission.nombre)).flat(Infinity)
          permissions.value = Array.from(new Set(permissions.value))

          loadingPage.value = false

        } else {

          loadingPage.value = false

          console.error(response.data)
          router.push({ name: '401-Unauthorize' })
        }

      })
      .catch(err => {
        console.error(err);
      })

  }
```

## Gestion de peticiones

[<- Regresar al home ](home)

La funcion a utilizar necesita que se envie como parametro una variable llamada app que contendra el nombre de la aplicacion en mayusculas y separadas las palabras por un guion bajo.
Si el valor del parametro app no existe en las tablas, simplemente se devolvera los roles y permisos vacios.

Posterior a esto la funcion se encargara de manejar los errores dentro de una estado error para poder manejarlos y mostrarlos de la manera que quieras.

Tambien se tiene una funcion llamada ```checkPermission()``` como su nombre lo indica te permite saber si un permiso existe en el estado **permissions** y retornarte ya sea un ```true``` o un ```false```.


La gestion de peticiones, es realizada por la libreria axios instalada en el projecto y como mejora, este tiene incorporado para todas las peticiones la url que se definio en las variables de entorno ```axios.defaults.baseURL = import.meta.env.VITE_MY_URL_BASE``` con esta linea de codigo solo nos queda completar las rutas a donde queremos realizar las peticiones.
Ejemplo

>Antes: 
    >>```axios.get(import.meta.env.VITE_MY_URL_BASE + 'url')```

>Ahora:
    >>```axios.get('url')```

Como podemos darnos cuenta ahora se minifica la cantidad de codigo por peticion a realizar.

Esta configuracion la podemos visualizar en el archivo main.js del proyecto.


## Variables de entorno

[<- Regresar al home ](home)

Las variables de entorno son archivos donde se coloca llaves y valores, dependiendo del entorno en el que se encuentre **(development, production)**

Las llaves que se utilizan en el proyecto son la siguientes:

- VITE_MY_URL_API_BASE = 'http://172.23.25.80/GestionServicios/apis/dcai/public/api/'
- VITE_MY_APPNAME = 'MORA_ANTIGUA'
- VITE_MY_BASE = '/GestionServicios/apps/nelson/'

En la primera llave nos indica, la url base de la api a la cual queremos consumir.

En la segunda nos indica el nombre de la aplicacion con el formato de todo en mayuscula y palabras separadas por un guion bajo, y esta sirve para ir a traer el role y los permisos especificos de cada aplicacion, siempre y cuando existan en las tablas rh_role y rh_permiso del esquema RRHH.

Y la tercera nos indica, la url base donde se ejecutara el codigo esta linea sirve especificamente cuando se compila el proyecto y se pasa a produccion, para que el proyecto compilado sepa en que parte se ubicara dentro de la arquitectura de carpetas del servidor web.

## BackTop
[<- Regresar al home ](home)

Este componente es para colocar un boton que nos lleve al inicio de la pagina cuando esta sea lo sufientemente alta que necesite scroll, automaticamente se activa y esta renderizado una sola vez en la vista principal **App.vue**.


## Button

[<- Regresar al home ](home)

Este componente es un elemento ```<button></button>``` el cual ya tiene un CSS que se acopla al diseño del proyecto, cabe resaltar que tiene alguna mejoras como que acepta un icono y la modificacion de sus estilos base haciendo uso del atributo ``` class="shadow-red-800"``` y este esta diseñado de tal manera en que se utilice de la siguiente manera:

PROPS QUE ACEPTA:
- text: Sirve para colocar un texto en el boton
- icon: puedes pasarle el nombre del icono de fontAwesome
- loading: le puedes pasar un valor true o false y este te permitira desactivar el boton en el caso se este ejecutando un proceso, esto con el fin de evitar que el usuario final presione mas de una vez sobre el y ejecute el boton varias veces.

## Calendar

[<- Regresar al home ](home)

Este componente es un calendario que te permite pasar como prop un objeto llamado **events**, este se pintara en el dia indicado. y cuando presiones sobre la fecha indicada se levantara un modal mostrando los detalles de los eventos, este mismo utiliza la libreria vue-date-picker la cual tambien puedes hacer uso en cualquier parte del proyecto.

el objeto que se pase por prop tiene que tener las siguientes propiedades.

```
    const events = [
        {
            title : 'Evento 1', 
            description : 'Esta es la descripcion del evento que dice que carajos hacer 01', 
            time : { 
                start : '2023-09-07 06:30'
            }
        },
        {
            title : 'Evento 2', 
            description : 'Esta es la descripcion del evento que dice que carajos hacer 02', 
            time : { 
                start : '2023-09-07 06:30'
            }
        }
   ]
```


## DataTable

[<- Regresar al home ](home)


Este componente acepta dos props, **headers, data, color y loading** los headers tiene como objetivo indicarle al componente las cabeceras y acciones y estas interactuan con el arreglo u objeto que se le pasa al prop data.
La estructura de los headers es la siguiente: 

```
const headers = [
      {title:'nit',key:'nit', sort: true },
      {title:'empleado',key:'fullname'},
      {title:'usuario',key:'usuario'}
   ];
```

- La propiedad **title** sirve para mostrar el texto de encabezado en el datatable, por defecto todo texto sera cambiado a mayusculas.
- La propiedad **key** es el nombre del campo en el cual queremos mostrar, este acepta la notacion de anidamiento por medio del punto (propidad.sub-propieda.sub-propiedad2).
- La propiedad **sort** indica si se puede ordenar el campo, si este no aparece la propiedad no se tomara en cuenta para ser ordenable, acepta valores true o false.
- La propiedad **search** indica si el campo puede ser buscado o no, por defecto todos los campos seran buscables, acepta valores true o false, este campo se filtra por el valor pasado a la propiedad key.
- La propiedad **width** indica el tamaño de la columna.
- La propiedad **align** indica la direccion de justificacion del texto, a este se le puede pasar los valores: **left, right y center**

Otra de las bondades de este componente, es que se puede utilizar slots dentro del mismo cuando se quiera mostrar un campo personalizado, los slots seran los nombre que se indiquen en la propiedad **key**

``` 
<Data-Table :headers="headers" :data="data" color="bg-lime-muni text-lime-100" :loading="loading">

    <template #{'nombre que se coloco en la propieda key:'}>
        //mi codigo
    </template>

</Data-Table>
```


El prop **color** sirve para que el color del los titulos de las columnas, y el color del texto, como el color de fondo de la paginacion cambien de acuerdo con el valor que le pases.

El prop **loading** sirve para indicarle a la loadingBar del componente que inicie o se detenga, esto con el fin de mostar al usuario que hay un evento en proceso, en este caso la carga de los datos.

## Input

[<- Regresar al home ](home)

El componente Input se define como ```<Input/>``` esto lo podemos observar en la instanciacion del componente en el archivo de **main.js**.

Este componente tiene dos versiones, y es dependiendo de si definimos el propo **option**, este acepta como valor unico label y le indica al componente que el imput tendra un label como titulo, posterior mente se le tiene que pasar un prop **title** y se le escribe lo que se quiere mostrar en el input y tiene una interaccion al selecciona y escribir en el input. este titulo remplaza al atributo placeholder de un input normal.

La segunda version solo es un input normal donde acepta un prop **icon** para que aparezca del lado izquierdo por defecto.

hambas versiones aceptan un prop **error** que le permite interactuar con los bordes de los imputs y ponerlos de color rojo, esto es muy utilizado para las validaciones de los campos.

En hambos casos aceptan los atributos comunes como **type, name, min, max, pattern, etc...**


## Modal
[<- Regresar al home ](home)

El componente ```<Modal></Modal>``` es una ventana flotante que te permite utilizarla para mostrar detalles o pequeñas accines dentro del proyecto. Esta acepta como props **title,icon, footer y open**.

- El prop **title** sirve para colocar un titulo en el header del modal.
- El prop **icon** te permite mostrar un icon al lado izquierdo del titulo del modal.
- El prop **footer** te permite un slot en el modal para colocar los botones de accion del mismo.
- El prop **open** es el encargado de abrir o cerrar el modal acepta un valor de tipo booleano.

En cuanto al tamaño del modal este se va a decuando a la valor contenido en el.

## ValidateErrors
[<- Regresar al home ](home)

Aun no trabajo en esto 










