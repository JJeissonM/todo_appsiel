
		<?php if(count($matriculas_inactivas>0)){?>
		<div class="col-sm-offset-1 col-sm-10">
			<div class="panel panel-warning">
				<div class="panel-heading" align="center">
					Matrículas Anteriores de <?php echo $estudiante->nombres; ?>
				</div>

				<div class="panel-body">
					<table class="table table-striped estudiante-table">
						{{ Form::bsTableHeader(['Código','Año lectivo','Curso','Acudiente','Estado','Imprimir']) }}
						<tbody>
						@foreach ($matriculas_inactivas as $registro)
						<?php
                                $nom_curso2 = DB::table('sga_cursos')->where('id', '=', $registro->curso_id)->value('descripcion');
                            ?>
						<tr>
							<td class="table-text"><div>{{ $registro->codigo }}</div></td>
							<td class="table-text"><div>{{ $registro->anio }}</div></td>
							<td class="table-text"><div>{{ $nom_curso2 }}</div></td>
							<td class="table-text"><div>{{ $registro->acudiente }}</div></td>
							<td class="table-text"><div>{{ $registro->estado }}</div></td>
							<td class="table-text"><div><a class="btn btn-info btn-xs btn-detail" href="{{ url('matriculas/imprimir/'.$registro->id) }}"target="_blank"><i class="fa fa-btn fa-print"></i>&nbsp;</a></td>
						</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
        <?php }?>