<?php
    $colegio = App\Core\Colegio::where('empresa_id','=', Auth::user()->empresa_id )
                    ->get()->first();

    $empresa = App\Core\Empresa::find( Auth::user()->empresa_id );

    $config = config('gestion_documental');

    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen;


    // MEJORA: HACER UN ARRAY CON LOS IF DE LA CONFIGURACIÓN, AGREGAR CAMPOS DE ORDEN PARA CADA CAMPO A LA CONFIGURACIÓN, LUEGO ORDENAR EL ARRAY POR ESE ORDEN
?>
<table class="table banner">
    <tr>
        <td width="200px">
            <img src="{{ $url }}" width="{{ config('configuracion.ancho_logo_formatos') }}" height="{{ config('configuracion.alto_logo_formatos') }}" />
        </td>

        <td align="center">
            <br/>
            <span style="font-size: 1.4em;"><b>{{ $colegio->descripcion }}</b> </span>

            @if( $config['banner_colegio_mostrar_slogan'] )
                <br/> <b>{{ $colegio->slogan }}</b>
            @endif

            <br/> Resolución {{ $colegio->resolucion }}

            @if( $config['banner_colegio_mostrar_nit'] )
                <br>{{ config("configuracion.tipo_identificador") }}:
                @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}	@else {{ $empresa->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}
            @endif

            @if( $config['banner_colegio_mostrar_direccion'] )
                <br/> {{ $colegio->direccion }}
                @if( $config['banner_colegio_mostrar_telefono'] )
                    , Teléfonos: {{ $colegio->telefonos }}
                @endif
            @endif


            @if( $config['banner_colegio_mostrar_ciudad'] )
                <?php
                    $ciudad = DB::table('core_ciudades')->where( 'id', $empresa->codigo_ciudad )->get()[0];
                    $departamento = DB::table('core_departamentos')->where( 'id', $ciudad->core_departamento_id )->get()[0];
                ?>
                <br> {{ $ciudad->descripcion }} - {{ $departamento->descripcion }}
            @endif

            <br/>
            
        </td>
    </tr>
</table>