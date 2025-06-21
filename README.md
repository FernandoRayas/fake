# Fake App

**Integrante: Rayas Alvarado Juan Fernando**

**Integrante: Abraham Alejandro De La Hoya Angeles**

## Descripción del Proyecto

**Fake** es una aplicación web que incluye un módulo de chat como herramienta principal para la comunicación entre usuarios. Este chat permite a los usuarios interactuar en tiempo real, enviar mensajes de texto y recibir notificaciones instantáneas, facilitando una interacción fluida y eficiente dentro de la plataforma.

La aplicación está diseñada para gestionar información personal y permitir interacciones dentro de la plataforma de manera segura y eficiente. A través de un sistema de autenticación, validaciones y variables de entorno, Fake asegura que los datos estén protegidos y sea fácil de configurar para cada entorno de desarrollo.

## Requisitos

Para ejecutar la aplicación de manera correcta, es necesario configurar un archivo `.env` con tus propias credenciales y configuraciones. A continuación, se describen los pasos para preparar tu entorno de desarrollo:

### 1. Crear archivo `.env`
- Renombra el archivo `.env_template` a `.env`.
- Dentro de este archivo, encontrarás las variables de entorno que necesitas personalizar. Asegúrate de incluir tus credenciales de base de datos, como se muestra a continuación:

```env
SERVER_NAME=
USER_NAME=
PASSWORD=
DATABASE=
```

**Nota**: Cambia las credenciales con las de tu propia base de datos.

### 2. Dependencias
La aplicación no requiere dependencias externas adicionales. Sin embargo, asegúrate de tener configurado tu servidor local y entorno adecuado para ejecutar PHP y MySQL.

## Características

- **Autenticación segura**:  
  Implementación de un sistema de autenticación con verificación de usuario mediante correo electrónico y contraseña. Además, incluye un sistema CAPTCHA para garantizar que el usuario es humano antes de acceder.

- **Variables de entorno**:  
  Utiliza un archivo `.env` para gestionar de manera segura las credenciales y configuraciones. Esto permite a la aplicación ser fácilmente configurable sin necesidad de modificar el código fuente directamente.

- **Funcionalidad de inicio de sesión**:  
  El sistema verifica que las credenciales del usuario coincidan con los datos almacenados en la base de datos y redirige al usuario a un dashboard si la autenticación es exitosa.

## Instrucciones de Uso

1. **Configura tu entorno**:
   - Renombra el archivo `.env_template` a `.env`.
   - Abre el archivo `.env` y reemplaza las credenciales de la base de datos con las correctas.

2. **Configuración del servidor**:
   - Asegúrate de tener un servidor local en funcionamiento (por ejemplo, XAMPP, MAMP, etc.).
   - Configura tu base de datos utilizando las credenciales que colocaste en el archivo `.env`.

3. **Ejecuta el proyecto**:
   - Una vez configurado el archivo `.env` con las credenciales correctas, abre tu navegador y accede a la página de inicio de sesión.
   - Ingresa las credenciales de acceso y completa la verificación CAPTCHA para acceder a la plataforma.

## Funcionalidades Destacadas

- **Login con validación CAPTCHA**:  
  Asegura que el usuario que intenta acceder sea humano, evitando bots.

- **Autenticación con base de datos**:  
  Los usuarios pueden iniciar sesión proporcionando su correo electrónico y contraseña. Si las credenciales coinciden con las de la base de datos, se permite el acceso.

- **Uso de variables de entorno**:  
  Las credenciales y parámetros de configuración importantes están almacenados en el archivo `.env`, lo que permite una gestión segura y flexible de la configuración.

## Tecnologías Utilizadas

- **VUE.JS**: Para la lógica del frontend y la interfaz de usuario.
- **PHP**: Para la lógica del backend y la autenticación de usuarios.
- **MySQL**: Sistema de gestión de base de datos para almacenar la información de los usuarios.
- **HTML/CSS**: Para la estructura y diseño de la interfaz de usuario.
- **JavaScript**: Para la validación de formularios en el frontend.
- **Variables de entorno**: Para manejar configuraciones sensibles de manera segura.

## Notas Importantes

1. Asegúrate de que tu archivo `.env` esté correctamente configurado antes de ejecutar la aplicación.
2. **No subas el archivo `.env` a repositorios públicos**. Usa `.env_template` como referencia y crea tu propio archivo `.env` con las configuraciones correctas.

---

**¡Disfruta trabajando con Fake App y personalízala según tus necesidades!**
