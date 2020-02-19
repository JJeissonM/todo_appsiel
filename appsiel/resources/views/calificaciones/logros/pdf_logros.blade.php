<style>
th, td {
    border-bottom: 1px solid #ddd;
}

th {
	background-color: #CACACA;
}

h2 {
	text-align:center;
}

.page-break {
    page-break-after: always;
}
</style>

<div class="container">
  <h2>Listado de logros de <strong>{{$nom_asignatura}}</strong></h2>
  <table class="table table-striped" width="100%">
    <thead>
      <tr>
        <th width="25px">Código</th>
        <th>Descripción</th>
      </tr>
    </thead>
    <tbody>
	  @foreach ($registros as $campo)
		  <tr>
			<td>{{ $campo->codigo }}</td>
			<td style="text-align: justify;">{{ $campo->descripcion }}</td>
		  </tr>
	  @endforeach
    </tbody>
  </table>
</div>