<br/>
<?php if( !is_null($matriculas) ){ ?>
        <div class="panel panel-info">
            <div class="panel-heading" align="center">
                Matrículas Anteriores
            </div>

            {{ Form::bsBtnCreate( url('web/create?id=1&id_modelo=281&id_transaccion='), '_blank' ) }}
            {{ Form::bsBtnExcel( 'Historial de matriculas' ) }}

            <div class="panel-body">
                <table class="table table-striped">
                    {{ Form::bsTableHeader(['Código','Año lectivo','Estudiante','Curso','Estado','Imprimir','']) }}
                    <tbody>
                    @foreach ($matriculas as $registro)
                        <?php
                            if($registro->estado=="Activo"){
                                $clase = ".danger";
                                $mensaje = '<div class="alert alert-warning">
                                  <strong>Advertencia!</strong> Al crear una nueva matrícula, esta será inactivada.
                                </div>';
                            }else{
                                $clase = ".warning";
                                $mensaje = "";
                            }
                        ?>
                        <tr class="{{$clase}}">
                            <td class="table-text text-center"><div>{{ $registro->codigo }}</div></td>
                            <td class="table-text"><div>{{ $registro->descripcion }}</div></td>
                            <td class="table-text"><div>{{ $registro->estudiante->tercero->descripcion }}</div></td>
                            <td class="table-text"><div>{{ $registro->nombre_curso }}</div></td>
                            <td class="table-text"><div>{{ $registro->estado }}</div></td>
                            <td class="table-text"><div><a class="btn btn-info btn-xs btn-detail" href="{{ url('matriculas/imprimir/'.$registro->id) }}"target="_blank" id="btn_imprimir"><i class="fa fa-btn fa-print"></i>&nbsp;</a></td>
                            <td class="table-text"><div>{!!$mensaje!!}</div> </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
<?php } ?>