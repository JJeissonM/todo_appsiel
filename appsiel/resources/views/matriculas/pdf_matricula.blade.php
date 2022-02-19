<!DOCTYPE html>
<html>

<head>
    <title>Matrícula</title>
    <style>
        body {
            /*font-size: 1em;*/
        }

        img {
            padding-left: 30px;
        }

        table {
            width: 100%;
            border: 1px solid;
            font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
        }

        table.encabezado {
            padding: 5px;
            text-align: center;
        }

        table.banner {
            font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
            font-style: italic;
            font-size: 1.2em;
        }

        table.contenido td {
            border: 1px solid;
            font-size: 12px;
            padding-left: 12px;
        }

        table.datos1 td.titulo {
            text-align: right;
            font: bold;
            font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
            padding-left: 8px;
            background: #B1BECF;
        }

        table.datos1 td.titulo2 {
            text-align: right;
            font: bold;
            font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
            padding-left: 8px;
            background: #B1BECF;
        }

        table.datos1 td.titulo3 {
            text-align: right;
            font: bold;
            font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
            padding-left: 8px;
            background: #B1BECF;
        }

        table.datos1 td.campo {
            font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
            background: #D9E0E8;
            overflow: visible;
            padding-left: 8px;
            width: 100%;
        }

        th {
            background-color: #E0E0E0;
            text-align: center;
        }

        .etiqueta {
            font-weight: bold;
            text-align: right;
        }

        h2 {
            font-family: "Monotype Corsiva";
            margin: 0;
        }

        h3 {
            margin: 0;
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
    $ancho_col = "120px";
    $ancho_col2 = "60px";
    $ancho_col3 = "130px";

    function tabla_datos($titulo, $nombre, $cedula, $ocupacion, $telefono, $email)
    {
        return '<table class="datos1">
                <tr>
                    <td colspan="2" align="center" style="background: #B1BECF;"> <b>' . $titulo . '</b></td>
                </tr>
                <tr>
                    <td class="titulo3">Nombre: </td> <td class="campo" style="overflow: hidden;">' . $nombre . '</td>
                </tr>
                <tr>
                    <td class="titulo3">Cédula: </td> <td class="campo" style="overflow: hidden;">' . number_format($cedula, '0', ',', '.') . '</td>
                </tr>
                <tr>
                    <td class="titulo3"> Ocupación: </td><td class="campo" style="overflow: hidden;">' . $ocupacion . '</td>
                </tr>
                <tr>
                    <td class="titulo3"> Teléfono: </td><td class="campo" style="overflow: hidden;"> ' . $telefono . ' </td>
                </tr>
                <tr>
                    <td class="titulo3"> E-mail: </td><td class="campo" style="overflow: hidden;"> <a href="mailto:' . $email . '" target="_blank">' . $email . ' </a></td>
                </tr>
            </table>';
    }

    $papa = (object)['tercero' => (object) ['descripcion' => '--', 'numero_identificacion' => 0, 'telefono1' => '--', 'email' => '--'], 'ocupacion' => '--'];
    $mama = (object)['tercero' => (object) ['descripcion' => '--', 'numero_identificacion' => 0, 'telefono1' => '--', 'email' => '--'], 'ocupacion' => '--'];


    $responsable_financiero = $matricula->estudiante->responsable_financiero();

    if (is_null($responsable_financiero)) {
        $responsable_financiero = (object)['tercero' => null];
    }

    if (is_null($responsable_financiero->tercero)) {
        $responsable_financiero->tercero = (object)['numero_identificacion' => 0, 'descripcion' => ' ------ Verificar Tercero'];
    }

    if (!is_null($matricula->estudiante->papa())) {
        $papa = $matricula->estudiante->papa();
    }

    if (!is_null($matricula->estudiante->mama())) {
        $mama = $matricula->estudiante->mama();
    }
    ?>


    @include('banner_colegio')

    <table>
        <tr>
            <td colspan="4" align="center"> <b style="font-size: 1.2em;">Matrícula Académica {{ $matricula->descripcion }}</b></td>
        </tr>
        <tr>
            <td width="{{$ancho_col}}" align="right">
                <span class="etiqueta">Matrícula No.: </span>
            </td>
            <td width="{{$ancho_col3}}">
                &nbsp; {{ $matricula->codigo }}
            </td>
            <td width="{{$ancho_col2}}" align="right">
                <span class="etiqueta">Fecha:</span>
            </td>
            <td>
                &nbsp; {{ $matricula->fecha_matricula }}
            </td>
        </tr>
        <tr>
            <td width="{{$ancho_col}}" align="right">
                <span class="etiqueta">Grado: </span>
            </td>
            <td width="{{$ancho_col3}}">
                &nbsp; {{ $matricula->nombre_curso }}
            </td>
            @if( config('matriculas.mostrar_password_en_ficha_matricula') )
            <td width="{{$ancho_col2}}" align="right">
                <span class="etiqueta">E-mail:</span>
                <?php
                $password = App\Core\PasswordReset::where('email', $estudiante->email)->get()->first();
                ?>
            </td>
            <td>
                &nbsp; {{ $estudiante->email }}
                @if( !is_null( $password) )
                <span style="color: #ddd;"> &nbsp; &nbsp; &nbsp; <b> Contraseña: </b>{{ $password->token }} </span>
                @endif
            </td>
            @else
            <td colspan="2">&nbsp;</td>
            @endif
        </tr>
    </table>

    <table class="datos1">
        <tr>
            <td colspan="4" align="center" style="background: #B1BECF;"> <b>Datos del estudiante</b></td>
        </tr>
        <tr>
            <?php

            function calcular_edad($fecha_nacimiento)
            {
                $datetime1 = new DateTime($fecha_nacimiento);
                $datetime2 = new DateTime('now');
                $interval = $datetime1->diff($datetime2);
                $edad = $interval->format('%R%a');
                return floor($edad / 365) . " Años";
            }

            ?>
            <td class="titulo">Nombre: </td>
            <td class="campo">{{ $estudiante->nombre1.' '.$estudiante->otros_nombres }}</td>
            <td class="titulo2">Fecha Nacimiento: </td>
            <td class="campo"> {{$estudiante->fecha_nacimiento}} </td>
        </tr>
        <tr>
            <td class="titulo"> Apellidos: </td>
            <td class="campo">{{ $estudiante->apellido1 }} {{ $estudiante->apellido2 }} </td>
            <td class="titulo2"> Ciudad Nacimiento: </td>
            <td class="campo"> {{$estudiante->ciudad_nacimiento}}</td>
        </tr>
        <tr>
            <td class="titulo"> Documento: </td>
            <td class="campo"> {{ $estudiante->tipo_y_numero_documento_identidad }} </td>
            <td class="titulo2"> Edad: </td>
            <td class="campo"> <?php echo calcular_edad($estudiante->fecha_nacimiento); ?> </td>
        </tr>
        <tr>
            <td class="titulo"> Dirección: </td>
            <td class="campo"> {{ $estudiante->direccion1 }}, {{ $estudiante->barrio }} </td>
            <td class="titulo2"> Teléfono: </td>
            <td class="campo"> {{ $estudiante->telefono1 }} </td>
        </tr>
        <tr>
            <td class="titulo">Datos Acudiente </td>
            <td class="campo" colspan="3">
                <b>Cédula: </b>{{ number_format( $responsable_financiero->tercero->numero_identificacion, '0',',','.') }} <br />
                <b>Nombre: </b>{{ $responsable_financiero->tercero->descripcion }}
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td> &nbsp;</td>
        </tr>
    </table>

    <table>
        <tr>
            <td>
                <?php
                echo tabla_datos('Datos del padre', $papa->tercero->descripcion, $papa->tercero->numero_identificacion, $papa->ocupacion, $papa->tercero->telefono1, $papa->tercero->email);
                ?>
            </td>
            <td>
                <?php
                echo tabla_datos('Datos de la madre', $mama->tercero->descripcion, $mama->tercero->numero_identificacion, $mama->ocupacion, $mama->tercero->telefono1, $mama->tercero->email);
                ?>
            </td>
        </tr>
    </table>

    <table class="datos1">
        <tr>
            <td colspan="4" align="center" style="background: #B1BECF;"> <b>Controles médicos</b></td>
        </tr>
        <tr>
            <td class="titulo2">Grupo sanguíneo: </td>
            <td class="campo" style="overflow: hidden;">{{ $estudiante->grupo_sanguineo }}</td>
            <td class="titulo2">Medicamento(s): </td>
            <td class="campo" style="overflow: hidden;">{{ $estudiante->medicamentos }}</td>
        </tr>
        <tr>
            <td class="titulo2"> Alergias: </td>
            <td class="campo">{{ $estudiante->alergias }}</td>
            <td class="titulo2"> E.P.S.: </td>
            <td class="campo">{{ $estudiante->eps }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="6" align="center"> <b>Requisitos de matrícula (Documentos)</b></td>
            <?php
            $requisitos = explode("-", str_replace("on", "checked", $matricula->requisitos));
            //print_r($requisitos);
            ?>
        </tr>
        <tr>
            <td width="15px"><input type="checkbox" {{$requisitos[0]}}></td>
            <td> Documento identidad</td>
            <td width="15px"><input type="checkbox" {{$requisitos[1]}}></td>
            <td>Constancia SIMAT</td>
            <td width="15px"><input type="checkbox" {{$requisitos[2]}}></td>
            <td>Fotos</td>
        </tr>
        <tr>
            <td width="15px"><input type="checkbox" {{$requisitos[3]}}></td>
            <td>Registro calificaciones</td>
            <td width="15px"><input type="checkbox" {{$requisitos[4]}}></td>
            <td>Certificación E.P.S.</td>
            <td width="15px"><input type="checkbox" {{$requisitos[5]}}></td>
            <td>Registro de vacunación</td>
        </tr>
    </table>

    <table>
        <tr>
            <td align="center" height="40px">
                <h2>Aceptamos todos los reglamentos del colegio.</h2>
            </td>
        </tr>
    </table>

    <br /><br />

    <table border="no">
        <tr>
            <td width="50px">
            <td> ______________________________ </td>
            <td width="20px">
            <td> ______________________________ </td>
            <td width="50px">
        </tr>
        <tr>
            <td width="50px">
            <td align="center"> Directora </td>
            <td width="20px">
            <td align="center"> Padre de familia o acudiente </td>
            <td width="50px">
        </tr>
    </table>


    <div style="width: 100%; position: fixed; bottom: 0;">
        <hr>
        {!! generado_por_appsiel() !!}
    </div>

</body>

</html>