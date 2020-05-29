<!DOCTYPE html>

<style type="text/css">
  .label{
    font-weight: bold;
    background: #ddd;
    display: block;
  }  

</style>

<html lang="es">
    <body>
        
        Saludos {{$empresa->descripcion}}
        <br> 
        Alguien dej&oacute; un comentario desde la p&aacute;gina web. 
        <br><br>
        
        @foreach( $campos AS $fila )
          <p>
            <span class="label"> {{ $fila[0] }} </span> 
            {{ $fila[1] }} 
          </p>
        @endforeach


        <p>
          <span class="label">Fecha y hora de envío: </span> 
          {{ $request->fecha_hora }} 
        </p>
        
    </body>
</html>'