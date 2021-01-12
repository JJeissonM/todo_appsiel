<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        APPSIEL
    </title>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <!-- Styles -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">

    <link rel="stylesheet" href="{{ asset('assets/css/mis_estilos.css') }}">
    <style>
        body {
            font-family: 'Lato';
            background-color: #FFFFFF !important;
            font-size: 10px;
            /*width: 98%;*/
        }

        .page-break {
            page-break-after: always;
        }

        .container {
            width: 100%;
        }

        .row {
            width: 100%;
            border-left: 1px solid;
            border-right: 1px solid;
            border-bottom: 1px solid;
            padding: 5px;
        }
    </style>
</head>

<body id="app-layout">
    <div class="container-fluid">
        FECHA DE IMPRESIÓN REPORTE: {{$fecha}}
        <div class="row" style="background-color: #574696; color: #FFFFFF; border-top: 1px solid;">
            <table style="width: 100%">
                <tr>
                    <td style="width: 100%; text-align: center;">
                        <b style="font-size:18px;">{{$tituloExport}}</b>
                    </td>
                </tr>
            </table>
        </div>
        <?php
        if ($filtros !== null) {
            if (count($filtros) > 0) {
        ?>
                <div class="row" style="font-size: 14px;">
                    <table style="width: 100%;">
                        <?php
                        $i = $total = $van = 0;
                        $html = $row1 = $row2 = "";
                        $total = count($filtros);
                        foreach ($filtros as $key => $value) {
                            $i = $i + 1;
                            $van = $van + 1;
                            $row1 = $row1 . "<th style='width: 33.3% !important; text-align: center; background-color: #42A3DC; color:#FFFFFF;'>" . $key . "</th>";
                            $row2 = $row2 . "<th style='width: 33.3%; text-align: center; background-color: #42A3DC; color:#FFFFFF;'>" . $value . "</th>";
                            if ($i === 3) {
                                $i = 0;
                                $html = $html . "<tr>" . $row1 . "</tr><tr>" . $row2 . "</tr>";
                                $row1 = $row2 = "";
                            } else {
                                if ($van === $total) {
                                    $html = $html . "<tr>" . $row1 . "</tr><tr>" . $row2 . "</tr>";
                                    $row1 = $row2 = "";
                                }
                            }
                        }
                        echo $html;
                        ?>
                    </table>
                </div>
            <?php
            }
        }
        if ($registros !== null) {
            if (count($registros) > 0) {
            ?>
                <div class="row" style="font-size: 12px;">
                    <?php
                    if ($nivel === 1) {
                        //nivel 1
                        $html = "<table style='width: 100%;'><thead><tr>";
                        $i = $total = $van = 0;
                        $total = count($cabeceras);
                        foreach ($cabeceras as $c) {
                            $i = $i + 1;
                            $van = $van + 1;
                            $html = $html . "<th style='width: 20% !important; background-color:#50B794; color:#000000;'>" . $c . "</th>";
                            if ($i === 5) {
                                $i = 0;
                                $html = $html . "</tr><tr>";
                            } else {
                                if ($van === $total) {
                                    $html = $html . "</tr>";
                                }
                            }
                        }
                        $html = $html . "</thead><tbody>";
                        $parastilo = 0;
                        foreach ($registros as $value) {
                            $i2 = $van2 = 0;
                            $parastilo = $parastilo + 1;
                            $total2 = 0;
                            foreach ($value as $vv) {
                                $total2 = $total2 + 1;
                            }
                            if ($parastilo % 2 == 0) {
                                $html = $html . "<tr style='width: 20% !important; background-color: #ecf0f1;'>";
                            } else {
                                $html = $html . "<tr style='width: 20% !important;'>";
                            }
                            foreach ($value as $t) {
                                $i2 = $i2 + 1;
                                $van2 = $van2 + 1;
                                $html = $html . "<td>" . strtoupper($t) . "</td>";
                                if ($i2 === 5) {
                                    $i2 = 0;
                                    if ($parastilo % 2 == 0) {
                                        $html = $html . "</tr><tr style='width: 20% !important; background-color: #ecf0f1;'>";
                                    } else {
                                        $html = $html . "</tr><tr>";
                                    }
                                } else {
                                    if ($van2 === $total2) {
                                        $html = $html . "</tr>";
                                    }
                                }
                            }
                        }
                        echo $html . "</tbody></table>";
                    } else {
                        //nivel 2
                        $html = "";
                        foreach ($registros as $key => $value) {
                            $html = $html . "<table style='width: 100%;'><tr><th style='width: 100% !important; background-color: #ed4c1b; color:#FFFFFF;'>" . $key . "</th></tr></table>";
                            if (count($value) > 0) {
                                $html = $html . "<table style='width: 100%;'><tr>";
                                $i = $total = $van = 0;
                                $total = count($cabeceras);
                                foreach ($cabeceras as $c) {
                                    $i = $i + 1;
                                    $van = $van + 1;
                                    $html = $html . "<th style='width: 20% !important; background-color:#2196F3; color:#FFFFFF;'>" . $c . "</th>";
                                    if ($i === 5) {
                                        $i = 0;
                                        $html = $html . "</tr><tr>";
                                    } else {
                                        if ($van === $total) {
                                            $html = $html . "</tr>";
                                        }
                                    }
                                }
                                $html = $html . "</table><table style='width: 100%;'>";
                                $parastilo = 0;
                                foreach ($value as $v) {
                                    $html = $html . "<tr>";
                                    $parastilo = $parastilo + 1;
                                    $i2 = $total2 = $van2 = 0;
                                    $total2 = count($v);
                                    foreach ($v as $t) {
                                        $i2 = $i2 + 1;
                                        $van2 = $van2 + 1;
                                        if ($parastilo % 2 == 0) {
                                            $html = $html . "<td style='width: 20% !important; background-color: #ecf0f1;'>" . $t . "</td>";
                                        } else {
                                            $html = $html . "<td style='width: 20% !important;'>" . $t . "</td>";
                                        }
                                        if ($i2 === 5) {
                                            $i2 = 0;
                                            $html = $html . "</tr><tr>";
                                        } else {
                                            if ($van2 === $total2) {
                                                $html = $html . "</tr>";
                                            }
                                        }
                                    }
                                }
                                $html = $html . "</table>";
                            }
                        }
                        echo $html;
                    }
                    ?>
                </div>
        <?php
            }
        }
        ?>
    </div>
</body>

</html>