@extends('layouts.principal')

@section('content')
    @php
        use App\Http\Controllers\Sistema\VistaController;

        $es_tarjeta_bancaria = $registro->comportamiento === 'Tarjeta bancaria';
        $tab_relaciones_label = $es_tarjeta_bancaria ? 'Cuentas Bancarias' : 'Cajas';

        $relaciones = $registro->destinos()
            ->with(['caja', 'cuenta_bancaria.entidad_financiera'])
            ->orderBy('id', 'DESC')
            ->get();

        $ids_cajas_relacionadas = $relaciones->pluck('teso_caja_id')->filter()->all();
        $ids_cuentas_relacionadas = $relaciones->pluck('teso_cuenta_bancaria_id')->filter()->all();

        $cajas_disponibles = App\Tesoreria\TesoCaja::get_cajas_permitidas()
            ->reject(function ($caja) use ($ids_cajas_relacionadas) {
                return in_array($caja->id, $ids_cajas_relacionadas);
            });

        $cuentas_disponibles = App\Tesoreria\TesoCuentaBancaria::get_cuentas_permitidas()
            ->reject(function ($cuenta) use ($ids_cuentas_relacionadas) {
                return in_array($cuenta->id, $ids_cuentas_relacionadas);
            });
    @endphp

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
                    {{ Form::bsBtnPrev('web/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
                @endif

                @if($reg_siguiente!='')
                    {{ Form::bsBtnNext('web/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
                @endif
            </div>
        </div>
    </div>
    <hr>

    @include('layouts.mensajes')

    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#tab_datos">Datos básicos</a></li>
        <li><a data-toggle="tab" href="#tab_relaciones">{{ $tab_relaciones_label }}</a></li>
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

        <div id="tab_relaciones" class="tab-pane fade">
            <div class="marco_formulario">
                <div class="container">
                    <h4>{{ $tab_relaciones_label }} relacionadas para "{{ $registro->descripcion }}"</h4>
                    <hr>

                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-8">
                            {{ Form::open(['url' => 'teso_medios_recaudo/'.$registro->id.'/destinos?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'), 'class' => 'form-inline']) }}
                                @if($es_tarjeta_bancaria)
                                    <div class="form-group">
                                        <label for="teso_cuenta_bancaria_id" style="margin-right: 10px;">Cuenta bancaria</label>
                                        <select name="teso_cuenta_bancaria_id" id="teso_cuenta_bancaria_id" class="form-control" required>
                                            <option value="">Seleccionar...</option>
                                            @foreach($cuentas_disponibles as $cuenta)
                                                <option value="{{ $cuenta->id }}">{{ $cuenta->entidad_financiera }} - {{ $cuenta->descripcion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <div class="form-group">
                                        <label for="teso_caja_id" style="margin-right: 10px;">Caja</label>
                                        <select name="teso_caja_id" id="teso_caja_id" class="form-control" required>
                                            <option value="">Seleccionar...</option>
                                            @foreach($cajas_disponibles as $caja)
                                                <option value="{{ $caja->id }}">{{ $caja->descripcion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa fa-plus"></i> Agregar
                                </button>
                            {{ Form::close() }}
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ $tab_relaciones_label }}</th>
                                    <th style="width: 130px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($relaciones as $relacion)
                                    <tr>
                                        <td>
                                            @if($es_tarjeta_bancaria)
                                                {{ $relacion->cuenta_bancaria && $relacion->cuenta_bancaria->entidad_financiera ? $relacion->cuenta_bancaria->entidad_financiera->descripcion : '' }} - {{ $relacion->cuenta_bancaria ? $relacion->cuenta_bancaria->descripcion : '' }}
                                            @else
                                                {{ $relacion->caja ? $relacion->caja->descripcion : '' }}
                                            @endif
                                        </td>
                                        <td>
                                            {{ Form::open(['url' => 'teso_medios_recaudo/'.$registro->id.'/destinos/'.$relacion->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'), 'method' => 'DELETE', 'style' => 'display:inline;']) }}
                                                <button class="btn btn-danger btn-xs" type="submit" title="Eliminar" onclick="return confirm('¿Eliminar relación?')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            {{ Form::close() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2">No hay {{ strtolower($tab_relaciones_label) }} relacionadas para este medio de recaudo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
