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
            					<td colspan="2"><b>Estudiante: </b> {{ $registro->nombre_completo }} </td>
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

            					    <b>Contraseña: </b>{{ $passwd }}
            					</td>
            					<td>
            					    NOTA: Debe cambiar la contraseña. Ingresando en la parte superior derecha. Hace clic en el <b>Nombre del estudiante</b>, luego en <b>Perfil</b> y luego en <b>Cambiar Contraseña</b>
            					</td>
            				</tr>
            		</table>
            	@endforeach
		<div class="page-break"></div>
	@endfor
</div>