@if( \App\Sistema\Aplicacion::where('app','consultorio_medico')->value('estado') == 'Activo' )
    <?php 
        $caja_trabajo = \App\Core\ModeloEavValor::get_valor_campo( '221-'.$doc_encabezado->id.'--1305' );
    ?>
    <br>
    <b>Historia clínica No.: &nbsp;&nbsp;</b> {{ App\Salud\Paciente::where( 'core_tercero_id', $doc_encabezado->core_tercero_id )->value('codigo_historia_clinica') }}
    <br>
    <b>Fecha y hora de entrega: &nbsp;&nbsp;</b> {{ $doc_encabezado->fecha_entrega }} &nbsp;&nbsp; - &nbsp;&nbsp; {{ $doc_encabezado->hora_entrega }}
    @if( $caja_trabajo != '' )
        &nbsp;&nbsp;&nbsp;&nbsp;  | &nbsp;&nbsp;&nbsp; <b>Caja de trabajo: &nbsp;&nbsp;</b> {{ $caja_trabajo }}
    @endif
    <br>
@endif