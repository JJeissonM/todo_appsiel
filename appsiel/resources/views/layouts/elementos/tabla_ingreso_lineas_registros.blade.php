<div id="div_ingreso_registros">
	<br/>
    @if( $datos['titulo'] != '' )
        <h4>{{ $datos['titulo'] }} <small style="color: red;"> &#171;En cada campo presione Enter para continuar.&#187;</small></h4>
        <hr>
    @endif
    <div class="table-responsive" id="table_content">
        <table class="table table-striped" id="ingreso_registros">
            <thead>
                <tr>
                	<?php 
                		$thead = '';
                		$cant = count( $datos['columnas'] );
                		$cols_invisibles = 0;
                		for ($i=0; $i < $cant; $i++) 
                		{ 

                			if ( $datos['columnas'][$i]['name'] == '') 
                			{
                				$data_override = '';
                			}else{
                				$data_override = ' data-override="'.$datos['columnas'][$i]['name'].'"';
                			}

                            $style = '';
                            if ( $datos['columnas'][$i]['display'] == 'none') 
                            {
                                $style = ' style="display: none;"';
                            }else{
                                $cols_invisibles++;
                            }

                            $width = '';
                            if ( $datos['columnas'][$i]['width'] != '') 
                            {
                                $width = ' width="'.$datos['columnas'][$i]['width'].'"';
                            }    			 

                			$thead .= '<th'.$data_override.$style.$width.'>'.$datos['columnas'][$i]['etiqueta'].'</th>';
                			
                		}

                		echo $thead;
                	?>
                </tr>
            </thead>
            <tbody>
                {!! $datos['fila_body'] !!}
            </tbody>
            <tfoot>
                {!! $datos['fila_foot'] !!}
            </tfoot>
        </table>
    </div>
</div>