<div class="row">
	<div class="col-sm-12">
		<h3>Ayuda</h3>
						@php $campos = App\Core\DifoCamposSeccion::all(); @endphp
						<pre class="pre-scrollable"><b>Palabras Claves</b><br/>Puede usar las siguientes palabras claves para agregar al <b><u>Contenido</u></b> de 
las secciones. Estas serán reemplazadas al momento de generar 
la sección.<ul>
	@foreach($campos as $fila)
		<li>{{ $fila->descripcion }}</li>
	@endforeach
</ul></pre></div>
</div>
