```markdown
# Fake App

## Descripción del Proyecto

**Fake** es una aplicación web que incluye un módulo de chat como herramienta principal para la comunicación entre usuarios. Este chat permite a los usuarios interactuar en tiempo real, enviar mensajes de texto y recibir notificaciones instantáneas, facilitando la interacción fluida y eficiente dentro de la plataforma.

Es un sistema web que tiene como objetivo principal permitir a los usuarios gestionar su información personal y realizar distintas interacciones con la plataforma de manera eficiente y segura. Esta app está construida con tecnologías modernas y es totalmente funcional para usuarios con roles específicos como clientes o administradores. 


## Requisitos

Para ejecutar la aplicación de manera correcta, necesitas configurar un archivo `.env` con tus propias credenciales de base de datos y otras configuraciones del sistema. A continuación, se describen los pasos para configurar tu entorno de desarrollo:

1. **Crear archivo `.env`**  
   - Tienes que renombrar el archivo `.env_template` a `.env`.
   - En este archivo encontrarás las variables de entorno que necesitas personalizar con tus credenciales de base de datos, como:

     ```env
     SERVER_NAME=
     USER_NAME=
     PASSWORD=
     DATABASE=
     ```

     **Nota:** Cambia las credenciales a las de tu propia base de datos.

2. **Dependencias**  
   La aplicación no requiere dependencias externas, pero es recomendable tener configurado tu servidor local y un entorno adecuado para PHP.

## Características

- **Autenticación segura**:  
  La aplicación implementa un sistema de autenticación con validación de usuario mediante correo electrónico y contraseña, y también incorpora un sistema de CAPTCHA para validar que el usuario es humano.

- **Variables de entorno**:  
  Se utiliza un archivo `.env` para gestionar de manera segura las credenciales y configuraciones, lo que permite a la aplicación ser fácilmente configurable sin necesidad de modificar directamente el código.

- **Funcionalidad de inicio de sesión**:  
  El sistema verifica que el correo y la contraseña coincidan con los datos almacenados en la base de datos y redirige al usuario a un dashboard si la autenticación es exitosa.

## Instrucciones de Uso

1. **Configura tu entorno**:
   - Renombra el archivo `.env_template` a `.env`.
   - Abre el archivo `.env` y reemplaza las credenciales de base de datos con las tuyas.
   
2. **Configuración del servidor**:
   - Asegúrate de tener un servidor local en funcionamiento (por ejemplo, usando XAMPP, MAMP, etc.).
   - Configura tu base de datos de acuerdo con las credenciales que hayas colocado en el archivo `.env`.

3. **Ejecuta el proyecto**:
   - Una vez que hayas configurado el archivo `.env` con las credenciales correctas, abre tu navegador y accede a la página de login.
   - Ingresa las credenciales de acceso y completa la verificación CAPTCHA para acceder a la plataforma.

## Funcionalidades Destacadas

- **Login con validación CAPTCHA**:  
  Asegúrate de que el usuario que ingresa sea humano antes de permitir el acceso.

- **Autenticación con base de datos**:  
  Los usuarios pueden iniciar sesión proporcionando su correo electrónico y contraseña. Si las credenciales coinciden con las almacenadas en la base de datos, se permite el acceso.

- **Uso de variables de entorno**:  
  Las credenciales de la base de datos y otros parámetros importantes están almacenados en el archivo `.env`, lo que permite que la configuración de la aplicación sea flexible y segura.

## Tecnologías Utilizadas

- **VUE.JS**: Para la lógica del frontend y para la UX.
- **PHP**: Para la lógica del backend y la autenticación de usuarios.
- **MySQL**: Como sistema de gestión de base de datos para almacenar la información de los usuarios.
- **HTML/CSS**: Para el diseño y maquetación del frontend.
- **JavaScript**: Para la validación del formulario en el frontend.
- **Variables de entorno**: Usadas para manejar configuraciones sensibles de manera segura.

## Notas Importantes

1. Asegúrate de que tu archivo `.env` esté correctamente configurado antes de ejecutar la aplicación.
2. El archivo `.env` **no debe ser subido a un repositorio público**. Utiliza `.env_template` como referencia y crea tu propio archivo `.env` con las configuraciones correctas.

---

**¡Disfruta trabajando con Fake App y personalízala según tus necesidades!**
