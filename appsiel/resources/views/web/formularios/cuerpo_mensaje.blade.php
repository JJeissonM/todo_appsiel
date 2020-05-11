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
        
        <p>
          <span class="label">Nombre o razón social: </span> 
          {{$request->nombre }} 
        </p>
        <p>
          <span class="label">Documento identidad: </span> 
          {{$request->tipo_documento_identidad }} no. {{ number_format( (int)$request->numero_identificacion, 0, ',', '.') }} 
        </p>
        <p>
          <span class="label">Email: </span> 
          {{$request->email }} 
        </p>
        <p>
          <span class="label">Tipo de solicitud: 
          </span> 
          {{$request->tipo_solicitud }} 
        </p>
        <p>
          <span class="label">Asunto: 
          </span> 
          {{$request->asunto }} </p>
        <p>
          <span class="label">Comentario: 
          </span> 
          {{$request->comentario }} 
        </p>
        <p>
          <span class="label">Fecha y hora de envío: </span> 
          {{$request->fecha_hora }} 
        </p>
        
    </body>
</html>'