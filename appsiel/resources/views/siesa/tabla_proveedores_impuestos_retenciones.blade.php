@extends('layouts.principal')

@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    <hr>

    @include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">
            <br/>
            <div class="row">
                <div class="col-md-12">
                    <h2 align="center">Impuestos y retenciones por proveedor</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <a class="btn btn-success btn-sm" href="{{ url('siesa/tabla_proveedores_impuestos_retenciones/excel') }}" target="_blank">
                        <i class="fa fa-file-excel-o"></i> Exportar Excel
                    </a>
                </div>
            </div>
            <br/>
            <div class="row">
                <div class="col-md-12">
                    <form method="GET" action="{{ url('siesa/tabla_proveedores_impuestos_retenciones') }}" class="form-inline">
                        <label for="per_page">Filas por p&aacute;gina:</label>
                        <select name="per_page" id="per_page" class="form-control input-sm" onchange="this.form.submit()">
                            @foreach ([100,200,500,1000,2000] as $size)
                                <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
            <br/>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTable">
                    {{ Form::bsTableHeader([
                        'TipoRegistro (Impuestos cliente = 46, retención cliente = 47, Impuestos proveedor = 49, retención proveedor = 50)',
                        'CodClienteProveedor',
                        'SucurClienteProveedor',
                        'CodClaseImpRetencion',
                        'ConfTercero (IVA 0 NO APLICA, 1 APLICA) RET (0 NO RT, 1 RT, 2 AUTORETENEDOR)',
                        'Llave',
                        'RazonSocial'
                    ]) }}
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td class="text-center">{{ $row->tipo_registro }}</td>
                                <td class="text-center">{{ $row->cod_cliente_proveedor }}</td>
                                <td class="text-center">{{ $row->sucur_cliente_proveedor }}</td>
                                <td class="text-center">{{ $row->cod_clase_imp_retencion }}</td>
                                <td class="text-center">{{ $row->conf_tercero }}</td>
                                <td class="text-center">{{ $row->llave }}</td>
                                <td class="text-center">{{ $row->razon_social }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-center">
                {{ $rows->appends(['per_page' => $perPage])->links() }}
            </div>
        </div>
    </div>
    <br/><br/>
@endsection
