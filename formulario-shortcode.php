<?php
/**
 * Plugin Name: Formulario Shortcode Mejorado
 * Plugin URI: https://tusitio.com/
 * Description: Un plugin mejorado que inserta un formulario mediante un shortcode con seguridad nonce, envío de correos y estilos CSS personalizados.
 * Version: 2.0
 * Author: Jose Manuel Ropero
 * Author URI: https://tusitio.com/
 * License: GPL2
 * Text Domain: formulario-shortcode
 */

// Evitar el acceso directo fuera de WordPress
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes para rutas
define('MPP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MPP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Incluir la clase manejadora del formulario, maneja toda la lógica.
require_once MPP_PLUGIN_DIR . 'includes/class-form-handler.php';

// Instanciar la clase y activar el plugin
function mpp_init_form_handler() {
    $form_handler = new MPP_Form_Handler();
}
//hook para inicializar el formulario cuando se carguen todos los plugins.
add_action('plugins_loaded', 'mpp_init_form_handler');

// Registrar el hook de activación correctamente
register_activation_hook(__FILE__, 'mpp_crear_tabla_al_activar');

function mpp_crear_tabla_al_activar() {
    // Crear una instancia temporal para ejecutar el método de creación de tabla
    $form_handler = new MPP_Form_Handler();
    $form_handler->mpp_crear_tabla();

    // Definir los campos predeterminados
    $default_fields = array(
        'nombre' => array(
            'id' => 'nombre',
            'label' => 'Nombre',
            'type' => 'text',
            'required' => 1,
            'options' => array(),
            'order' => 1,
        ),
        'correo' => array(
            'id' => 'correo',
            'label' => 'Correo Electrónico',
            'type' => 'email',
            'required' => 1,
            'options' => array(),
            'order' => 2,
        ),
        'asunto' => array(
            'id' => 'asunto',
            'label' => 'Asunto',
            'type' => 'text',
            'required' => 1,
            'options' => array(),
            'order' => 3,
        ),
        'mensaje' => array(
            'id' => 'mensaje',
            'label' => 'Mensaje',
            'type' => 'textarea',
            'required' => 1,
            'options' => array(),
            'order' => 4,
        ),
        'telefono' => array(
            'id' => 'telefono',
            'label' => 'Teléfono',
            'type' => 'tel',
            'required' => 1,
            'options' => array(),
            'order' => 5,
        ),
        'boletin' => array(
            'id' => 'boletin',
            'label' => 'Quiero recibir el boletín informativo',
            'type' => 'checkbox',
            'required' => 0,
            'options' => array(),
            'order' => 6,
        ),
        'terminos' => array(
            'id' => 'terminos',
            'label' => 'Acepto los términos y condiciones',
            'type' => 'checkbox',
            'required' => 1,
            'options' => array(),
            'order' => 7,
        ),
    );

    // Obtener los campos existentes
    $existing_fields = get_option('mpp_form_fields', array());

    // Si no hay campos existentes, establecer los predeterminados
    if (empty($existing_fields)) {
        update_option('mpp_form_fields', $default_fields);
        error_log('Campos predeterminados cargados al activar el plugin.');
    }
}

