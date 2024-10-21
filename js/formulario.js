document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.mpp-form');

    //Si el formulario existe...
    if (form) {
        form.addEventListener('submit', function (e) {  //Se ejecuta cuando se intenta enviar.
            let valid = true;
            let messages = [];  //Para almacenar los mensajes de error.

            // Validar el campo Nombre
            const nombre = document.getElementById('mpp_nombre');
            const nombrePattern = /^[A-Za-z0-9\s]+$/;
            if (!nombrePattern.test(nombre.value.trim())) {
                valid = false;
                messages.push(nombre.title);
            }

            // Validar el campo Correo Electrónico
            const correo = document.getElementById('mpp_correo');
            const correoPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!correoPattern.test(correo.value.trim())) {
                valid = false;
                messages.push(correo.title);
            }

            // Validar el campo Asunto
            const asunto = document.getElementById('mpp_asunto');
            if (asunto.value.trim() === '') {
                valid = false;
                messages.push(asunto.title);
            }

            // Validar el campo Mensaje
            const mensaje = document.getElementById('mpp_mensaje');
            if (mensaje.value.trim() === '') {
                valid = false;
                messages.push(mensaje.title);
            }

            // Validar el campo Teléfono
            const telefono = document.getElementById('mpp_telefono');
            if (telefono && telefono.value.trim() !== '') {
                const telefonoPattern = /^[0-9]{9}$/;
                if (!telefonoPattern.test(telefono.value.trim())) {
                    valid = false;
                    messages.push(telefono.title);
                }
            }

            // Validar reCAPTCHA
            const recaptchaResponse = grecaptcha.getResponse(); //Obtenemos el valor del token cuando el usuario lo completa.
            if (recaptchaResponse.length === 0) {
                valid = false;
                messages.push('Por favor, verifica que no eres un robot.');
            }

            // Validar aceptación de términos
            const terminos = document.getElementById('mpp_terminos');
            if (terminos && !terminos.checked) {
                valid = false;
                messages.push('Debes aceptar los términos y condiciones.');
            }

            if (!valid) {
                e.preventDefault(); // Prevenir el envío del formulario
                alert(messages.join('\n')); // Mostrar los mensajes de error
            }
        });
    }
});

// Validar que se acepten los términos y condiciones antes de enviar el formulario.
jQuery(document).ready(function($) {
    
    $('.mpp-form').on('submit', function(e) {
        if ($('#terminos').length && !$('#terminos').is(':checked')) {
            alert('Por favor, acepta los términos y condiciones.');
            e.preventDefault();
        }
    });
});

