<table class="table table-striped">
	<tr>
		<td>
			<?php  
				$unwanted_array = array('À'=>'A', 'Á'=>'A', 'È'=>'E', 'É'=>'E',
                                'Ì'=>'I', 'Í'=>'I', 'Ò'=>'O', 'Ó'=>'O', 'Ù'=>'U',
                                'Ú'=>'U', 'à'=>'a', 'á'=>'a', 'è'=>'e', 'é'=>'e', 'ì'=>'i', 'í'=>'i', 'Ñ'=>'N', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ù'=>'u', 'ú'=>'u' );
			?>
			<div class="container">
				@for($k=0;$k < count($estudiantes) ;$k++)
					<!-- TITULOS -->
					<div align="center"> <b> Lista de usuarios de estudiantes </b> </div>
					<b>Grado: </b> {{ $estudiantes[$k]['grado'] }}
					<b>Curso: </b> {{ $estudiantes[$k]['curso'] }}
					<br><br>
							@foreach ($estudiantes[$k]['listado'] as $registro)
								<table class="table">
										<tr>
											<td colspan="2"><b>Estudiante: </b> {{ strtr( $registro->nombre_completo, $unwanted_array) }} </td>
										</tr>
										<tr>
											<td class="celda1" width="400px"> 
												<b>Enlace plataforma: </b> {{ url('inicio') }}
												<br>
												<b>Usuario: </b>{{ $registro->email }}
												<br>

												<?php 
													$passwd = App\Core\PasswordReset::where('email',$registro->email )->get()->first();
													
													if( !is_null( $passwd ) )
													{
														$passwd = $passwd->token;
													}else{
														$passwd = '';
													}
													
												?>

												<b>Contrasena: </b>{{ $passwd }}
											</td>
											<td>
												NOTA: Debe cambiar la contrasena. Ingresando en la parte superior derecha. Hace clic en el <b>Nombre del estudiante</b>, luego en <b>Perfil</b> y luego en <b>Cambiar Contrasena</b>
											</td>
										</tr>
								</table>
								<br>
							@endforeach
					<div class="page-break"></div>
				@endfor
			</div>
		</td>
	</tr>
</table>