<?php

$cols = 1; // cantidad de columnas, una por cada lapso a mostrar 

$tabla = '<table id="myTable" class="table table-striped" style="margin-top: -4px; font-size: 1.1em;">
                    <thead>
                        <th>
                           &nbsp;
                        </th>
                        <th>
                           &nbsp;
                        </th>
                        <th>
                           '.$lapso1_lbl.'
                        </th>';
if ( $lapso2_lbl != '' ) {
    $tabla.='<th>
               '.$lapso2_lbl.'
            </th>';
    $cols = 2;
}
if ( $lapso3_lbl != '' ) {
    $tabla.='<th>
               '.$lapso3_lbl.'
            </th>';
    $cols = 3;
}

$tabla.='<th></th></thead>
            <tbody>';

// una fila en blanco
$tabla.='<tr>
            '.str_repeat('<td>&nbsp;</td>',$cols+3).'
        </tr>';

echo $tabla;
?>