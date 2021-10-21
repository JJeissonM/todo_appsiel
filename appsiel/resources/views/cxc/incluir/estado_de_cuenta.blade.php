<style type="text/css">
    .table-bordered {
                border: 1px solid gray;
            }

            .table-bordered>tbody>tr>td, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>td, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>thead>tr>th {
                border: 1px solid gray;
            }
</style>
<table id="myTable" class="table table-borderless">
    <tbody>
        <tr>
            <td>
                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'show' ] )
            </td>                
        </tr>
        <tr>
            <td>
                <h3 style="width: 100%; text-align: center;"> Estado de cuenta </h3>
                <hr>
            </td>                
        </tr>
        <tr>
            <td>
                <b>Cliente:</b> {{ $cliente->tercero->descripcion }}
                &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
                <b>{{ config("configuracion.tipo_identificador") }} / CC:</b> 
                @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}	@else {{ $empresa->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}
                &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
                <b>Dirección: &nbsp;&nbsp;</b> {{ $cliente->tercero->direccion1 }}, {{ $cliente->tercero->ciudad->descripcion }} - {{ $cliente->tercero->ciudad->departamento->descripcion }}
                &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
                <b>Teléfono: &nbsp;&nbsp;</b> {{ $cliente->tercero->telefono1 }}
                &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
                <b>Email: &nbsp;&nbsp;</b> {{ $cliente->tercero->email }}
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>                
        </tr>
        <tr>
            <td>
                @include('cxc.reportes.documentos_pendientes_cxc')
            </td>                
        </tr>
    </tbody>
</table>