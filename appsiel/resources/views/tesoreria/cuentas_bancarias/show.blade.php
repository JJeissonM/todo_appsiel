@extends('layouts.principal')

@section('content')

    {{ Form::bsMigaPan($miga_pan) }}

    <div class="row">
        <div class="col-md-6">
            &nbsp;&nbsp;&nbsp;
            <div class="btn-group">
                @if( isset($url_crear) && $url_crear!='' )
                    {{ Form::bsBtnCreate($url_crear) }}
                @endif

                @if( isset($url_edit) && $url_edit!='' )
                    {{ Form::bsBtnEdit2(str_replace('id_fila', $registro->id, $url_edit),'Editar') }}
                @endif

                @if(isset($botones))
                    @foreach($botones as $boton)
                        {!! str_replace( 'id_fila', $registro->id, $boton->dibujar() ) !!}
                    @endforeach
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <div class="btn-group pull-right">
                @if($reg_anterior!='')
                    {{ Form::bsBtnPrev('teso_cuentas_bancarias/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion')) }}
                @endif

                @if($reg_siguiente!='')
                    {{ Form::bsBtnNext('teso_cuentas_bancarias/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion')) }}
                @endif
            </div>
        </div>
    </div>
    <hr>

    @include('layouts.mensajes')

    <?php use App\Http\Controllers\Sistema\VistaController; ?>

    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#tab_datos">Datos básicos</a></li>
        <li><a data-toggle="tab" href="#tab_chequeras">Chequeras</a></li>
    </ul>

    <div class="tab-content" style="padding-top: 20px;">
        <div id="tab_datos" class="tab-pane fade in active">
            <div class="container-fluid">
                <div class="marco_formulario">
                    <div class="container">
                        <h4>Consulta</h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row" style="padding:5px;"> <b> ID: </b> {{$registro->id}} </div>
                                <?php VistaController::campos_dos_colummnas($form_create['campos'], 'show'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab_chequeras" class="tab-pane fade">
            <div style="margin-bottom: 12px;">
                <a class="btn btn-primary btn-sm" href="{{ url('teso_cuentas_bancarias/'.$registro->id.'/chequeras/create?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion')) }}">
                    <i class="fa fa-plus"></i> Nueva chequera
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th>Número inicial</th>
                            <th>Número final</th>
                            <th>Consecutivo actual</th>
                            <th>Estado</th>
                            <th style="width: 130px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($chequeras as $chequera)
                            <tr>
                                <td>{{ $chequera->descripcion }}</td>
                                <td>{{ $chequera->numero_inicial }}</td>
                                <td>{{ $chequera->numero_final }}</td>
                                <td>{{ $chequera->consecutivo_actual }}</td>
                                <td>{{ $chequera->estado }}</td>
                                <td>
                                    <a class="btn btn-warning btn-xs" title="Editar" href="{{ url('teso_cuentas_bancarias/'.$registro->id.'/chequeras/'.$chequera->id.'/edit?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion')) }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    {{ Form::open(['url' => 'teso_cuentas_bancarias/'.$registro->id.'/chequeras/'.$chequera->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion'), 'method' => 'DELETE', 'style' => 'display:inline;']) }}
                                        <button class="btn btn-danger btn-xs" type="submit" title="Eliminar" onclick="return confirm('¿Eliminar chequera?')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    {{ Form::close() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No hay chequeras creadas para esta cuenta.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
