<?php 
  $datos = [
            ['titulo' => 'MISIÓN', 'contenido' => 'Facilitar la vida de nuestros clientes a través de productos y servicios de calidad que les ayuden a alcanzar sus objetivos.'],
            ['titulo' => 'VISIÓN', 'contenido' => 'Para el 2023, AppSiel estará posicionada entre las 100 mejores empresas de Colombia, reconocida por la calidad de sus productos y servicios, la felicidad de sus empleados y la satisfacción de sus clientes.'],
            ['titulo' => 'VALORES', 'contenido' => 'Nuestros valores nos impulsan a dar lo mejor: 
                <ul style="list-style-type: none;">
                <li>&#10084; Amabilidad</li>
                <li>&#9994; Pasión</li>
                <li>&#10166; Persistencia</li>
                <li>&#9472; Sencillez</li>
                <li>&#9670; Integridad</li>
                <li>&#10032; Excelencia</li>
                <li>&#9822; Liderazgo</li>
                </ul>']
          ];
  $cant = count($datos);
?>

@for($i=0;$i<$cant;$i++)
  <button class="accordion">
    {{$datos[$i]['titulo']}}
    <a href="#" class="close" data-dismiss="alert" aria-label="close">&plus;</a>
  </button>
  <div class="panel_acordion">
    <p>{!! $datos[$i]['contenido'] !!}</p>
  </div>
@endfor