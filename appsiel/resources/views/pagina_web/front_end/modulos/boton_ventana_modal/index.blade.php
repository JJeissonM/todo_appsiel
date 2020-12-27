<button type="button" class="{{ $clase_html }}" style="{{ $estilo_css }}" id="{{ $id_html }}"  data-toggle="modal" data-target="#myModal" >
  <i class="fa fa-{{ $fa_icon }}"></i>
  {{ $texto_boton }}
</button>
            
@include( 'components.design.ventana_modal', [ 
                      'titulo' => $titulo_modal,
                      'texto_mensaje' => $texto_mensaje_modal,
                      'contenido_modal' => $contenido_modal
                    ]
        )