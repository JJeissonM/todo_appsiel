
	&nbsp;&nbsp;&nbsp;


	<ul class="nav navbar-nav">
        <li class="dropdown">
	        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Acciones <span class="caret"></span></a>
	        <ul class="dropdown-menu sub-menu">
	            <li class="dropdown">
			        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> Liquidación automática <span class="caret"></span></a>
			        <ul class="dropdown-menu sub-menu">
			            <li> <a href="#"> Todos </a> </li>
			        </ul>
			    </li>
	        </ul>
	    </li>
    </ul>

<div class="dropdown" style="display:inline-block;">
    <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
    	<i class="fa fa-money"></i> 
    	Acciones
    	<span class="caret"></span>
	</button>
    <ul class="dropdown-menu">

			
			<li>
				{{ Form::bsBtnDropdown( 'Acciones', 'success', 'money', 
		          [ 
		            ['link' => 'nomina/liquidacion/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'), 
		            'etiqueta' => 'Liquidación automática'], 
		            ['link' => 'nomina/retirar_liquidacion/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'), 'etiqueta' => 'Retirar registros automáticos' ]
		          ] ) }}
			</li>


			<li>
				<a href="{{ url( 'nomina/retirar_liquidacion/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}"> 
					Retirar registros automáticos 
				</a>
			</li>

	</ul>
 </div>