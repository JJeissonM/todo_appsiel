<!DOCTYPE html>
<html lang="es">
    <body>
        Saludos {{$empresa->descripcion}}
        <br> 
        Alguien dej&oacute; un comentario desde la p&aacute;gina web. 
        <br><br>
        
        <table style="boder: solid 1px; border-collapse: collapse;">
          <thead>
            <tr style="boder: font-weight: bold;">
              <th style="boder: solid 1px; border-collapse: collapse;">Nombre</th>
              <th style="boder: solid 1px; border-collapse: collapse;">Email</th>
              <th style="boder: solid 1px; border-collapse: collapse;">Teléfono</th>
              <th style="boder: solid 1px; border-collapse: collapse;">Ciudad</th>
              <th style="boder: solid 1px; border-collapse: collapse;">Comentarios</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="boder: solid 1px; border-collapse: collapse;">{{$request->nombre}}</td>
              <td style="boder: solid 1px; border-collapse: collapse;">{{$request->email}}</td>
              <td style="boder: solid 1px; border-collapse: collapse;">{{$request->telefono}}</td>
              <td style="boder: solid 1px; border-collapse: collapse;">{{$request->ciudad}}</td>
              <td style="boder: solid 1px; border-collapse: collapse;">{{$request->comentarios}}</td>
            </tr>
          </tbody>
        </table>
        
    </body>
</html>'