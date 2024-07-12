<table class="table table-striped">
	<tr>
		<td>
			<?php  
				$unwanted_array = array('À'=>'A', 'Á'=>'A', 'È'=>'E', 'É'=>'E',
                                'Ì'=>'I', 'Í'=>'I', 'Ò'=>'O', 'Ó'=>'O', 'Ù'=>'U',
                                'Ú'=>'U', 'à'=>'a', 'á'=>'a', 'è'=>'e', 'é'=>'e', 'ì'=>'i', 'í'=>'i', 'Ñ'=>'N', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ù'=>'u', 'ú'=>'u' );
				

				$cantidad_cursos = count($estudiantes);
				
				$cant_x_pagina = 6;
				if ( $tam_hoja == 'Legal') {
					$cant_x_pagina = 8;
				}
				
			?>

			@for($index_curso = 0; $index_curso < $cantidad_cursos; $index_curso++)

				<?php
					$cant_x_pagina = 6;
					if ( $tam_hoja == 'Legal') {
						$cant_x_pagina = 8;
					}

					$estudiantes_del_curso = $estudiantes[$index_curso]['listado'];

					$cantidad_estudiantes = count($estudiantes_del_curso);
					
					$division_aux = $cantidad_estudiantes / 6;
					
					$cant_paginas = intval( $division_aux ) + 1;
					
					$index_estudiante = 0;
				?>
				@for($pagina = 0; $pagina < $cant_paginas; $pagina++)
					<div class="container">
						<!-- TITULOS -->
						<div align="center"> <b> Lista de usuarios de estudiantes </b> </div>
						<b>Grado: </b> {{ $estudiantes[$index_curso]['grado'] }}
						<b>Curso: </b> {{ $estudiantes[$index_curso]['curso'] }}
						<br><br>
						@foreach ( $estudiantes_del_curso as $registro)
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
							<?php
								$index_estudiante++; 
							?> 
							@if ( $index_estudiante == $cant_x_pagina )
								<div class="page-break"></div>
								
								<?php
									$index_estudiante = 0; // Reset
								?> 
							@endif
						@endforeach
					</div>

					<div class="page-break"></div>
				@endfor
			@endfor
		</td>
	</tr>
</table>