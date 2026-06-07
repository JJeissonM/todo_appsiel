$(document).ready(function() {
    var forbiddenChars = /[!@#$%^&*()_+\={}\[\]:;"'<>,.?/\\|~]/;
    var forbiddenCharsString = "! @ # $ % ^ & * ( ) _ + = { } [ ] : ; \" ' < > , . ? / \\ | ~";

    var fieldsToValidate = [
        '#descripcion', 
        '#razon_social', 
        '#nombre1', 
        '#otros_nombres', 
        '#apellido1', 
        '#apellido2'
    ];

    $(fieldsToValidate.join(', ')).on('input', function() {
        var value = $(this).val();
        if (forbiddenChars.test(value)) {
            // Remove the forbidden character
            $(this).val(value.replace(new RegExp(forbiddenChars, 'g'), ''));
            
            // Show alert
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Caracter no permitido',
                    text: 'No puedes usar los siguientes caracteres: ' + forbiddenCharsString
                });
            } else {
                alert('Caracter no permitido. No puedes usar los siguientes caracteres: ' + forbiddenCharsString);
            }
        }
    });

    // Also block submission if somehow pasted
    $('form').on('submit', function(e) {
        var hasError = false;
        $(fieldsToValidate.join(', ')).each(function() {
            var value = $(this).val();
            if (value && forbiddenChars.test(value)) {
                hasError = true;
                $(this).focus();
            }
        });

        if (hasError) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'Algunos campos contienen caracteres especiales no permitidos: ' + forbiddenCharsString
                });
            } else {
                alert('Error de validación. Algunos campos contienen caracteres especiales no permitidos: ' + forbiddenCharsString);
            }
            return false;
        }
    });
});
