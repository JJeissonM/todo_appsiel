<div class="row">
    <div class="col-md-4">
        <div class="btn-group">
            @if( isset($url_crear) )
                @if($url_crear!='')
                    {{ Form::bsBtnCreate($url_crear) }}
                @endif
            @endif

            @if( isset($url_edit) )
                @if( $url_edit!='' && !auth()->user()->hasPermissionTo('vtas_pos_bloqueo_ver_movimientos_sistema_en_arqueo_caja') )
                    {{ Form::bsBtnEdit2(str_replace('id_fila', $registro->id, $url_edit),'Editar') }}
                @endif
            @endif
            @if(isset($botones))
                @php
                    $i=0;
                @endphp
                @foreach($botones as $boton)
                    {!! str_replace( 'id_fila', $registro->id, $boton->dibujar() ) !!}
                    @php
                        $i++;
                    @endphp
                @endforeach
            @endif
        </div>
    </div>
    <div class="col-md-4 text-center">
        <div class="btn-group">
            <!--Imprimir
            {{ Form::bsBtnPrint( 'tesoreria/imprimir/'.$registro->id ) }}-->
            Formato: {{ Form::select('formato_impresion_id',[ 'estandar' => 'Estándar', 'pos' => 'POS', 'pos58mm' => 'POS 58mm'], null, [ 'id' =>'formato_impresion_id' ]) }}
            {{ Form::bsBtnPrint( 'tesoreria/imprimir/'.$registro->id.'?formato_impresion_id=0' ) }}
        </div>
    </div>
    <div class="col-md-4">
        <div class="btn-group pull-right">
            @if($reg_anterior!='')
                {{ Form::bsBtnPrev('tesoreria/arqueo_caja/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
            @endif

            @if($reg_siguiente!='')
                {{ Form::bsBtnNext('tesoreria/arqueo_caja/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
            @endif
        </div>
    </div>
</div>