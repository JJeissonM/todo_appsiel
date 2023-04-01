<h4 align="center">Planilla de Asistencia</h4>
<h5 align="center">Curso {{$curso->descripcion}} </h5>
<div class="row">
        <div class="col-sm-3">
            <b>Fecha inicial: </b> {{ $fecha_inicial }}
        </div>
        <div class="col-sm-3">
           <b> Fecha final: </b> {{$fecha_final }}
        </div>
        <div class="col-sm-6">
            <b>Iconos:</b>  &#128077;: Asistió &nbsp;&nbsp;&#128503;: No Asistió &nbsp;&nbsp; --: Sin registro
        </div>
</div>

<?php 
    function translate_day( $day_name )
    {
        switch ($day_name) {
            case "Sunday":
                return 'Do';
            break;
            case "Monday":
                return 'Lu';
            break;
            case "Tuesday":
                return 'Ma';
            break;
            case "Wednesday":
                return 'Mi';
            break;
            case "Thursday":
                return 'Ju';
            break;
            case "Friday":
                return 'Vi';
            break;
            case "Saturday":
                return 'Sa';
            break;
        }
    }
    $i=0;
    $init_date = date_create($fecha_inicial);
    $end_date = date_create($fecha_final);

    $actual_date = $init_date;
    $arr_header = [];
    $arr_dates = [];
    $arr_header[] = 'Estudiante';
    while ($actual_date <= $end_date) {
        
        $arr_dates[] = (object)[
            'date' => date_format($actual_date,"Y-m-d"),
            'label_day' => date_format($actual_date,"l")
        ];

        $arr_header[] = translate_day( date_format($actual_date,"l") ) . '/' . date_format($actual_date,"d");        
        date_add($actual_date, date_interval_create_from_date_string('1 days'));
    }
    //$arr_header[] = 'Total';
    
    //dd($registros);
    
    //dd($estudiantes);

?>
<div class="table-responsive">
<table class="table table-bordered table-striped">
    {{ Form::bsTableHeader($arr_header) }}
	<tbody>
	    <?php $i=0; ?>
	    @foreach ($estudiantes as $estudiante)	    
	    	<tr>
	            <td> {{ $estudiante->tercero->descripcion }} </td>
                @foreach ($arr_dates as $obj_date)
                    <?php 
                        $asistencia = $registros->where('id_estudiante',$estudiante->id)->where('fecha',$obj_date->date)->first();
                        //dd($asistencia);
                        $icono = '--';
                        if($asistencia != null)
                        {
                            $icono = '&#128077;';
                            if ($asistencia->asistio == 'No') {
                                $icono = '&#128503;';
                            }
                        }
                    ?>
	                <td style="font-size:18px; text-align: center;"> {!! $icono !!} </td>
                @endforeach
	            <!-- <td> </td> -->
	        </tr>
	    @endforeach
	</tbody>
</table>