<table class="matriz_dofa">
	<tr>
		<td style="text-align: center; width: 250px;">
			<h4><b>Factores internos</b></h4>
		</td>
		<td style="text-align: center; width: 250px;">
			<h4><b>Factores externos</b></h4>
		</td>
	</tr>
	<tr style="height: 150px;">
		<td class="gota1">				
			<h5> <b> Debilidades</b> </h5>
			@include('terceros.analisis_dofa.dibujar_lista_elementos', ['tipo_caracteristica' => 'Debilidad', 'lista_items' => $registros_analisis])
		</td>
		<td class="gota2">
			<h5><b>Oportunidades</b></h5>
			@include('terceros.analisis_dofa.dibujar_lista_elementos', ['tipo_caracteristica' => 'Oportunidad', 'lista_items' => $registros_analisis])
		</td>
	</tr>
	<tr style="height: 150px;">
		<td class="gota3">
			@include('terceros.analisis_dofa.dibujar_lista_elementos', ['tipo_caracteristica' => 'Fortaleza', 'lista_items' => $registros_analisis])
			<h5><b>Fortalezas</b></h5>
		</td>
		<td class="gota4">
			@include('terceros.analisis_dofa.dibujar_lista_elementos', ['tipo_caracteristica' => 'Amenaza', 'lista_items' => $registros_analisis])
			<h5><b>Amenazas</b></h5>
		</td>
	</tr>
</table>