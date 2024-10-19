# 📄 Formulario Shortcode Mejorado


<img src="./assets/images/logo_wordpress_shortcode.png" alt="Logo del Plugin" width="50%">

**Formulario Shortcode Mejorado** es un potente plugin de WordPress que permite insertar formularios personalizados mediante un shortcode. Ofrece características avanzadas como seguridad nonce, envío de correos electrónicos vía SMTP, integración con Google reCAPTCHA v2, validación frontend, limitación de frecuencia de envíos y gestión dinámica de campos para mejorar la experiencia del usuario.

## 🌟 Características

- **Shortcode Fácil de Usar:** Inserta el formulario en cualquier página o entrada utilizando `[mpp_formulario]`.
- **Seguridad Robusta:** Implementa nonces para proteger contra ataques CSRF.
- **Envío de Correos Electrónicos Confiable:** Configura WP Mail SMTP para asegurar la entrega de correos.
- **Protección contra Spam:** Integra Google reCAPTCHA v2 y campos Honeypot para verificar que los usuarios son humanos.
- **Validación Frontend:** Validaciones HTML5 y JavaScript para mejorar la experiencia del usuario.
- **Almacenamiento de Datos:** Guarda las entradas del formulario en la base de datos de WordPress para futuras referencias.
- **Limitación de Frecuencia de Envío:** Evita envíos repetidos en un corto período mediante transients y filtros personalizables.
- **Gestión Dinámica de Campos:** Añade, edita o elimina campos personalizados desde el panel de administración.
- **Personalización Fácil:** Configura las claves de reCAPTCHA y otros ajustes desde el panel de administración.

## 🚀 Instalación

### Método 1: Instalar desde el Panel de Administración de WordPress

1. **Descargar el Plugin:**
   - Haz clic en el botón **"Code"** y luego en **"Download ZIP"** en la página del repositorio.

2. **Instalar el Plugin:**
   - Ve a tu panel de administración de WordPress.
   - Navega a **Plugins > Añadir nuevo**.
   - Haz clic en **"Subir plugin"** y selecciona el archivo ZIP descargado.
   - Haz clic en **"Instalar ahora"** y luego en **"Activar"**.

### Método 2: Instalar Manualmente

1. **Clonar el Repositorio:**
bash
   git clone https://github.com/tu-usuario/formulario-shortcode-mejorado.git
   
2. **Subir la Carpeta a WordPress:**

   Copia la carpeta `formulario-shortcode-mejorado` a `wp-content/plugins/` en tu instalación de WordPress.

3. **Activar el Plugin:**

   - Ve a **Plugins > Plugins instalados** en el panel de administración de WordPress.
   - Busca "Formulario Shortcode Mejorado" y haz clic en **"Activar"**.

## ⚙️ Configuración

### 1. Configurar WP Mail SMTP

Para asegurar que los correos electrónicos enviados desde el formulario lleguen correctamente, es recomendable configurar WP Mail SMTP.

#### Instalar y Activar WP Mail SMTP:

- **Desde el Panel de Administración:**
  - Ve a **Plugins > Añadir nuevo**.
  - Busca **"WP Mail SMTP"**.
  - Haz clic en **"Instalar ahora"** y luego en **"Activar"**.

#### Configurar las Opciones SMTP:

1. Ve a **Ajustes > WP Mail SMTP**.
2. Ingresa los detalles de tu proveedor SMTP (por ejemplo, Gmail, Mailgun, SendGrid).
3. Guarda los cambios.

### 2. Configurar Google reCAPTCHA v2

#### Obtener las Claves de reCAPTCHA:

1. Regístrate y obtén las claves desde [Google reCAPTCHA](https://www.google.com/recaptcha/admin/create).
2. Selecciona **reCAPTCHA v2** y registra tu dominio.

#### Ingresar las Claves en el Plugin:

1. Ve a **Ajustes > Formulario Shortcode Mejorado** en el panel de administración de WordPress.
2. Ingresa tu **Site Key** y **Secret Key**.
3. Haz clic en **"Guardar Configuraciones"**.

### 3. Gestionar Campos Personalizados

El plugin permite añadir, editar o eliminar campos personalizados desde el panel de administración, proporcionando flexibilidad para adaptar el formulario a tus necesidades específicas.

#### Añadir un Nuevo Campo:

1. Ve a **Ajustes > Formulario Shortcode Mejorado**.
2. Navega a la sección **"Campos del Formulario"**.
3. Haz clic en **"Añadir Nuevo Campo"**.
4. Completa los detalles del campo:
   - **ID del Campo:** Un identificador único.
   - **Etiqueta:** El texto que se mostrará al usuario.
   - **Tipo de Campo:** Selecciona el tipo (texto, email, textarea, select, checkbox, radio, tel, etc.).
   - **Opciones:** (Si aplica) Añade opciones para campos como select o radio.
   - **Requerido:** Marca si el campo es obligatorio.
   - **Orden:** Define el orden en que aparecerá el campo en el formulario.
5. Guarda el nuevo campo.

#### Editar un Campo Existente:

1. En la sección **"Campos del Formulario"**, localiza el campo que deseas editar.
2. Haz clic en **"Editar"**.
3. Modifica los detalles según sea necesario.
4. Guarda los cambios.

#### Eliminar un Campo:

1. En la sección **"Campos del Formulario"**, localiza el campo que deseas eliminar.
2. Haz clic en **"Eliminar"**.
3. Confirma la eliminación.

## 📝 Uso

### Insertar el Formulario en una Página o Entrada

1. **Crear o Editar una Página/Entrada:**
   - Ve a **Páginas > Añadir nueva** o **Entradas > Añadir nueva**.

2. **Agregar el Shortcode:**
   - Inserta el siguiente shortcode donde deseas que aparezca el formulario:
plaintext
     [mpp_formulario]
     
3. **Publicar o Actualizar la Página/Entrada:**
   - Guarda los cambios para ver el formulario en acción.

## 🛡 Seguridad

- **Protección CSRF:** Utiliza nonces para proteger contra ataques de falsificación de solicitudes entre sitios.
- **Sanitización y Validación de Datos:** Los datos ingresados son saneados y validados tanto en el frontend como en el backend para garantizar su integridad.
- **Protección contra Spam:** Integra Google reCAPTCHA v2 y campos Honeypot para verificar que los usuarios son humanos.

## 📂 Estructura del Repositorio
formulario-shortcode-mejorado/  
&emsp;css/  
&emsp;&emsp;formulario.css  
&emsp;includes/  
&emsp;&emsp;funciones-tiempo-espera.php  
&emsp;&emsp;class-form-handler.php  
&emsp;js/  
&emsp;&emsp;formulario.js  
&emsp;templates/  
&emsp;&emsp;formulario-template.php  
formulario-shortcode.php  
README.md  
LICENSE  
.gitignore
## 🤝 Contribuciones

¡Las contribuciones son bienvenidas! Si deseas mejorar este plugin, por favor sigue estos pasos:

1. **Fork del Repositorio:**
   - Haz clic en el botón **"Fork"** en la esquina superior derecha de la página del repositorio.

2. **Clonar tu Fork:**
bash
   git clone https://github.com/tu-usuario/formulario-shortcode-mejorado.git
   
3. **Crear una Rama para tu Feature o Bugfix:**
bash
   git checkout -b nombre-de-tu-rama
   
4. **Realizar los Cambios y Confirmarlos:**
bash
   git add .
   git commit -m "Descripción de los cambios"
   
5. **Push a tu Rama:**
bash
   git push origin nombre-de-tu-rama
   
6. **Abrir un Pull Request:**
   - Ve a tu repositorio en GitHub y abre un Pull Request hacia el repositorio original.

## 📜 Licencia

Este plugin está licenciado bajo la [GNU General Public License v2.0](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html).

## 📞 Contacto

Si tienes alguna pregunta, sugerencia o problema, no dudes en abrir un **Issue** en el repositorio.

---