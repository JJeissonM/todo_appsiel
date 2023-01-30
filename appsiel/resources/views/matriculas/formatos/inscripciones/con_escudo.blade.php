<!DOCTYPE html>
<html>
<head>
    <title>{{ $descripcion_transaccion }}</title>
    <style>
        body {
            border: solid 1px #333333;
        }
        @page{
            size: 612pt 410pt;
            margin: 15px;
        }

        footer { 
            position: absolute;
              right: 0;
              bottom: 0;
              left: 0;
              color: gray;
              text-align: right;
              font-size: 10px;
        }
    </style>
</head>

<body>
<?php
    function calcular_edad($fecha_nacimiento){
            $datetime1 = new DateTime($fecha_nacimiento);
            $datetime2 = new DateTime('now');
            $interval = $datetime1->diff($datetime2);
            $edad=$interval->format('%R%a');
            return floor($edad/365)." Años";
        }
?>

@include('banner_colegio_con_escudo')

<table class="table table-bordered" width="100%" style="border-collapse: collapse;">
    <tr>
        <td style="border: solid 1px black;" colspan="4" height="19">
            <p style="font-size: 20px; text-align: center; padding: 0; margin: 0;">
                {{ $descripcion_transaccion }}
            </p>
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>Código:</b> {{ $inscripcion->codigo }}
        </td>
        <td style="border: solid 1px black;">
            <b>Fecha:</b> {{ $inscripcion->fecha }}
        </td>
        <td style="border: solid 1px black;" colspan="2">
            <b>Grado:</b> {{ $inscripcion->nombre_grado }}
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black; text-align: center; background-color: #B1BECF;" colspan="4">
            <b>Datos básicos</b>
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;" colspan="2">
            <b>Nombre:</b> {{ $inscripcion->nombre_completo}}
        </td>
        <td style="border: solid 1px black;" colspan="2">
            <b>Documento:</b> {{ $inscripcion->tipo_y_numero_documento_identidad }}
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;" colspan="2">
            <b>Fecha nacimiento:</b> {{ $inscripcion->fecha_nacimiento }} (<?php  echo calcular_edad($inscripcion->fecha_nacimiento); ?>)
        </td>
        <td style="border: solid 1px black;" colspan="2">
            <b>Genero:</b> {{ $inscripcion->genero }}
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>Dirección:</b> {{ $inscripcion->direccion1 }}
        </td>
        <td style="border: solid 1px black;">
            <b>Teléfono:</b> {{ $inscripcion->telefono1 }}
        </td>
        <td style="border: solid 1px black;" colspan="2">
            <b>Email:</b> {{ $inscripcion->email }}
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;" colspan="2">
            <b>Colegio anterior:</b> {{ $inscripcion->colegio_anterior }}
        </td>
        <td style="border: solid 1px black;" colspan="4">
            <b>Observación:</b> {{ $inscripcion->observacion }}
        </td>
    </tr>
</table>

<br>
@include('matriculas.estudiantes.datos_basicos_padres')

<br><br>

<table width="100%">
    <tr>
        <td width="160px"></td>
        <td> ______________________________ </td>
        <td width="20px"></td>
        <td> ______________________________ </td>
        <td width="50px"> </td>
    </tr>
    <tr>
        <td width="160px"> </td>
        <td align="center"> Director(a) </td>
        <td width="20px"> </td>
        <td align="center"> Padre de familia o acudiente </td>
        <td width="50px"> </td>
    </tr>
</table>

<div style="bottom: 50px;">
    {!! generado_por_appsiel() !!}
</div>
</body>
</html>