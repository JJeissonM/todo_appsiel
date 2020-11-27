<?php

namespace App\Http\Controllers\Sistema;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Form;
use Auth;
use View;
use DB;

use App\Sistema\Campo;
use App\Core\Empresa;
use App\Core\Colegio;

use App\Calificaciones\EscalaValoracion;

class VistaController extends Controller
{

    /*
        MEJORA: Permitir usar campos compuestos. Ejemplo, Campo: Fecha próximo control = (campo_numérico) (Campo_select)
        En el campo numerico se indica la cantidad y el campo select se determina la unidad de medida (DIA, MES, AÑO)
        A través de Javascript se transforman estos campos a una fecha específica. 
    */


    public function dibujar_vista($tipo_vista)
    {
        

        //////   P A R A   M A T R I C U L A S

        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get();
        $colegio = $colegio[0];
        
        $id_colegio=$colegio->id;
        
        $matriculas = DB::table('matriculas')
            ->where([['id_colegio' , $id_colegio],
                    ['anio' , date('Y')],['estado','Activo']])
            ->orderBy('codigo','DESC')
            ->get();

        return view('matriculas.index', compact('matriculas','menus')); 


        //////   P A R A   E S T U D I A N T E S

        $estudiantes = Estudiante::where('id_colegio','=',$colegio->id)
                                ->orderBy('id','desc')
                                ->get();

        return view('matriculas.estudiantes.index', [
            'estudiantes' => $estudiantes
        ]);


    }

    public static function campos_dos_colummnas(array $campos, $modo = null)
    {
        $i=2;
        $cantidad_campos = 0;
        foreach ($campos as $campo)
        {

            /*

                    echo '<div class="column">';
                        if ( $modo == 'show')
                        {
                            echo '<div class="row" style="padding:5px;">'.VistaController::mostrar_campo( $campo['id'], $campo['value'], 'show' ).'</div>';
                        }else{
                            // Si el campo tiene el name core_campo_id-ID, se reemplaza por el ID del campo en la tabla sys_campos
                            echo '<div class="row" style="padding:5px;">'.str_replace("core_campo_id-ID", 'core_campo_id-'.$campo['id'], VistaController::dibujar_campo($campo) ).'</div>';
                        }
                    echo '</div>';

            */

            if( $i%2 == 0 ) // Si $i es par
            {
                echo '<div class="row">';
                echo '<div class="col-md-6">';
            }else{
                echo '<div class="col-md-6">';
            }

            if ( !isset($campo['id']) ) {
                $campo['id'] = 0;
            }
            
            if ( $modo == 'show') {
                echo '<div class="row" style="padding:5px;">'.VistaController::mostrar_campo( $campo['id'], $campo['value'], 'show' ).'</div>';
            }else{
                // Si el campo tiene el name core_campo_id-ID, se reemplaza por el ID del campo en la tabla sys_campos
                echo '<div class="row" style="padding:5px;">'.str_replace("core_campo_id-ID", 'core_campo_id-'.$campo['id'], VistaController::dibujar_campo($campo) ).'</div>';
            }

            echo '</div>';

            $i++;
            if($i%2==0){
                echo '</div>';
            }

            $cantidad_campos++;
        }

        if( $cantidad_campos%2 != 0)
        {
            echo '<div class="col-md-6"></div> </div>';
        }
    }

    public static function campos_una_colummna(array $campos, $modo = null)
    {
        foreach ($campos as $campo)
        {            
            echo '<div>'.VistaController::dibujar_campo( $campo ).'</div>';
        }
    }


    /*
        Pendiente por mejorar
    */
    public static function campos_una_linea(array $campos, $modo = null)
    {
        $linea = '<form class="form-inline">';
        foreach ($campos as $campo)
        {            
            $linea .= '<div class="form-group">' . VistaController::dibujar_campo( $campo ).'</div>';
        }
        $linea .= '</form>';

        echo $linea;
    }


    // Esta es la funcion principal que se llama desde las vistas create y edit
    public static function dibujar_campo(array $campo){
        //print_r($campo);
        if ($campo['requerido']) {
            $campo['descripcion'] = '*'.$campo['descripcion'];
        }
        switch ($campo['tipo']) {
            case 'bsLabel':

                $valor = '';
                switch ($campo['name']) {
                    case 'id_colegio':
                        $empresa = Empresa::find(Auth::user()->empresa_id);
                        $colegio = Colegio::where('empresa_id',$empresa->id)->get();
                        $valor = $colegio[0]->id;
                        break;
                    case 'empresa_id':
                        $valor = Auth::user()->empresa_id;
                        break;
                    case 'core_empresa_id':
                        $empresa_id = Auth::user()->empresa_id;
                        $empresa = Empresa::find($empresa_id);
                        $valor = $empresa->razon_social.$empresa->nombre1." ".$empresa->otros_nombres." ".$empresa->apellido1." ".$empresa->apellido2;
                        break;
                    case 'asignatura_id':
                        $valor = $campo['value'];
                        $empresa_id = Auth::user()->empresa_id;
                        break;
                    
                    default:
                        $valor = $campo['value'];
                        $empresa_id = Auth::user()->empresa_id;
                        break;
                }

                $control = Form::bsLabel($campo['name'],[$valor,$empresa_id],$campo['descripcion'], $campo['atributos']);

                break;
            case 'bsText':
                $control = Form::bsText($campo['name'],$campo['value'],$campo['descripcion'], $campo['atributos']);
                break;
            case 'bsEmail':
                $control = Form::bsEmail($campo['name'],$campo['value'],$campo['descripcion'], $campo['atributos']);
                break;
            case 'bsNumber':
                $control = Form::bsNumber($campo['name'],$campo['value'],$campo['descripcion'], $campo['atributos']);
                break;
            case 'bsTextArea':
                $control = Form::bsTextArea($campo['name'], $campo['value'], $campo['descripcion'], $campo['atributos']);
                break;
            case 'password':
                $control = Form::bsPassword($campo['name'],$campo['value'],$campo['descripcion'], $campo['atributos']);
                break;
            case 'hidden':
                $control = Form::hidden($campo['name'],$campo['value'], array_merge(['id' => $campo['name']], $campo['atributos']));
                break;
            case 'botones_form':
                $control = Form::bsButtonsForm($campo['value'], $campo['atributos']);
                break;
            case 'select':
                $opciones = VistaController::get_opciones_campo_tipo_select( $campo );
                $control = Form::bsSelect($campo['name'], $campo['value'], $campo['descripcion'], $opciones, $campo['atributos']);
                break;
            case 'bsSelectCreate':
                $control = Form::bsSelectCreate($campo['name'], $campo['value'], $campo['descripcion'], $campo['opciones'], $campo['atributos']);
                break;
            case 'fecha':
                $control = Form::bsFecha($campo['name'], $campo['value'], $campo['descripcion'], $campo['opciones'], $campo['atributos']);
                break;
            case 'hora':
                $control = Form::bsHora($campo['name'], $campo['value'], $campo['descripcion'], $campo['opciones'], $campo['atributos']);
                break;

            case 'bsCheckBox':
                $control = Form::bsCheckBox($campo['name'], $campo['value'], $campo['descripcion'], $campo['opciones'], $campo['atributos']);
                break;

            case 'bsRadioBtn':
                $control = Form::bsRadioBtn($campo['name'], $campo['value'], $campo['descripcion'], $campo['opciones'], $campo['atributos']);
                break;

            case 'spin':
                $control = '<div class="row" id="spin" style="display: none;">
                <img src="'.asset('assets/img/spinning-wheel.gif').'" width="32px" height="32px"></div>';
                break;
            case 'personalizado':
                $control = $campo['value'];
                break;
            case 'constante':
                switch ( $campo['value'] )
                {
                    case 'id_colegio':
                        $empresa = Empresa::find(Auth::user()->empresa_id);
                        $colegio = Colegio::where('empresa_id',$empresa->id)->get();
                        $valor = $colegio[0]->id;
                        break;
                    case 'colegio_id':
                        $empresa = Empresa::find(Auth::user()->empresa_id);
                        $colegio = Colegio::where('empresa_id',$empresa->id)->get();
                        $valor = $colegio[0]->id;
                        break;
                    case 'empresa_id':
                        $valor = Auth::user()->empresa_id;
                        break;
                    case 'created_by':
                        $valor = Auth::user()->id;
                        break;
                    
                    default:
                        $valor = $campo['value'];
                        break;
                }
                $control = Form::hidden($campo['name'],$valor, array_merge(['id' => $campo['name']], $campo['atributos']));
                break;

            case 'imagen':
                // Si se manda como valor la ubicación de la imagen, se muestra
                if ($campo['value']==null) {
                    $imagen='';
                }else{
                    $url = $campo['value'];
                    $imagen='<img alt="foto.jpg" src="'.asset($url).'" style="width: 150px; height: 150px;" />';
                }

                $vec_tipos = explode(',', $campo['opciones']);
                $tipos = '';
                for ($i=0; $i < count($vec_tipos); $i++) 
                { 
                    $tipos.='.'.$vec_tipos[$i].',';
                }

                $control = '<div class="well" style="margin-left: 17px; margin-right: 16px;">
                                <label for="'.$campo['name'].'" class="control-label"> '.$campo['descripcion'].' </label>
                                <br/>'.$imagen.'<br/>'.Form::file($campo['name'],['id'=>$campo['name'], 'accept'=>trim($tipos,",") ]).'
                            </div>';
                break;
            case 'imagenes_multiples':
                // Si se manda como valor la ubicación de la imagen, se muestra
                if ($campo['value']==null) {
                    $imagen='';
                }else{
                    $url = $campo['value'];
                    $imagen='<img alt="foto.jpg" src="'.asset($url).'" style="width: 150px; height: 150px;" />';
                }

                $vec_tipos = explode(',', $campo['opciones']);
                $tipos = '';
                for ($i=0; $i < count($vec_tipos); $i++) 
                { 
                    $tipos.='.'.$vec_tipos[$i].',';
                }

                $control = '<div class="form-group">'.$imagen.'<br/>
                            <label for="'.$campo['name'].'" class="control-label"> '.$campo['descripcion'].' </label>
                            '.Form::file($campo['name'],['id'=>$campo['name'], 'accept'=>trim($tipos,","), 'multiple' ]).'
                        </div>';
                break;
            case 'file':

                $vec_tipos = explode(',', $campo['opciones']);
                $tipos = '';
                for ($i=0; $i < count($vec_tipos); $i++) 
                { 
                    $tipos.='.'.$vec_tipos[$i].',';
                }
                
                $control = '<div class="form-group" id="div_'.$campo['name'].'" style="padding:5px;">
                            <label for="'.$campo['name'].'" class="control-label"> '.$campo['descripcion'].' </label>
                            '.Form::file($campo['name'],['id'=>$campo['name'], 'accept'=>trim($tipos,",")]).'
                        </div>';
                break;

            case 'html_ayuda':
                $vec_ruta = explode("_", $campo['opciones']);
                //$ruta = str_replace(".", "/", $vec_ruta[1])
                $control = View::make($vec_ruta[1]);
                break;
            case 'json_simple':
                $filas = '';
                if ( $campo['value'] != "" ) 
                {
                    $opciones = json_decode( $campo['value'] );
                    if ( !is_null($opciones) ) 
                    {
                        foreach ($opciones as $key => $value) {
                            $filas .= '<tr>
                                        <td>'.$key.'</td>
                                        <td>'.$value.'</td>
                                        <td><button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="glyphicon glyphicon-trash"></i></button></td>
                                        </tr>';
                        }
                    }                        
                }
                $control = ' <div class="form-group"> <div id="div_agregar_opciones" class="well">
                                <br/>
                                <h4> Agregar opciones </h4>
                                <hr>
                                <table class="table table-striped" id="ingreso_registros">
                                    <thead>
                                        <tr>
                                            <th>Opción</th>
                                            <th>Valor</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    '.$filas.'
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td><input type="text" class="form-control" id="key_json"></td>
                                            <td><input type="text" class="form-control" id="value_json"></td>
                                            <td>
                                                <button type="button" class="btn btn-xs btn-success" id="btn_nueva_linea"><i class="fa fa-btn fa-plus"></i></button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <input type="hidden" name="opciones" id="opciones" style="background:cyan;" required="required" value="'.$campo['value'].'">
                            </div>
                            </div>';
                break;
            case 'escala_valoracion':

                $escalas = EscalaValoracion::get_escalas_periodo_lectivo_abierto();
                $control = '<h4>Descripción para cada escala de valoración</h4>
                                <hr>';
                $escalas_ids = '';
                $el_primero = true;
                foreach ($escalas as $una_escala)
                {
                    // Al editar, value tiene un array valores, cuyos índices son los ids de las escalas
                    if ( !is_null( $campo['value'] )  )
                    {
                        // Si el índice existe
                        if ( isset( $campo['value'][ $una_escala->id ] ) )
                        {
                            $control .= Form::bsTextArea('descripcion_escala[]', $campo['value'][ $una_escala->id ], $una_escala->nombre_escala.' ('.$una_escala->calificacion_minima.'-'.$una_escala->calificacion_maxima.')', $campo['atributos']);
                        }else{
                            // Si hay una escala de valoración que no tiene descripcion de logro
                            $control .= Form::bsTextArea('descripcion_escala[]', null, $una_escala->nombre_escala.' ('.$una_escala->calificacion_minima.'-'.$una_escala->calificacion_maxima.')', $campo['atributos']);
                        }
                    }else{
                        $control .= Form::bsTextArea('descripcion_escala[]', null, $una_escala->nombre_escala.' ('.$una_escala->calificacion_minima.'-'.$una_escala->calificacion_maxima.')', $campo['atributos']);
                    }
                    
                    if ($el_primero)
                    {
                        $escalas_ids .= $una_escala->id;
                        $el_primero = false;
                    }else{
                        $escalas_ids .= '-'.$una_escala->id;
                    }
                }

                $control .= '<input type="hidden" name="escalas_ids" value="'.$escalas_ids.'">';
                break;
            case 'frame_ajax':
                $control = '<div id="frame_ajax" class="frame_ajax" > hello </div>';
                break;

            case 'input_lista_sugerencias':
                $control = Form::bsInputListaSugerencias( $campo['name'], $campo['value'], $campo['descripcion'], $campo['atributos']);
                break;
            
            default:
                $control = '<div class="alert alert-danger">
                              <strong>¡Error!</strong> Tipo de campo (elemento input) no existe.</div>';
                break;
        }

        return $control;
    }

    /*

            dibujar_solo_control()
            ESTA FUNCION SOLO SE USA EN LA VISTAS: cosultorio_medico.resultado_examen_create.blade.php y cosultorio_medico.resultado_examen_edit.blade.php

            SE DEBE replantear esas vistas y eliminar esta función
    */
    public static function dibujar_solo_control( $tipo, $name, $valor)
    {
        switch ($tipo) {
            case 'bsLabel':
                break;

            case 'bsText':
                $control = Form::text( $name,$valor, ['class' => 'form-control'] );
                break;
            case 'bsTextArea':
                $control = Form::textarea($name, $valor, ['class' => 'form-control']);
                break;
            case 'password':
                $control = Form::bsPassword($campo['name'],$campo['value'],$campo['descripcion'], $campo['atributos']);
                break;
            case 'hidden':
                $control = Form::hidden($campo['name'],$campo['value'], array_merge(['id' => $campo['name']], $campo['atributos']));
                break;
            case 'botones_form':
                $control = Form::bsButtonsForm($campo['value'], $campo['atributos']);
                break;
            case 'select':
                $control = Form::select($campo['name'], $campo['opciones'], $campo['value'], array_merge( [ 'id' => $campo['name'],'style'=>'border: none;border-color: transparent;border-bottom: 1px solid gray;' ], $campo['atributos'] ));
                break;
            case 'bsSelectCreate':
                $control = Form::bsSelectCreate($campo['name'], $campo['value'], $campo['descripcion'], $campo['opciones'], $campo['atributos']);
                break;
            case 'fecha':
                $control = Form::bsFecha($campo['name'], $campo['value'], $campo['descripcion'], $campo['opciones'], $campo['atributos']);
                break;
            case 'hora':
                $control = Form::bsHora($campo['name'], $campo['value'], $campo['descripcion'], $campo['opciones'], $campo['atributos']);
                break;
            case 'spin':
                $control = '<div class="row" id="spin" style="display: none;">
                <img src="'.asset('assets/img/spinning-wheel.gif').'" width="32px" height="32px"></div>';
                break;
            case 'personalizado':
                $control = $campo['value'];
                break;
            case 'constante':
                switch ( $campo['value'] ) {
                    case 'id_colegio':
                        $empresa = Empresa::find(Auth::user()->empresa_id);
                        $colegio = Colegio::where('empresa_id',$empresa->id)->get();
                        $valor = $colegio[0]->id;
                        break;
                    case 'empresa_id':
                        $valor = Auth::user()->empresa_id;
                        break;
                    
                    default:
                        $valor = $campo['value'];
                        break;
                }
                $control = Form::hidden($campo['name'],$valor, array_merge(['id' => $campo['name']], $campo['atributos']));
                break;
            case 'imagen':
                // Si se manda como valor la ubicación de la imagen, se muestra
                if ($campo['value']==null) {
                    $imagen='';
                }else{
                    $url = $campo['value'];
                    $imagen='<img alt="foto.jpg" src="'.asset($url).'" style="width: 150px; height: 150px;" />';
                }

                $vec_tipos = explode(',', $campo['opciones']);
                $tipos = '';
                for ($i=0; $i < count($vec_tipos); $i++) 
                { 
                    $tipos.='.'.$vec_tipos[$i].',';
                }

                $control = '<div class="form-group">'.$imagen.'<br/>
                            <label for="'.$campo['name'].'" class="control-label"> '.$campo['descripcion'].' </label>
                            '.Form::file($campo['name'],['id'=>$campo['name'], 'accept'=>trim($tipos,",") ]).'
                        </div>';
                break;
            case 'file':
                $control = '<div class="form-group" id="div_'.$campo['name'].'">
                            <label for="'.$campo['name'].'" class="control-label"> '.$campo['descripcion'].' </label>
                            '.Form::file($campo['name'],['id'=>$campo['name']]).'
                        </div>';
                break;

            case 'html_ayuda':
                $vec_ruta = explode("_", $campo['opciones']);
                //$ruta = str_replace(".", "/", $vec_ruta[1])
                $control = View::make($vec_ruta[1]);
                break;
            default:
                $control = '<div class="alert alert-danger">
                              <strong>¡Error!</strong> Tipo de campo (elemento input) no existe.</div>';
                break;
        }

        return $control;
    }

    // Recibe un array del campo
    public static function show_campo(array $campo)
    {
        $control = VistaController::mostrar_campo( $campo['id'], $campo['value'], 'show' );

        return $control;
    }

    // Recibe el ID de un campo y su valor
    public static function mostrar_campo( $core_campo_id, $valor, $modo )
    {
        $campo = Campo::find( $core_campo_id );

        if ( is_null( $campo ) )
        {
            return '<div class="alert alert-danger">
                              <strong>¡Error!</strong> Campo no existe.</div>';
        }

        $campo = $campo->toArray();

        // El campo Atributos se ingresa en  formato JSON {"campo1":"valor1","campo2":"valor2"}
        if ($campo['atributos']!='') {
            $campo['atributos'] = json_decode($campo['atributos'],true);
        }else{
            $campo['atributos'] = [];
        }

        if ($campo['requerido']) {
            $campo['atributos']=array_merge($campo['atributos'],['required' => 'required']);
        }


        if ($campo['value']=='null') {
            $campo['value']=null;
        }

        $texto_opciones = $campo['opciones'];

        $salida = '';
        switch ( $modo ) {
            case 'create':
                
                $salida = VistaController::dibujar_campo( $campo );
                
                break;
            case 'edit':

                // Esto hace lo mismo que hace automáticamente el facade Form::model() cuando el value del campo tiene null
                // Como en este edit especial no se una Form::model() hay que asignarle al select  
                //$vec['']='';
                if ($texto_opciones!='' and $campo['tipo'] == 'select') 
                {
                    $campo['opciones'] = VistaController::get_opciones_campo_tipo_select( $campo );
                }
                
                if ( $valor != "" ) {
                    $campo['value'] = $valor;
                }
                
                $salida = $campo;
                break;
            case 'show':
                
                if ($texto_opciones!='' and $campo['tipo'] == 'select') 
                {
                    $campo['value'] = $valor;
                    $valor = VistaController::get_descripcion_value_campo_tipo_select( $campo );
                }                

                $salida = '<b>'.$campo['descripcion'].': </b>'.$valor;
                break;
            
            default:
                # code...
                break;
        }

        return $salida;
    }

    // Para obtener/formatear las opciones de los campos tipo select y bsCheckBox
    public static function get_opciones_campo_tipo_select( array $campo )
    {   
        $texto_opciones = '';
        if ( is_string( $campo['opciones'] ) )
        {
            $texto_opciones = trim($campo['opciones']);
        }

        if ( is_array( $campo['opciones'] ) ) {
            return $campo['opciones'];
        }
        
        $vec['']='';

        if ( $texto_opciones == '')
        {
            return $vec;
        }

        switch (substr($texto_opciones,0,strpos($texto_opciones, '_'))) {
            case 'table':
                // Cuando en opciones está la cadena table_[nombre_tabla_bd]
                $tabla = substr($texto_opciones,6,strlen($texto_opciones)-1);
            
                $opciones = DB::table($tabla)->get();
                
                // Mostar solo los registros de la empresa del usuario, si aplica
                if ( isset($opciones[0]->core_empresa_id) ) {
                    unset($opciones);
                    $opciones = DB::table($tabla)->where('core_empresa_id',Auth::user()->empresa_id)->get();
                }
                
                foreach ($opciones as $opcion){

                    // Si la tabla TIENE un campo descripcion para llenar el select
                    if ( isset($opcion->descripcion) ) {                        
                        $vec[$opcion->id] = $opcion->descripcion;
                    }

                    // Para la tabla roles
                    if (isset($opcion->name)) {
                        $vec[$opcion->id]=$opcion->name;
                    }

                    // Para la tabla estudiantes
                    if (isset($opcion->nombres)) {
                        $vec[$opcion->id]=$opcion->apellido1.' '.$opcion->apellido2.' '.$opcion->nombres;
                    }
                }

                // Para propiedad horizontal
                if( $campo['name'] == 'ph_propiedad_id')
                {
                    $opciones = DB::table($tabla)->leftJoin('core_terceros','core_terceros.id','=',$tabla.'.core_tercero_id')->where($tabla.'.core_empresa_id',Auth::user()->empresa_id)->select('core_terceros.id as core_tercero_id',$tabla.'.id',$tabla.'.codigo','core_terceros.descripcion')->get();

                    foreach ($opciones as $opcion)
                    {
                        $vec[$opcion->core_tercero_id.'a3p0'.$opcion->id] = $opcion->codigo.' - '.$opcion->descripcion;
                    }
                }

                if( $campo['name'] == 'escala_valoracion_id')
                {
                    foreach ($opciones as $opcion)
                    {
                        $vec[$opcion->id] = $opcion->nombre_escala." (".$opcion->calificacion_minima."-".$opcion->calificacion_maxima.")";
                    }
                }

                break;

            case 'model':

                // Cuando en opciones está la cadena model_[name_space_modelo]

                $model = substr($texto_opciones,6,strlen($texto_opciones)-1);

                $vec = app($model)->opciones_campo_select();

                break;

            case 'ayuda':
                
                break;
            
            default:

                // Cuando en opciones está la cadena en formato JSON
                $vec = json_decode($texto_opciones,true);
                
                if ( is_null($vec) )
                {
                    $vec = ['Error en formato JSON del campo.'];
                }

                break;
        }

        return $vec;
    }

    // El campo recibido ya tiene asignado el valor de la opcion en la key value
    public static function get_descripcion_value_campo_tipo_select( array $campo )
    {   
        $valor = 'Valor no encontrado.';

        $texto_opciones = $campo['opciones'];
        $vec['']='';
        switch (substr($texto_opciones,0,strpos($texto_opciones, '_'))) {
            case 'table':
                $tabla = substr($texto_opciones,6,strlen($texto_opciones)-1);

                $registro = DB::table($tabla)->where('id', $campo['value'])->get();
                break;

            case 'model':

                $model = substr($texto_opciones,6,strlen($texto_opciones)-1);

                $registro = app($model)->where('id', $campo['value'])->get();

                break;

            case 'ayuda':
                
                break;
            
            default:
                $vec = json_decode($texto_opciones,true);

                if ( isset( $vec[ $campo['value'] ] ) ) {
                    $valor = $vec[ $campo['value'] ];
                }else{
                    $valor = 'Error: Valor no encontrado.';
                }
                
                break;
        }

        if ( isset($registro) ) 
        {
            if (count($registro)==0) {
                $valor = 0;
            }else{
                $opcion = $registro[0];
                if (isset($opcion->descripcion)) {
                    $valor = $opcion->descripcion;
                }
                if (isset($opcion->name)) {
                    $valor = $opcion->name;
                }
                if (isset($opcion->nombres)) {
                    $valor = $opcion->apellido1.' '.$opcion->apellido2.' '.$opcion->nombres;
                }
            }
        }

        return $valor;
    }
}