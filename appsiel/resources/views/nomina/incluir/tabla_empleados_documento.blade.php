<div class="container-fluid">
    <br/><br/>
    
        <div class="row">
            <div class="col-md-12">
                {!! $tabla !!}
            </div>
        </div>

    {{ Form::open(array('url'=>'nom_guardar_asignacion')) }}
        <div class="row">
            <div class="col-md-8 col-md-offset-2" style="vertical-align: center; border: 1px solid gray;">
                <h3>Asignar nuevo</h3>
                <div class="row">
                    <div class="col-md-6">
                        {{ Form::bsSelect('registro_modelo_hijo_id',null,$titulo_tab,$opciones,['class'=>'combobox']) }}
                    </div>
                    <div class="col-md-6">
                        {{ Form::bsText('nombre_columna1',null,'Orden',[]) }}
                    </div>
                    {{ Form::hidden('registro_modelo_padre_id',$registro_modelo_padre_id) }}

                    {{ Form::hidden('url_id',Input::get('id'))}}
                    {{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
                    {{ Form::hidden('url_id_transaccion',Input::get('id_transaccion'))}}
                </div>
                <div align="center">
                    <br/>
                    {{ Form::submit('Guardar', array('class' => 'btn btn-primary btn-sm')) }}
                </div>
                <br/><br/>
            </div>
        </div>
    {{ Form::close() }}
</div>