@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}
	
    <?php  
        $ingredientes = $registro->ingredientes();

        $btn_nuevo = '';//'<button class="btn btn-primary btn-sm btn_agregar_precio" type="button"><i class="fa fa-plus"></i> Agregar nuevo ingrediente </button> ';

        $reg_anterior = \App\Inventarios\RecetaCocina::where([
                                                        ['id', '<', $registro->id],
                                                        ['item_platillo_id', '<>', $registro->item_platillo_id]
                                                    ])
                                                    ->max('id');
        
        $reg_siguiente = \App\Inventarios\RecetaCocina::where([
                                                        ['id', '>', $registro->id],
                                                        ['item_platillo_id', '<>', $registro->item_platillo_id]
                                                    ])
                                                    ->min('id');

		$platillos = \App\Inventarios\RecetaCocina::opciones_campo_select();
    ?>

	<div class="row">
		<div class="col-md-4">
			&nbsp;&nbsp;&nbsp;
			<div class="btn-group">
				@if( isset($url_crear) )
					@if($url_crear!='')
						{{ Form::bsBtnCreate($url_crear) }}
					@endif
				@endif
			</div>
		</div>

		<div class="col-md-4">
			&nbsp;&nbsp;&nbsp;
			<div class="btn-group">
				<div style="vertical-align: center;">
					<br/>
					{{ Form::label('item_platillo_id','Platillo:') }}
					{{ Form::select('item_platillo_id',$platillos,null,['class'=>'combobox','id'=>'item_platillo_id']) }}
					&nbsp;&nbsp;&nbsp;
					<a class="btn btn-info btn-xs cambiar_platillo"><i class="fa fa-arrow-right"></i> Cambiar </a>
				</div>
			</div>
		</div>

		<div class="col-md-4">
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

	<div class="container-fluid">
		<div class="marco_formulario">
		    <div class="container">
			    <h3 style="width: 100%;text-align: center;">Ingredientes para la receta "{{ $registro->item_platillo->descripcion }}"</h3>
			    <div class="row">
			    	<div class="col-md-4" style="padding:5px;"> 
			    		<b>Unid. Medida: </b> {{ $registro->item_platillo->unidad_medida1 }}
			    	</div>
			    	<div class="col-md-4" style="padding:5px;"> 
			    		<b>Categoría: </b> {{ $registro->item_platillo->grupo_inventario->descripcion }}
			    	</div>
			    	<div class="col-md-4" style="padding:5px;"> 
			    		{!! $btn_nuevo !!}
			    	</div>			    	
			    </div>
			    <hr>

			    <div class="row">
			    	<div class="table-responsive" id="table_content">
						<table class="table table-bordered table-striped" id="myTable">
							<thead>
								<tr>
									<th>ID item ingred.</th>
									<th>Ingrediente (U.M.)</th>
									<th>Cant. x una(1) porción</th>
									<th>Costo unit.</th>
									<th>Costo total</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
                                <?php
                                    $sum_costo_total = 0;
                                ?>
								@foreach( $ingredientes as $linea)
                                    <?php
                                        $sum_costo_total += $linea['cantidad_porcion'] * $linea['ingrediente']->get_costo_promedio(0); 

                                        $string_search_platillo = $registro->item_platillo->descripcion;
                                    ?>
                                    <tr>
										<td>{{ $linea['ingrediente']->id }}</td>
										<td>{{ $linea['ingrediente']->descripcion }} ({{ $linea['ingrediente']->unidad_medida1 }})</td>
										<td align="center">{{ number_format( $linea['cantidad_porcion'], 2, ',', '.') }}</td>
										<td align="right">${{ number_format( $linea['ingrediente']->get_costo_promedio(0), 2, ',', '.') }}</td>
										<td align="right">${{ number_format( $linea['cantidad_porcion'] * $linea['ingrediente']->get_costo_promedio(0), 2, ',', '.') }}</td>
										<td>
											<a class="btn btn-warning btn-xs btn-detail" href="{{ url( 'web/'.$linea['id'].'/edit?id=8&id_modelo=321&id_transaccion=' ) }}" title="Modificar"><i class="fa fa-btn fa-edit"></i>&nbsp;</a>
                                            &nbsp;
                                            <a class="btn btn-danger btn-xs btn-detail eliminarElement" data-linea_receta_id="{{$linea['id']}}" data-ruta_redirect_completa="{{urlencode('web?id=8&id_modelo=321&curso_id=&asignatura_id=&search='.$string_search_platillo)}}" title="Eliminar"><i class="fa fa-trash"></i></a>
										</td>
									</tr>
								@endforeach
                                <tr>
                                    <td> &nbsp; </td>
                                    <td> &nbsp; </td>
                                    <td> &nbsp; </td>
                                    <td> &nbsp; </td>
                                    <td align="right"> ${{ number_format( $sum_costo_total, 2, ',', '.') }}</td>
                                    <td> &nbsp; </td>
                                </tr>
							</tbody>
						</table>
					</div>
			    </div>
				<br>
                {!! $btn_nuevo !!}
			</div>

			<br/><br/>

		</div>
	</div>

@endsection

@section('scripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$(".eliminarElement").click(function(event){
                event.preventDefault();
			    var url = "{{url('')}}" + "/" + "web_eliminar/" + $(this).attr('data-linea_receta_id') + "?id=8&id_modelo=321&id_transaccion=&ruta_redirect_completa=" + $(this).attr('data-ruta_redirect_completa');
                location.href = url;
		    });

			$(".cambiar_platillo").click(function(event){
				event.preventDefault();		

				var url = "{{url('')}}" + "/" + "web/" + $('#item_platillo_id').val() + "?id=8&id_modelo=321&id_transaccion=";
				location.href = url;
			});


		});
	</script>
@endsection