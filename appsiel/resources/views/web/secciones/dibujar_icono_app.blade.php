<?php 
  $url_icono = "https://appsiel.com.co/el_software/assets/img/".$fila['nombre_imagen'];
?>
<div class="col-sm-{{(12/$cant_cols)}}" style="border: solid 1px #F4F4F4; padding: 10px;">
  <div class="row">

    <div class="col-sm-4">
      <img class="img-responsive" src="{{$url_icono}}" title="{{ $fila['definicion'] }}"/>
    </div>

    <div class="col-sm-6">
      <div class="lbl_descripcion_app">
        {{$fila['descripcion']}}
      </div>
      @if($fila['tipo_precio']=='Fijo')
        <div class="lbl_precio_app">
          ${{number_format($fila['precio'], 0, ',', '.')}} COP / mes
        </div>
      @else
        <div class="lbl_precio_app">
          <input type="hidden" id="precio-{{$fila['id']}}" value="{{$fila['precio']}}">
          Precio Unitario: ${{number_format($fila['precio'], 0, ',', '.')}} 
          <br/>
          Cantidad: <div style="display: inline;"> <input type="number" id="cantidad-{{$fila['id']}}" value="1" min="1" style="border: none;border-color: transparent;border-bottom: 1px solid gray; display: inline-block;"> </div> 
          <br/>
          Total: <div id="lbl_sub_total-{{$fila['id']}}" style="display: inline;">
                    ${{number_format($fila['precio'], 0, ',', '.')}}
                  </div>
        </div>
      @endif
    </div>

    <div class="col-sm-2">
      <input type="checkbox" id="sub_total-{{$fila['id']}}" class="seleccionar" value="{{$fila['precio']}}">
    </div>    
  </div>
</div>