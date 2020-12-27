@if( $mostrar_usuarios_estudiantes == 'Si') 

	<?php

		if( !is_null( $registro->password_estudiante ) )
		{
			$passwd = $registro->password_estudiante->token;
		}else{
			$passwd = '**********';
		}
		
	?>
	@if( $passwd != '' )
		<div style="width: 100%; border: 1px solid; font-size: 0.9em;">
			Datos de acceso a la plataforma:
			<br>
			<b> Enlace: </b> {{ url('/inicio') }}
			<br>
			<b> Usuario: </b> {{ $registro->estudiante->tercero->email }}
			&nbsp;&nbsp; <b> Contraseña: </b> {{ $passwd }}
		</div>
	@endif
@endif