<?php
// Archivo: includes/funciones-tiempo-espera.php
if (!defined('ABSPATH')) {
    exit; // Evita el acceso directo al archivo
}

/**
 * Personalizar el tiempo de espera del formulario.
 *
 * @param int    $duracion Tiempo de espera actual en segundos.
 * @param string $correo   Correo electrónico del usuario.
 * @return int             Tiempo de espera modificado en segundos.
 */
function customizar_tiempo_espera_formulario($duracion, $correo) {
    // Para todos los usuarios, establecer 10 minutos
    return 600; // 600 segundos = 10 minutos
}

/**
 * Personalizar el tiempo de espera según si el usuario está logueado.
 *
 * @param int    $duracion Tiempo de espera actual en segundos.
 * @param string $correo   Correo electrónico del usuario.
 * @return int             Tiempo de espera modificado en segundos.
 */
function personalizar_tiempo_espera_por_usuario($duracion, $correo) {
    if (is_user_logged_in()) {
        return 300; // 5 minutos para usuarios registrados
    }
    return 600; // 10 minutos para visitantes
}

/**
 * Personalizar el tiempo de espera según el dominio del correo electrónico.
 *
 * @param int    $duracion Tiempo de espera actual en segundos.
 * @param string $correo   Correo electrónico del usuario.
 * @return int             Tiempo de espera modificado en segundos.
 */
function personalizar_tiempo_espera_por_dominio($duracion, $correo) {
    $dominio = substr(strrchr($correo, "@"), 1);
    if ($dominio === 'ejemplo.com') {
        return 120; // 2 minutos para usuarios de ejemplo.com
    }
    return 600; // 10 minutos para otros dominios
}

// Añadir los filtros
add_filter('mpp_formulario_tiempo_espera', 'customizar_tiempo_espera_formulario', 10, 2);
add_filter('mpp_formulario_tiempo_espera', 'personalizar_tiempo_espera_por_usuario', 10, 2);
add_filter('mpp_formulario_tiempo_espera', 'personalizar_tiempo_espera_por_dominio', 10, 2);
