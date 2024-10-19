<?php
if (!defined('ABSPATH')) {
    exit; // Evita el acceso directo al archivo
}

class MPP_Form_Handler {

    public function __construct() {

        // Incluir archivos necesarios
        include_once MPP_PLUGIN_DIR . 'includes/funciones-tiempo-espera.php';
        
        // Encolar estilos y scripts
        add_action('wp_enqueue_scripts', array($this, 'mpp_encolar_estilos_scripts'));

        // Añadir el shortcode
        add_shortcode('mpp_formulario', array($this, 'mpp_render_formulario'));

        // Agregar la página de configuración en el menú de WordPress
        add_action('admin_menu', array($this, 'mpp_agregar_pagina_configuracion'));

        // Procesar el formulario si se envía
        add_action('template_redirect', array($this, 'mpp_procesar_formulario'), 20);
    }

    /**
     * Crear tabla en la base de datos al activar el plugin
     */
    public function mpp_crear_tabla() {
        global $wpdb;
        $tabla_name = $wpdb->prefix . 'mpp_formularios';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $tabla_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            nombre varchar(100) NOT NULL,
            correo varchar(100) NOT NULL,
            asunto varchar(150) NOT NULL,
            mensaje text NOT NULL,
            telefono varchar(15),
            servicio varchar(50),
            boletin VARCHAR(3) NOT NULL DEFAULT 'No',
            datos_dinamicos text,
            fecha datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Encolar estilos y scripts para el frontend
     */
    public function mpp_encolar_estilos_scripts() {
        // Encolar el CSS del formulario
        wp_enqueue_style(
            'mpp-formulario-css',
            MPP_PLUGIN_URL . 'css/formulario.css',
            array(),
            '2.0'
        );

        // Encolar el JS del formulario
        wp_enqueue_script(
            'mpp-formulario-js',
            MPP_PLUGIN_URL . 'js/formulario.js',
            array('jquery'), // Dependencias
            '2.0',
            true // En el footer
        );

        // Encolar la librería de reCAPTCHA
        wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true);
    }

    /**
     * Renderizar el formulario mediante un shortcode
     */
    public function mpp_render_formulario($atts) {
        ob_start();

        // Mostrar el mensaje almacenado en el transiente
        if ($message = get_transient('mpp_message')) {
            echo $message;
            delete_transient('mpp_message');
            error_log('Mensaje de transiente mostrado: ' . $message);
        }

        // Incluir la plantilla del formulario
        $this->mpp_mostrar_formulario();
        error_log('Plantilla del formulario incluida.');

        return ob_get_clean();
    }

    /**
     * Mostrar el formulario HTML mediante la plantilla
     */
    private function mpp_mostrar_formulario() {
        // Obtener las claves de reCAPTCHA desde las opciones
        $site_key = get_option('mpp_site_key', '');
        error_log('Site Key obtenida: ' . $site_key);

        // Obtener los campos personalizados
        $form_fields = get_option('mpp_form_fields', array());

        // Convertir el arreglo asociativo a índice para ordenar
        $form_fields = array_values($form_fields);

        // Ordenar los campos según 'order'
        usort($form_fields, function($a, $b) {
            return $a['order'] - $b['order'];
        });

        // Incluir la plantilla
        include MPP_PLUGIN_DIR . 'templates/formulario-template.php';
        error_log('Plantilla del formulario cargada.');
    }

    /**
     * Procesar el formulario enviado
     */
    public function mpp_procesar_formulario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mpp_submit'])) {
            error_log('Inicio del procesamiento del formulario.');
    
            // Verificar el nonce
            if (!isset($_POST['mpp_nonce']) || !wp_verify_nonce($_POST['mpp_nonce'], 'mpp_formulario_envio')) {
                set_transient('mpp_message', '<div class="mpp-mensaje-error">' . __('Hubo un problema con tu envío. Por favor, intenta de nuevo.', 'formulario-shortcode') . '</div>', 60);
                error_log('Nonce inválido.');
                return; // No redirigir
            }
    
            // Verificar el campo Honeypot
            if (!empty($_POST['mpp_honeypot'])) {
                set_transient('mpp_message', '<div class="mpp-mensaje-error">' . __('Se detectó un envío sospechoso.', 'formulario-shortcode') . '</div>', 60);
                error_log('Campo Honeypot detectado.');
                return; // No redirigir
            }
    
            // Verificar la respuesta de reCAPTCHA
            if (!isset($_POST['g-recaptcha-response'])) {
                set_transient('mpp_message', '<div class="mpp-mensaje-error">' . __('Por favor, verifica que no eres un robot.', 'formulario-shortcode') . '</div>', 60);
                error_log('Falta la respuesta de reCAPTCHA.');
                return; // No redirigir
            }
    
            error_log('Verificación de reCAPTCHA en progreso.');
    
            $recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response']);
            $secret_key = get_option('mpp_secret_key', '');
    
            // Hacer la solicitud a la API de Google reCAPTCHA
            $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
                'body' => array(
                    'secret' => $secret_key,
                    'response' => $recaptcha_response,
                    'remoteip' => $_SERVER['REMOTE_ADDR'],
                ),
            ));
    
            if (is_wp_error($response)) {
                set_transient('mpp_message', '<div class="mpp-mensaje-error">' . __('Error al verificar reCAPTCHA. Por favor, intenta de nuevo.', 'formulario-shortcode') . '</div>', 60);
                error_log('Error en la solicitud de reCAPTCHA: ' . $response->get_error_message());
                return; // No redirigir
            }
    
            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body, true);
    
            if (!$result['success']) {
                set_transient('mpp_message', '<div class="mpp-mensaje-error">' . __('Falló la verificación de reCAPTCHA. Por favor, intenta de nuevo.', 'formulario-shortcode') . '</div>', 60);
                error_log('Verificación de reCAPTCHA fallida: ' . print_r($result, true));
                return; // No redirigir
            }
    
            // Validar frecuencia de envío
            // Asegúrate de que el campo de correo exista
            $correo = isset($_POST['correo']) ? sanitize_email($_POST['correo']) : '';
            if (empty($correo)) {
                set_transient('mpp_message', '<div class="mpp-mensaje-error">' . __('Se requiere tu correo electrónico para procesar el formulario.', 'formulario-shortcode') . '</div>', 60);
                error_log('Correo electrónico ausente para limitar frecuencia de envío.');
                return; // No redirigir
            }
    
            $limite_envio = $this->mpp_limitar_frecuencia_envio($correo);
            if (!$limite_envio) {
                set_transient('mpp_message', '<div class="mpp-mensaje-error">' . __('Has enviado un formulario recientemente. Por favor, espera unos minutos antes de intentar nuevamente.', 'formulario-shortcode') . '</div>', 60);
                error_log('Frecuencia de envío excedida.');
                return; // No redirigir
            } elseif (is_int($limite_envio)) {
                $minutos = ceil($limite_envio / 60);
                set_transient('mpp_message', '<div class="mpp-mensaje-error">' . sprintf(__('Has enviado un formulario recientemente. Por favor, espera %d minuto(s) antes de intentar nuevamente.', 'formulario-shortcode'), $minutos) . '</div>', 60);
                error_log('Frecuencia de envío: esperar ' . $minutos . ' minutos.');
                return; // No redirigir
            }
    
            // Obtener los campos personalizados
            $form_fields = get_option('mpp_form_fields', array());
    
            $datos_formulario = array();
    
            // Validar y sanitizar dinámicamente los campos personalizados
            foreach ($form_fields as $field) {
                $field_id = sanitize_text_field($field['id']);
                $field_label = sanitize_text_field($field['label']);
                $field_type = sanitize_text_field($field['type']);
                $field_required = $field['required'];
    
                // Obtener el valor del campo
                if ($field_type === 'checkbox') {
                    $valor = isset($_POST[$field_id]) ? 'Sí' : 'No';
                } elseif ($field_type === 'radio' || $field_type === 'select') {
                    $valor = isset($_POST[$field_id]) ? sanitize_text_field($_POST[$field_id]) : '';
                } else {
                    $valor = isset($_POST[$field_id]) ? sanitize_text_field($_POST[$field_id]) : '';
                }
    
                // Validar si el campo es obligatorio
                if ($field_required && (is_string($valor) && trim($valor) === '') ) {
                    set_transient('mpp_message', '<div class="mpp-mensaje-error">' . sprintf(__('El campo "%s" es obligatorio.', 'formulario-shortcode'), $field_label) . '</div>', 60);
                    error_log("Campo obligatorio vacío: $field_label");
                    return; // No redirigir
                }
    
                // Validar formatos específicos
                if ($field_type === 'email' && !empty($valor) && !is_email($valor)) {
                    set_transient('mpp_message', '<div class="mpp-mensaje-error">' . sprintf(__('Por favor, introduce una dirección de correo válida para "%s".', 'formulario-shortcode'), $field_label) . '</div>', 60);
                    error_log("Formato de correo inválido para $field_label: $valor");
                    return; // No redirigir
                }
    
                if ($field_type === 'tel' && !empty($valor) && !preg_match('/^[0-9]{9}$/', $valor)) {
                    set_transient('mpp_message', '<div class="mpp-mensaje-error">' . sprintf(__('Por favor, introduce un número de teléfono válido de 9 dígitos para "%s".', 'formulario-shortcode'), $field_label) . '</div>', 60);
                    error_log("Formato de teléfono inválido para $field_label: $valor");
                    return; // No redirigir
                }
    
                // Agregar el valor al arreglo de datos
                $datos_formulario[$field_id] = $valor;
            }
    
            // Procesar campos adicionales (Boletín Informativo ya incluido como campo personalizado)
            $boletin = isset($datos_formulario['boletin']) ? 1 : 0;
            error_log('Contenido de $datos_formulario: ' . print_r($datos_formulario, true));

    
            // Insertar los datos en la base de datos
            $this->mpp_insertar_datos($datos_formulario, $boletin);
            error_log('Datos insertados en la base de datos.');
    
            // Enviar correo electrónico al administrador
            $this->mpp_enviar_correo($datos_formulario, $boletin);
            error_log('Correo enviado al administrador.');
    
            // Enviar correo electrónico al usuario (confirmación)
            $nombre = isset($datos_formulario['nombre']) ? $datos_formulario['nombre'] : '';
            $correo_usuario = isset($datos_formulario['correo']) ? $datos_formulario['correo'] : '';
            $this->mpp_enviar_correo_usuario($nombre, $correo_usuario);
            error_log('Correo de confirmación enviado al usuario.');
    
            // Mensaje de éxito
            set_transient('mpp_message', '<div class="mpp-mensaje-exito">' . sprintf(__('Gracias, %s. Hemos recibido tu información.', 'formulario-shortcode'), esc_html($nombre)) . '</div>', 60);
            error_log('Mensaje de éxito establecido.');
    
            // No redirigir; permitir que el mensaje se muestre en la misma página
        }
    }
    

    /**
     * Limitar la frecuencia de envío del formulario
     */
    private function mpp_limitar_frecuencia_envio($correo) {
        $transient_key = 'mpp_ultimo_envio_' . md5($correo);
        $ultima_solicitud = get_transient($transient_key);

        if ($ultima_solicitud) {
            // Obtener el tiempo restante en segundos
            $time_remaining = get_option("_transient_timeout_{$transient_key}") - current_time('timestamp');
            return $time_remaining > 0 ? $time_remaining : false;
        }

        // Establecer un transient de 10 minutos (600 segundos)
        set_transient($transient_key, time(), 600);

        return true;
    }

    /**
     * Insertar datos en la base de datos
     */
    private function mpp_insertar_datos($datos_formulario, $boletin) {
        global $wpdb;
        $tabla_name = $wpdb->prefix . 'mpp_formularios';

        // Convertir el arreglo de datos en JSON para almacenamiento flexible
        $datos_json = maybe_serialize($datos_formulario);

        $wpdb->insert(
            $tabla_name,
            array(
                'nombre'          => isset($datos_formulario['nombre']) ? $datos_formulario['nombre'] : '',
                'correo'          => isset($datos_formulario['correo']) ? $datos_formulario['correo'] : '',
                'asunto'          => isset($datos_formulario['asunto']) ? $datos_formulario['asunto'] : '',
                'mensaje'         => isset($datos_formulario['mensaje']) ? $datos_formulario['mensaje'] : '',
                'telefono'        => isset($datos_formulario['telefono']) ? $datos_formulario['telefono'] : '',
                'servicio'        => isset($datos_formulario['servicio']) ? $datos_formulario['servicio'] : '',
                'boletin'         => $boletin,
                'datos_dinamicos' => $datos_json,
            )
        );
    }

    /**
     * Enviar correo electrónico al administrador
     */
    private function mpp_enviar_correo($datos_formulario, $boletin) {
        $admin_email = get_option('admin_email');
        $asunto_email = __('Nuevo Formulario Enviado', 'formulario-shortcode');
        $mensaje_email = __('Has recibido un nuevo formulario de contacto con los siguientes detalles:', 'formulario-shortcode') . "\n\n";
    
        foreach ($datos_formulario as $campo => $valor) {
            $campo_legible = ucfirst(str_replace('_', ' ', $campo));
            $mensaje_email .= $campo_legible . ': ' . $valor . "\n";
        }
    
        $mensaje_email .= "\n" . __('Desea recibir el boletín:', 'formulario-shortcode') . ' ' . ($boletin ? __('Sí', 'formulario-shortcode') : __('No', 'formulario-shortcode')) . "\n";
    
        $headers = array('Content-Type: text/plain; charset=UTF-8');
    
        $mail_sent = wp_mail($admin_email, $asunto_email, $mensaje_email, $headers);
    
        if ($mail_sent) {
            error_log('Correo enviado exitosamente al administrador.');
        } else {
            error_log('Error al enviar el correo al administrador.');
        }
    }
    

    /**
     * Enviar correo electrónico al usuario (confirmación)
     */
    private function mpp_enviar_correo_usuario($nombre, $correo) {
        $asunto = __('Confirmación de Recepción de tu Mensaje', 'formulario-shortcode');
        $mensaje = sprintf(__('Hola %s, hemos recibido tu mensaje y nos pondremos en contacto contigo pronto.', 'formulario-shortcode'), $nombre) . "\n\n";
        $mensaje .= __('Gracias por contactarnos.', 'formulario-shortcode');

        wp_mail($correo, $asunto, $mensaje);
    }

    /**
     * Agregar página de configuración al menú de administración
     */
    public function mpp_agregar_pagina_configuracion() {
        add_options_page(
            __('Configuración Formulario Shortcode', 'formulario-shortcode'),
            __('Formulario Shortcode', 'formulario-shortcode'),
            'manage_options',
            'formulario-shortcode',
            array($this, 'mpp_render_pagina_configuracion')
        );
    }

    /**
     * Renderizar la página de configuración
     */
    public function mpp_render_pagina_configuracion() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            return;
        }
    
        // Procesar actualizaciones de configuración general
        if (isset($_POST['mpp_form_submitted']) && $_POST['mpp_form_submitted'] == 'general') {
            check_admin_referer('mpp_formulario_configuracion_general', 'mpp_nonce_config_general');
    
            // Sanear los datos
            $site_key = sanitize_text_field($_POST['mpp_site_key']);
            $secret_key = sanitize_text_field($_POST['mpp_secret_key']);
    
            // Actualizar las opciones
            update_option('mpp_site_key', $site_key);
            update_option('mpp_secret_key', $secret_key);
    
            echo '<div class="updated"><p>' . __('Configuraciones generales guardadas exitosamente.', 'formulario-shortcode') . '</p></div>';
            error_log('Configuraciones generales guardadas: Site Key y Secret Key actualizadas.');
        }
    
        // Procesar actualizaciones de campos personalizados
        if (isset($_POST['mpp_form_submitted']) && $_POST['mpp_form_submitted'] == 'fields') {
            check_admin_referer('mpp_formulario_configuracion_fields', 'mpp_nonce_config_fields');
    
            // Obtener campos actuales
            $fields = get_option('mpp_form_fields', array());
    
            // Actualizar campos
            if (isset($_POST['mpp_form_fields']) && is_array($_POST['mpp_form_fields'])) {
                $updated_fields = array();
                foreach ($_POST['mpp_form_fields'] as $field_id => $field) {
                    // Verificar si el campo está marcado para eliminar
                    if (isset($field['delete']) && $field['delete'] == '1') {
                        error_log("Campo '$field_id' marcado para eliminar.");
                        continue; // Saltar este campo, no lo incluirá en los campos actualizados
                    }
    
                    // Sanitizar y validar cada campo
                    $raw_id = sanitize_text_field($field['id']);
                    $sanitized_id = sanitize_title($raw_id);
    
                    // Validar que el `id` no esté vacío
                    if (empty($sanitized_id)) {
                        add_settings_error(
                            'mpp_form_fields',
                            'mpp_id_error',
                            __('El ID del campo no puede estar vacío y debe contener solo letras, números y guiones bajos.', 'formulario-shortcode'),
                            'error'
                        );
                        continue;
                    }
    
                    // Validar unicidad del `id`
                    if (array_key_exists($sanitized_id, $updated_fields)) {
                        add_settings_error(
                            'mpp_form_fields',
                            'mpp_duplicate_id',
                            __('El ID del campo debe ser único. El ID "' . esc_html($sanitized_id) . '" ya está en uso.', 'formulario-shortcode'),
                            'error'
                        );
                        continue;
                    }
    
                    $field_label = sanitize_text_field($field['label']);
                    $field_type = sanitize_text_field($field['type']);
                    $field_required = isset($field['required']) ? 1 : 0;
                    $field_options = isset($field['options']) ? array_map('sanitize_text_field', explode(',', $field['options'])) : array();
                    $field_order = intval($field['order']);
    
                    $updated_fields[$sanitized_id] = array(
                        'id' => $sanitized_id,
                        'label' => $field_label,
                        'type' => $field_type,
                        'required' => $field_required,
                        'options' => $field_options,
                        'order' => $field_order,
                    );
                }
    
                // Actualizar las opciones si no hay errores
                if (!get_settings_errors('mpp_form_fields')) {
                    update_option('mpp_form_fields', $updated_fields);
                    echo '<div class="updated"><p>' . __('Campos personalizados actualizados exitosamente.', 'formulario-shortcode') . '</p></div>';
                    error_log('Campos personalizados actualizados.');
                }
            }
        }
    
        // Obtener campos personalizados actuales
        $form_fields = get_option('mpp_form_fields', array());
    
        // Obtener las configuraciones generales actuales
        $site_key = get_option('mpp_site_key', '');
        $secret_key = get_option('mpp_secret_key', '');
    
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Configuración Formulario Shortcode', 'formulario-shortcode'); ?></h1>
    
            <!-- Mostrar errores si los hay -->
            <?php settings_errors('mpp_form_fields'); ?>
    
            <!-- Formulario de Configuración General -->
            <h2><?php esc_html_e('Configuraciones Generales', 'formulario-shortcode'); ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field('mpp_formulario_configuracion_general', 'mpp_nonce_config_general'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Site Key de reCAPTCHA', 'formulario-shortcode'); ?></th>
                        <td>
                            <input type="text" name="mpp_site_key" value="<?php echo esc_attr($site_key); ?>" class="regular-text" required>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Secret Key de reCAPTCHA', 'formulario-shortcode'); ?></th>
                        <td>
                            <input type="text" name="mpp_secret_key" value="<?php echo esc_attr($secret_key); ?>" class="regular-text" required>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Guardar Configuraciones Generales', 'formulario-shortcode')); ?>
                <input type="hidden" name="mpp_form_submitted" value="general">
            </form>
    
            <hr>
    
            <!-- Formulario de Configuración de Campos Personalizados -->
            <h2><?php esc_html_e('Campos Personalizados', 'formulario-shortcode'); ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field('mpp_formulario_configuracion_fields', 'mpp_nonce_config_fields'); ?>
                <table class="form-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID del Campo', 'formulario-shortcode'); ?></th>
                            <th><?php esc_html_e('Etiqueta', 'formulario-shortcode'); ?></th>
                            <th><?php esc_html_e('Tipo', 'formulario-shortcode'); ?></th>
                            <th><?php esc_html_e('Obligatorio', 'formulario-shortcode'); ?></th>
                            <th><?php esc_html_e('Opciones (para select/radio)', 'formulario-shortcode'); ?></th>
                            <th><?php esc_html_e('Orden', 'formulario-shortcode'); ?></th>
                            <th><?php esc_html_e('Eliminar', 'formulario-shortcode'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="mpp-fields-wrapper">
                        <?php
                        if (!empty($form_fields)) {
                            foreach ($form_fields as $field) {
                                ?>
                                <tr>
                                    <td>
                                        <input type="text" name="mpp_form_fields[<?php echo esc_attr($field['id']); ?>][id]" value="<?php echo esc_attr($field['id']); ?>" required pattern="[A-Za-z0-9_]+" title="<?php esc_attr_e('Solo letras, números y guiones bajos.', 'formulario-shortcode'); ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="mpp_form_fields[<?php echo esc_attr($field['id']); ?>][label]" value="<?php echo esc_attr($field['label']); ?>" required>
                                    </td>
                                    <td>
                                        <select name="mpp_form_fields[<?php echo esc_attr($field['id']); ?>][type]">
                                            <option value="text" <?php selected($field['type'], 'text'); ?>><?php esc_html_e('Texto', 'formulario-shortcode'); ?></option>
                                            <option value="email" <?php selected($field['type'], 'email'); ?>><?php esc_html_e('Correo Electrónico', 'formulario-shortcode'); ?></option>
                                            <option value="textarea" <?php selected($field['type'], 'textarea'); ?>><?php esc_html_e('Área de Texto', 'formulario-shortcode'); ?></option>
                                            <option value="select" <?php selected($field['type'], 'select'); ?>><?php esc_html_e('Seleccionar', 'formulario-shortcode'); ?></option>
                                            <option value="checkbox" <?php selected($field['type'], 'checkbox'); ?>><?php esc_html_e('Casilla de Verificación', 'formulario-shortcode'); ?></option>
                                            <option value="radio" <?php selected($field['type'], 'radio'); ?>><?php esc_html_e('Botón de Radio', 'formulario-shortcode'); ?></option>
                                            <option value="tel" <?php selected($field['type'], 'tel'); ?>><?php esc_html_e('Teléfono', 'formulario-shortcode'); ?></option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="mpp_form_fields[<?php echo esc_attr($field['id']); ?>][required]" value="1" <?php checked($field['required'], 1); ?>>
                                    </td>
                                    <td>
                                        <input type="text" name="mpp_form_fields[<?php echo esc_attr($field['id']); ?>][options]" value="<?php echo esc_attr(implode(',', $field['options'])); ?>" placeholder="<?php esc_attr_e('Opción1,Opción2,...', 'formulario-shortcode'); ?>">
                                    </td>
                                    <td>
                                        <input type="number" name="mpp_form_fields[<?php echo esc_attr($field['id']); ?>][order]" value="<?php echo esc_attr($field['order']); ?>" min="1">
                                    </td>
                                    <td>
                                        <input type="checkbox" name="mpp_form_fields[<?php echo esc_attr($field['id']); ?>][delete]" value="1">
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
                <button type="button" class="button" id="mpp-add-field"><?php esc_html_e('Añadir Campo', 'formulario-shortcode'); ?></button>
                <?php submit_button(__('Guardar Campos Personalizados', 'formulario-shortcode')); ?>
                <input type="hidden" name="mpp_form_submitted" value="fields">
            </form>
        </div>
    
        <!-- JavaScript para Añadir Nuevos Campos Dinámicamente -->
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#mpp-add-field').on('click', function(e) {
                    e.preventDefault();
                    var uniqueId = 'field_' + Date.now();
                    var newRow = '<tr>' +
                        '<td>' +
                            '<input type="text" name="mpp_form_fields[' + uniqueId + '][id]" value="" required pattern="[A-Za-z0-9_]+" title="<?php esc_attr_e('Solo letras, números y guiones bajos.', 'formulario-shortcode'); ?>">' +
                        '</td>' +
                        '<td>' +
                            '<input type="text" name="mpp_form_fields[' + uniqueId + '][label]" value="" required>' +
                        '</td>' +
                        '<td>' +
                            '<select name="mpp_form_fields[' + uniqueId + '][type]">' +
                                '<option value="text"><?php esc_html_e('Texto', 'formulario-shortcode'); ?></option>' +
                                '<option value="email"><?php esc_html_e('Correo Electrónico', 'formulario-shortcode'); ?></option>' +
                                '<option value="textarea"><?php esc_html_e('Área de Texto', 'formulario-shortcode'); ?></option>' +
                                '<option value="select"><?php esc_html_e('Seleccionar', 'formulario-shortcode'); ?></option>' +
                                '<option value="checkbox"><?php esc_html_e('Casilla de Verificación', 'formulario-shortcode'); ?></option>' +
                                '<option value="radio"><?php esc_html_e('Botón de Radio', 'formulario-shortcode'); ?></option>' +
                                '<option value="tel"><?php esc_html_e('Teléfono', 'formulario-shortcode'); ?></option>' +
                            '</select>' +
                        '</td>' +
                        '<td>' +
                            '<input type="checkbox" name="mpp_form_fields[' + uniqueId + '][required]" value="1">' +
                        '</td>' +
                        '<td>' +
                            '<input type="text" name="mpp_form_fields[' + uniqueId + '][options]" value="" placeholder="<?php esc_attr_e('Opción1,Opción2,...', 'formulario-shortcode'); ?>">' +
                        '</td>' +
                        '<td>' +
                            '<input type="number" name="mpp_form_fields[' + uniqueId + '][order]" value="1" min="1">' +
                        '</td>' +
                        '<td>' +
                            '<input type="checkbox" name="mpp_form_fields[' + uniqueId + '][delete]" value="1">' +
                        '</td>' +
                    '</tr>';
                    $('#mpp-fields-wrapper').append(newRow);
                });
    
                // Validación en el lado del cliente antes de enviar el formulario de configuración de campos
                $('form').on('submit', function(e) {
                    var isValid = true;
                    $(this).find('input[type="text"][required], select[required]').each(function() {
                        if ($(this).val().trim() === '') {
                            alert('Por favor, completa todos los campos obligatorios.');
                            isValid = false;
                            return false; // Salir del loop
                        }
                    });
    
                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            });
        </script>
    
        <?php
    }

} // Cierre de la clase






