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
                    <h2 align="center">Tabla de descuentos SIESA</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <a class="btn btn-success btn-sm" href="{{ url('siesa/tabla_descuentos/excel?ld=' . $ldFiltro) }}" target="_blank">
                        <i class="fa fa-file-excel-o"></i> Exportar Excel
                    </a>
                </div>
            </div>
            <br/>
            <div class="row">
                <div class="col-md-12">
                    <form method="GET" action="{{ url('siesa/tabla_descuentos') }}" class="form-inline">
                        <label for="ld">Cod. Lista Dcto.:</label>
                        <input type="text" name="ld" id="ld" class="form-control input-sm" value="{{ $ldFiltro }}" style="width: 120px;">
                        <label for="per_page">Filas por p&aacute;gina:</label>
                        <select name="per_page" id="per_page" class="form-control input-sm" onchange="this.form.submit()">
                            @foreach ([100,200,500,1000,2000] as $size)
                                <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                    </form>
                </div>
            </div>
            <br/>
            <div class="table-responsive">
                <table class="table table-bordered" id="myTable">
                    {{ Form::bsTableHeader(['LD','Codgio_dcto','Linea','Referencia_Item','Extension 1','Id_Cliente','Sucursal_Cliente','Lista_precio_cliente','Fecha Inicial','Cant Minima','Porcentaje_dcto']) }}
                    <tbody>
                        @php
                            $offset = ($rows->currentPage() - 1) * $rows->perPage();
                            $i = 0;
                        @endphp
                        @foreach ($rows as $row)
                            @php $i++; @endphp
                            <tr>
                                <td class="text-center">{{ $row->ld }}</td>
                                <td class="text-center">{{ $row->codigo_dcto }}</td>
                                <td class="text-center">{{ $offset + $i }}</td>
                                <td class="text-center">{{ $row->referencia_item }}</td>
                                <td class="text-center">{{ $row->extension_1 }}</td>
                                <td class="text-center">{{ $row->id_cliente }}</td>
                                <td class="text-center">{{ $row->sucursal_cliente }}</td>
                                <td class="text-center">{{ $row->lista_precio_cliente }}</td>
                                <td class="text-center">{{ $row->fecha_inicial }}</td>
                                <td class="text-center">{{ $row->cant_minima }}</td>
                                <td class="text-center">{{ $row->porcentaje_dcto }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-center">
                {{ $rows->appends(['per_page' => $perPage, 'ld' => $ldFiltro])->links() }}
            </div>
        </div>
    </div>
    <br/><br/>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function(){
        });
    </script>
@endsection

