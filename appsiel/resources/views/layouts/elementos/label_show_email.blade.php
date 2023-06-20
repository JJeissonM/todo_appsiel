<?php
    $icono_warning_email = '';
    if ( $email == '' || gettype( filter_var($email, FILTER_VALIDATE_EMAIL) ) != 'string' )
    {
        $icono_warning_email = '<i class="fa fa-warning"></i> Sin Email';
    }

    if ( gettype( filter_var($email, FILTER_VALIDATE_EMAIL) ) != 'string' )
    {
        $icono_warning_email = '<i class="fa fa-warning"></i> Formato de Email incorrecto';
    }
?>
<br/>
<b>Email: &nbsp;&nbsp;</b> {{ $email }} {!! $icono_warning_email !!}