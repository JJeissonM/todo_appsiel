<table class="table table-bordered">    
    <thead>
        <tr>
            <th>Diagnóstico principal</th>
            <th>Código CIE</th>
            <th>Tipo de diagnóstico</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach( $diagnosticos AS $diagnostico )
            <tr>
                <td> {{ $diagnostico->get_fields_to_show()->es_diagnostico_principal->value }} </td>
                <td> {{ $diagnostico->get_fields_to_show()->codigo_cie->value }} </td>
                <td> {{ $diagnostico->get_fields_to_show()->tipo_diagnostico_principal->value }} </td>
                <td> {{ $diagnostico->get_fields_to_show()->observaciones->value }} </td>
            </tr>
        @endforeach
    </tbody>
</table>