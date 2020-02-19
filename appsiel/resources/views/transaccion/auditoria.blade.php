<div style="text-align: right;">
    Creado por: {{ explode('@',$doc_encabezado->creado_por)[0] }}, {{ $doc_encabezado->created_at }}
    @if( $doc_encabezado->modificado_por != 0)
	    <br>
	    Modificado por: {{ explode('@',$doc_encabezado->modificado_por)[0] }}
	@endif
</div>