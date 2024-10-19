<?php
// Mostrar el mensaje almacenado en el transiente
if ($message = get_transient('mpp_message')) {
    echo $message; // Mostrar el mensaje
    delete_transient('mpp_message'); // Eliminar el mensaje para que no se muestre nuevamente
}
?>
<div class="mpp-form-container">
    <form action="" method="post" class="mpp-form" enctype="multipart/form-data">
        <?php wp_nonce_field('mpp_formulario_envio', 'mpp_nonce'); ?>

        <?php
        // Obtener los campos personalizados
        $form_fields = get_option('mpp_form_fields', array());

        // Ordenar los campos según 'order'
        usort($form_fields, function($a, $b) {
            return $a['order'] - $b['order'];
        });

        // Renderizar cada campo
        foreach ($form_fields as $field) {
            $field_id = esc_attr($field['id']);
            $field_label = esc_html($field['label']);
            $field_type = esc_attr($field['type']);
            $field_required = $field['required'] ? 'required' : '';
            $field_options = $field['options'];
            ?>
            <p>
                <?php
                switch ($field_type) {
                    case 'text':
                        ?>
                        <p>
                            <label for="<?php echo $field_id; ?>"><?php echo $field_label; ?><?php echo $field['required'] ? ' *' : ''; ?></label><br>
                            <input type="text" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" <?php echo $field_required; ?> placeholder="<?php echo __('Ingresa tu ' . strtolower($field_label), 'formulario-shortcode'); ?>">
                        </p>
                        <?php
                        break;
                    case 'email':
                        ?>
                        <label for="<?php echo $field_id; ?>"><?php echo $field_label; ?><?php echo $field['required'] ? ' *' : ''; ?></label><br>
                        <input type="email" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" <?php echo $field_required; ?> placeholder="<?php echo __('Ingresa tu correo electrónico', 'formulario-shortcode'); ?>">
                        <?php
                        break;
                    case 'textarea':
                        ?>
                        <label for="<?php echo $field_id; ?>"><?php echo $field_label; ?><?php echo $field['required'] ? ' *' : ''; ?></label><br>
                        <textarea id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" rows="5" <?php echo $field_required; ?> placeholder="<?php echo __('Escribe aquí tu ' . strtolower($field_label), 'formulario-shortcode'); ?>"></textarea>
                        <?php
                        break;
                    case 'select':
                        ?>
                        <label for="<?php echo $field_id; ?>"><?php echo $field_label; ?><?php echo $field['required'] ? ' *' : ''; ?></label><br>
                        <select id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" <?php echo $field_required; ?>>
                            <option value=""><?php esc_html_e('-- Selecciona --', 'formulario-shortcode'); ?></option>
                            <?php
                            foreach ($field_options as $option) {
                                ?>
                                <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                        <?php
                        break;
                    case 'checkbox':
                        ?>
                        <p class="checkbox-group">
                            <input type="checkbox" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" value="1" <?php echo $field_required; ?>>
                            <label for="<?php echo $field_id; ?>" class="checkbox-label"><?php echo $field_label; ?></label>
                        </p>
                        <?php
                        break;
                    case 'radio':
                        ?>
                        <span><?php echo $field_label; ?><?php echo $field['required'] ? ' *' : ''; ?></span><br>
                        <?php foreach ($field_options as $option): ?>
                            <input type="radio" id="<?php echo $field_id . '_' . sanitize_title($option); ?>" name="<?php echo $field_id; ?>" value="<?php echo esc_attr($option); ?>" <?php echo $field_required; ?>>
                            <label for="<?php echo $field_id . '_' . sanitize_title($option); ?>"><?php echo esc_html($option); ?></label><br>
                        <?php endforeach; ?>
                        <?php
                        break;
                    case 'tel':
                        ?>
                        <label for="<?php echo $field_id; ?>"><?php echo $field_label; ?><?php echo $field['required'] ? ' *' : ''; ?></label><br>
                        <input type="tel" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" pattern="[0-9]{9}" <?php echo $field_required; ?> placeholder="<?php echo __('Ingresa tu número de teléfono', 'formulario-shortcode'); ?>" title="<?php esc_attr_e('Por favor, introduce un número de teléfono válido de 9 dígitos.', 'formulario-shortcode'); ?>">
                        <?php
                        break;
                    default:
                        ?>
                        <label for="<?php echo $field_id; ?>"><?php echo $field_label; ?><?php echo $field['required'] ? ' *' : ''; ?></label><br>
                        <input type="text" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" <?php echo $field_required; ?> placeholder="<?php echo __('Ingresa tu ' . strtolower($field_label), 'formulario-shortcode'); ?>">
                        <?php
                        break;
                }
                ?>
            </p>
            <?php
        }
        ?>

        <!-- Campo Honeypot oculto -->
        <p style="display: none;">
            <label for="mpp_honeypot"><?php esc_html_e('Deja este campo vacío', 'formulario-shortcode'); ?></label>
            <input type="text" id="mpp_honeypot" name="mpp_honeypot">
        </p>

        <!-- Integrar el widget de reCAPTCHA -->
        <div class="g-recaptcha" data-sitekey="<?php echo esc_attr(get_option('mpp_site_key', '')); ?>"></div>

        <!-- Botón de Envío -->
        <p>
            <input type="submit" name="mpp_submit" value="<?php esc_attr_e('Enviar Mensaje', 'formulario-shortcode'); ?>">
        </p>
    </form>
    <!-- Cargar el script de reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</div>





