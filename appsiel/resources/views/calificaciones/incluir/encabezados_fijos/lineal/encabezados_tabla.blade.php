<?php 
		$cantidad_calificaciones = 16;

        $arr_labels = [
            [ '#cdddd8', 'Tareas'],
            [ '#ee8f6a', 'Quiz'],
            [ '#fffc2e', 'Exposición'],
            [ '#5b94e9', 'Mesa trabajo, apreciativa, participación'],
            [ '#e070e0', 'Ex. Final'],
            [ '#8df8a5', 'Prueba externa']
        ];
	?>	
<thead>
    <tr>
        <th>&nbsp;</th>
        <th colspan="7" style="background: {{$arr_labels[0][0]}}; text-align:center;">{{ $arr_labels[0][1]}} </th>
        <th colspan="2" style="background: {{$arr_labels[1][0]}}; text-align:center;"> {{ $arr_labels[1][1]}} </th>
        <th style="background: {{ $arr_labels[2][0]}}; text-align:center;"> {{ $arr_labels[2][1]}} </th>
        <th colspan="3" style="background: {{ $arr_labels[3][0]}}; text-align:center;"> {{ $arr_labels[3][1]}} </th>
        <th style="background: {{ $arr_labels[4][0]}}; text-align:center;"> {{ $arr_labels[4][1]}} </th>
        <th style="background: {{ $arr_labels[5][0]}}; text-align:center;"> {{ $arr_labels[5][1]}} </th>
        <th colspan="{{count($arr_labels_adicionales)}}">&nbsp;</th>
    </tr>
    <tr style="font-size: 16px;">
        <th>Asignatura</th>
        @for($k=1; $k < $cantidad_calificaciones; $k++)
            <?php
                if ($k <= 15 ) {
                    $pos_arr = 5;
                }
                if ($k <= 14 ) {
                    $pos_arr = 4;
                }
                if ($k <= 13 ) {
                    $pos_arr = 3;
                }
                if ($k <= 10 ) {
                    $pos_arr = 2;
                }
                if ($k <= 9 ) {
                    $pos_arr = 1;
                }
                if ($k <= 7 ) {
                    $pos_arr = 0;
                }
            ?>
            <th style="background: {{$arr_labels[$pos_arr][0]}}; text-align:center;">C{{$k}}</th>
        @endfor
        @foreach($arr_labels_adicionales as $key => $value)
            <th>{{$value}}</th>
        @endforeach
    </tr>
</thead>