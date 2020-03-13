<?php 
	$passwd = App\Core\PasswordReset::where('email',$estudiante->email )->get()->first();
	
	if( !is_null( $passwd ) )
	{
		$passwd = $passwd->token;
	}else{
		$passwd = '';
	}
	
?>
@if( $passwd != '' )
	<div style="width: 100%; border: 1px solid; font-size: 0.9em;">
		Datos de acceso a la plataforma:
		<br>
		<b> Enlace: </b> {{ url('/inicio') }}
		<br>
		<b> Usuario: </b> {{ $estudiante->email }}
		&nbsp;&nbsp; <b> Contraseña: </b> {{ $passwd }}
	</div>
@endif