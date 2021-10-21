<div style="text-align: center;">
    <b>{{ $empresa->nombre1 }} {{ $empresa->otros_nombres }} {{ $empresa->apellido1 }} {{ $empresa->apellido2 }}</b>
    <br>
    <b>{{ config("configuracion.tipo_identificador") }}:
        @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}	@else {{ $empresa->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}</b><br/>
    {{ $empresa->direccion1 }}, {{ $ciudad->descripcion }} <br/>
    Teléfono(s): {{ $empresa->telefono1 }}
    @if( $empresa->pagina_web != '' )
        <br/>
        <b style="color: blue; font-weight: bold;">{{ $empresa->pagina_web }}</b>
    @endif
    @if( $empresa->email != '' )
        <br/>
        <b>Email:</b> <b style="color: blue; font-weight: bold;">{{ $empresa->email }}</b><br/>
    @endif
</div>