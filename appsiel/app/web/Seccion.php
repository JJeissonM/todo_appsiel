<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
  protected $table = 'pw_seccion';
  protected $fillable = ['id', 'nombre', 'descripcion', 'preview', 'created_at', 'updated_at'];

  public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombre', 'Descripción', 'URL imágen'];

  public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","imprimir":"no","eliminar":"no","cambiar_estado":"no"}';

  public function widgets()
  {
    return $this->hasMany(Widget::class, 'seccion_id');
  }


  // METODO PARA LA VISTA INDEX
  public static function consultar_registros($nro_registros, $search)
  {
    return Seccion::select(
      'nombre AS campo1',
      'descripcion AS campo2',
      'preview AS campo3',
      'id AS campo4'
    )->where("nombre", "LIKE", "%$search%")
      ->orWhere("descripcion", "LIKE", "%$search%")
      ->orWhere("preview", "LIKE", "%$search%")
      ->orderBy('created_at', 'DESC')
      ->paginate($nro_registros);
  }

  public static function sqlString($search)
  {
    $string = Seccion::select(
      'nombre AS NOMBRE',
      'descripcion AS DESCRIPCIÓN',
      'preview AS URL_IMAGEN'
    )->where("nombre", "LIKE", "%$search%")
      ->orWhere("descripcion", "LIKE", "%$search%")
      ->orWhere("preview", "LIKE", "%$search%")
      ->orderBy('created_at', 'DESC')
      ->toSql();
    return str_replace('?', '"%' . $search . '%"', $string);
  }

  //Titulo para la exportación en PDF y EXCEL
  public static function tituloExport()
  {
    return "LISTADO DE SECCIONES";
  }
}
