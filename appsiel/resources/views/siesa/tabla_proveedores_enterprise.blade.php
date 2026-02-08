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
                    <h2 align="center">Tabla proveedores SIESA Enterprise</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <a class="btn btn-success btn-sm" href="{{ url('siesa/tabla_proveedores_enterprise/excel') }}" target="_blank">
                        <i class="fa fa-file-excel-o"></i> Exportar Excel
                    </a>
                </div>
            </div>
            <br/>
            <div class="row">
                <div class="col-md-12">
                    <form method="GET" action="{{ url('siesa/tabla_proveedores_enterprise') }}" class="form-inline">
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
                        'CodProveedor',
                        'SucurProveedor',
                        'DescripSucursal',
                        'ClaseProveedor',
                        'CondPago',
                        'TipoProveedor',
                        'FormaPagoProveedores',
                        'Observaciones',
                        'Contacto',
                        'Direccion1',
                        'Direccion2',
                        'Direccion3',
                        'Pais',
                        'Departamento',
                        'Ciudad',
                        'Barrio',
                        'Telefono',
                        'CorreoElectronico',
                        'FechaDeIngreso',
                        'MontoAnualDeCompra',
                        'IndDelMontoAnual',
                        'IndCotizDeCompra',
                        'IndOrdenDeCompraEDI',
                        'GrupoCentroOperacion',
                        'TelefonoCelular',
                        'IndSucPagosElectronicos'
                    ]) }}
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td class="text-center">{{ $row->cod_proveedor }}</td>
                                <td class="text-center">{{ $row->sucur_proveedor }}</td>
                                <td class="text-center">{{ $row->descrip_sucursal }}</td>
                                <td class="text-center">{{ $row->clase_proveedor }}</td>
                                <td class="text-center">{{ $row->cond_pago }}</td>
                                <td class="text-center">{{ $row->tipo_proveedor }}</td>
                                <td class="text-center">{{ $row->forma_pago_proveedores }}</td>
                                <td class="text-center">{{ $row->observaciones }}</td>
                                <td class="text-center">{{ $row->contacto }}</td>
                                <td class="text-center">{{ $row->direccion1 }}</td>
                                <td class="text-center">{{ $row->direccion2 }}</td>
                                <td class="text-center">{{ $row->direccion3 }}</td>
                                <td class="text-center">{{ $row->pais }}</td>
                                <td class="text-center">{{ $row->departamento }}</td>
                                <td class="text-center">{{ $row->ciudad }}</td>
                                <td class="text-center">{{ $row->barrio }}</td>
                                <td class="text-center">{{ $row->telefono }}</td>
                                <td class="text-center">{{ $row->correo_electronico }}</td>
                                <td class="text-center">{{ $row->fecha_de_ingreso }}</td>
                                <td class="text-center">{{ $row->monto_anual_de_compra }}</td>
                                <td class="text-center">{{ $row->ind_del_monto_anual }}</td>
                                <td class="text-center">{{ $row->ind_cotiz_de_compra }}</td>
                                <td class="text-center">{{ $row->ind_orden_de_compra_edi }}</td>
                                <td class="text-center">{{ $row->grupo_centro_operacion }}</td>
                                <td class="text-center">{{ $row->telefono_celular }}</td>
                                <td class="text-center">{{ $row->ind_suc_pagos_electronicos }}</td>
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
