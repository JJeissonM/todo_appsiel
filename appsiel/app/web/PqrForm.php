<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\VistaController;

use App\Sistema\Campo;

class PqrForm extends Model
{
    protected $table = 'pw_pqr_form';

    // Por lo pornto el campo "parametros" está almacenando el email en el que se recibiran los correos
    protected $fillable = [ 'contenido_encabezado', 'contenido_pie_formulario', 'campos_mostrar', 'parametros', 'widget_id'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }


    public function get_lista_campos()
    {
        $campos = json_decode( $this->campos_mostrar );
        
        $lista_campos = '<h2 style="color: orange; border: 1px solid #ddd; border-radius: 5px;">No hay campos en el fomulario.</h2>';
        
        if ( !is_null( $campos) )
        {
        	$lista_campos = '';
        	foreach ($campos as $key => $value)
	        {
	            $lista_campos .= VistaController::mostrar_campo( $key, '', 'create' );
	        }

        } 

        return $lista_campos;
    }
}
