<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;

use App\Calificaciones\CursoTieneAsignatura;

use App\AcademicoDocente\PlanClaseEstrucElemento;
use App\AcademicoDocente\PlanClaseRegistro;

use Form;
use Input;
use Auth;

use App\Matriculas\Curso;

use App\Calificaciones\Asignatura;

class GuiaAcademica extends Model
{
    protected $table = 'sga_plan_clases_encabezados';

	protected $fillable = ['plantilla_plan_clases_id', 'fecha', 'semana_calendario_id', 'periodo_id', 'curso_id', 'asignatura_id', 'user_id','archivo_adjunto', 'estado'];
	
    public $encabezado_tabla = ['Fecha', 'Semana académica', 'Periodo', 'Curso', 'Asignatura', 'Profesor', 'Estado', 'Acción'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"sga_planes_clases/id_fila"}';
    
    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/academico_docente/planes_clases.js';
	
	public static function consultar_registros()
	{
        $user = Auth::user();

        $array_wheres = [ ['sga_plan_clases_encabezados.plantilla_plan_clases_id' ,'=', 99999] ];
        
        if ( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') ) 
        {
            $array_wheres = array_merge($array_wheres, ['sga_plan_clases_encabezados.user_id' => $user->id]);          
        }

	    return GuiaAcademica::leftJoin( 'sga_semanas_calendario', 'sga_semanas_calendario.id', '=', 'sga_plan_clases_encabezados.semana_calendario_id')
                                    ->leftJoin( 'sga_periodos', 'sga_periodos.id', '=', 'sga_plan_clases_encabezados.periodo_id')
                                    ->leftJoin( 'sga_cursos', 'sga_cursos.id', '=', 'sga_plan_clases_encabezados.curso_id')
                                    ->leftJoin( 'sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_plan_clases_encabezados.asignatura_id')
                                    ->leftJoin( 'users', 'users.id', '=', 'sga_plan_clases_encabezados.user_id')
                                    ->where( $array_wheres )
                                    ->select(
                                        'sga_plan_clases_encabezados.fecha AS campo1',
                                        'sga_semanas_calendario.descripcion AS campo2',
                                        'sga_periodos.descripcion AS campo3',
                                        'sga_cursos.descripcion AS campo4',
                                        'sga_asignaturas.descripcion AS campo5',
                                        'users.name AS campo6',
                                        'sga_plan_clases_encabezados.estado AS campo7',
                                        'sga_plan_clases_encabezados.id AS campo8' )
                            	    ->get()
                            	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = GuiaAcademica::where('sga_plan_clases_encabezados.estado','Activo')
                    ->select('sga_plan_clases_encabezados.id','sga_plan_clases_encabezados.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    // PADRE = CURSO, HIJO = asignaturas
    public static function get_registros_select_hijo($id_select_padre)
    {
        $registros = CursoTieneAsignatura::asignaturas_del_curso( $id_select_padre, null, null, null );

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $campo) {
                            
            $opciones .= '<option value="'.$campo->id.'">'.$campo->descripcion.'</option>';
        }
        return $opciones;
    }

    public static function get_campos_adicionales_create( $lista_campos )
    {
        $user = Auth::user();
        
        // Enviar formulario vacío. Se evita la creación, si se presiona el botón desde Académico Docente, pues no se han enviado ni el curos ni la asignatura 
        if ( ( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') ) && is_null( Input::get('curso_id') ) ) 
        {
            return [
                        [
                                    "id" => 999,
                                    "descripcion" => "Label no se puede ingresar registros desde esta opción.",
                                    "tipo" => "personalizado",
                                    "name" => "lbl_planilla",
                                    "opciones" => "",
                                    "value" => '<div class="form-group">                    
                                                    <label class="control-label col-sm-3" style="color: red;" > <b> No se pueden ingresar registros desde esta opción. </b> </label>
                                                    <br>
                                                    <a href="'.url( 'academico_docente?id='.Input::get('id') ).'" class="btn btn-sm btn-info"> <i class="fa fa-th-large"></i> Ir a mi listado de asignaturas. </a>      
                                                </div>',
                                    "atributos" => [],
                                    "definicion" => "",
                                    "requerido" => 0,
                                    "editable" => 1,
                                    "unico" => 0
                                ]
                    ];          
        }


        /*
            Personalizar los campos
        */
        $cantida_campos = count($lista_campos);
        for ($i=0; $i <  $cantida_campos; $i++)
        {
            // Cuando se envían la asignatura y el curso en la URL

            if ( !is_null( Input::get('curso_id') ) ) {
                switch ( $lista_campos[$i]['name'] )
                {
                    case 'curso_id':
                        $curso = Curso::find( Input::get('curso_id') );
                        $lista_campos[$i]['opciones'] = [ $curso->id => $curso->descripcion ];
                        break;
                    case 'asignatura_id':
                        $asignatura = Asignatura::find( Input::get('asignatura_id') );
                        $lista_campos[$i]['opciones'] = [ $asignatura->id => $asignatura->descripcion ];
                        break;

                    default:
                        # code...
                    break;
                }
            }                    
        }


        /*
            Agregar nuevos campos
        */
        
        $plantilla_id = 99999; // Se usará este ID de plantilla para las guías académicas

        array_push($lista_campos, [
                                    "id" => 0,
                                    "descripcion" => 'CONTENIDO',
                                    "tipo" => "bsTextArea",
                                    "name" => "elemento_descripcion[]",
                                    "opciones" => "",
                                    "value" => null,
                                    "atributos" => [ 'class' => 'contenido' ],
                                    "definicion" => "",
                                    "requerido" => 0,
                                    "editable" => 1,
                                    "unico" => 0
                                ] );

        array_push($lista_campos, [
                                    "id" => 999,
                                    "descripcion" => 'user_id',
                                    "tipo" => "hidden",
                                    "name" => "user_id",
                                    "opciones" => "",
                                    "value" => Auth::user()->id,
                                    "atributos" => [],
                                    "definicion" => "",
                                    "requerido" => 0,
                                    "editable" => 1,
                                    "unico" => 0
                                ]
                            );



        array_push($lista_campos, [
                                            "id" => 999,
                                            "descripcion" => "",
                                            "tipo" => "personalizado",
                                            "name" => "lbl_planilla",
                                            "opciones" => "",
                                            "value" => '<input name="plantilla_plan_clases_id" id="plantilla_plan_clases_id" type="hidden" value="'.$plantilla_id.'"/>',
                                            "atributos" => [],
                                            "definicion" => "",
                                            "requerido" => 0,
                                            "editable" => 1,
                                            "unico" => 0
                                        ] );

        return $lista_campos;
    }



    public static function store_adicional( $datos, $registro )
    {

        foreach ( $datos['elemento_descripcion'] as $key => $value )
        {

            PlanClaseRegistro::create( 
                                        [ 
                                            'plan_clase_encabezado_id' => $registro->id,
                                            'plan_clase_estruc_elemento_id' => 99999,
                                            'contenido' => $value,
                                            'estado' => 'Activo'
                                        ]
                                    );
        }

        return true;
    }


    public static function get_campos_adicionales_edit( $lista_campos, $registro )
    {
        $user = Auth::user();
        
        /*
            Personalizar los campos
        */
        if( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') ) 
        {
            $cantida_campos = count($lista_campos);
            for ($i=0; $i <  $cantida_campos; $i++)
            {
                switch ( $lista_campos[$i]['name'] )
                {
                    case 'curso_id':
                        $curso = Curso::find( $registro->curso_id );
                        $lista_campos[$i]['opciones'] = [ $curso->id => $curso->descripcion ];
                        break;
                    case 'asignatura_id':
                        $asignatura = Asignatura::find( $registro->asignatura_id );
                        $lista_campos[$i]['opciones'] = [ $asignatura->id => $asignatura->descripcion ];
                        break;

                    default:
                        # code...
                    break;
                }
            }      
        }


        /*
            Agregar nuevos campos
        */
        $elemento_id = 99999; // Se usará este ID de plantilla para las guías académicas

        $registro_elemento = PlanClaseRegistro::where( 'plan_clase_encabezado_id', $registro->id )
                                                ->where( 'plan_clase_estruc_elemento_id', $elemento_id )
                                                ->get()
                                                ->first();
        
        $contenido = '';
        if ( !is_null( $registro_elemento ) )
        {
            $contenido = $registro_elemento->contenido;
        }
            
        array_push($lista_campos, [
                                    "id" => $elemento_id,
                                    "descripcion" => 'CONTENIDO',
                                    "tipo" => "bsTextArea",
                                    "name" => "elemento_descripcion[]",
                                    "opciones" => "",
                                    "value" => $contenido,
                                    "atributos" => ['class' => 'contenido'],
                                    "definicion" => "",
                                    "requerido" => 0,
                                    "editable" => 1,
                                    "unico" => 0
                                ] );

        return $lista_campos;
    }

    public static function update_adicional( $datos, $plan_clase_encabezado_id )
    {
        $elemento_id = 99999; // Se usará este ID de plantilla para las guías académicas

        foreach ( $datos['elemento_descripcion'] as $key => $value )
        {

            $registro_elemento = PlanClaseRegistro::where( 'plan_clase_encabezado_id', $plan_clase_encabezado_id )
                                                ->where( 'plan_clase_estruc_elemento_id', $elemento_id )
                                                ->get()
                                                ->first();

            if ( !is_null( $registro_elemento ) )
            {
                $registro_elemento->update( 
                                                [ 
                                                    'contenido' => $value
                                                ]
                                            );
            }else{
                PlanClaseRegistro::create( 
                                        [ 
                                            'plan_clase_encabezado_id' => $plan_clase_encabezado_id,
                                            'plan_clase_estruc_elemento_id' => $elemento_id,
                                            'contenido' => $value,
                                            'estado' => 'Activo'
                                        ]
                                    );
            }   
        }

        return true;
    }

    public static function get_registros_anterior_siguiente( $id )
    {
        $user = Auth::user();

        $where = [ ['sga_plan_clases_encabezados.user_id' ,'>', 0] ];
        
        if ( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') ) 
        {
            $where = [ ['sga_plan_clases_encabezados.user_id' ,'=', $user->id] ];
        }

        $reg_anterior = GuiaAcademica::where('id', '<', $id)->where( $where )->max('id');
        $reg_siguiente = GuiaAcademica::where('id', '>', $id)->where( $where )->min('id');

        return [ $reg_anterior, $reg_siguiente ];
    }
    
    public static function get_registro_impresion( $id )
    {
        return GuiaAcademica::leftJoin( 'sga_plan_clases_struc_plantillas', 'sga_plan_clases_struc_plantillas.id','=', 'sga_plan_clases_encabezados.plantilla_plan_clases_id' )
                                    ->leftJoin( 'sga_semanas_calendario', 'sga_semanas_calendario.id', '=', 'sga_plan_clases_encabezados.semana_calendario_id')
                                    ->leftJoin( 'sga_periodos', 'sga_periodos.id', '=', 'sga_plan_clases_encabezados.periodo_id')
                                    ->leftJoin( 'sga_cursos', 'sga_cursos.id', '=', 'sga_plan_clases_encabezados.curso_id')
                                    ->leftJoin( 'sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_plan_clases_encabezados.asignatura_id')
                                    ->leftJoin( 'users', 'users.id', '=', 'sga_plan_clases_encabezados.user_id')
                                    ->where( 'sga_plan_clases_encabezados.id', $id )
                                    ->select(
                                        'sga_plan_clases_struc_plantillas.descripcion AS plantilla_decripcion',
                                        'sga_plan_clases_encabezados.fecha',
                                        'sga_plan_clases_encabezados.archivo_adjunto',
                                        'sga_semanas_calendario.descripcion AS semana_decripcion',
                                        'sga_periodos.descripcion AS periodo_decripcion',
                                        'sga_cursos.descripcion AS curso_decripcion',
                                        'sga_asignaturas.descripcion AS asignatura_decripcion',
                                        'users.name AS usuario_decripcion',
                                        'sga_plan_clases_encabezados.estado',
                                        'sga_plan_clases_encabezados.id')
                                    ->get()
                                    ->first();
    }


    
    public static function consultar_guias_estudiantes( $curso_id, $asignatura_id )
    {
        $array_wheres = [ ['sga_plan_clases_encabezados.id' ,'>', 0] ];
        $array_wheres = array_merge($array_wheres, 
                                        ['sga_plan_clases_encabezados.curso_id' => $curso_id,'sga_plan_clases_encabezados.asignatura_id' => $asignatura_id ] );

        return GuiaAcademica::leftJoin( 'sga_plan_clases_struc_plantillas', 'sga_plan_clases_struc_plantillas.id','=', 'sga_plan_clases_encabezados.plantilla_plan_clases_id' )
                                    ->leftJoin( 'sga_semanas_calendario', 'sga_semanas_calendario.id', '=', 'sga_plan_clases_encabezados.semana_calendario_id')
                                    ->leftJoin( 'sga_periodos', 'sga_periodos.id', '=', 'sga_plan_clases_encabezados.periodo_id')
                                    ->leftJoin( 'sga_cursos', 'sga_cursos.id', '=', 'sga_plan_clases_encabezados.curso_id')
                                    ->leftJoin( 'sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_plan_clases_encabezados.asignatura_id')
                                    ->leftJoin( 'users', 'users.id', '=', 'sga_plan_clases_encabezados.user_id')
                                    ->where( $array_wheres )
                                    ->select(
                                        'sga_plan_clases_struc_plantillas.descripcion AS plan_clases',
                                        'sga_plan_clases_encabezados.fecha',
                                        'sga_semanas_calendario.descripcion AS semana',
                                        'sga_periodos.descripcion AS periodo_decripcion',
                                        'sga_cursos.descripcion AS curso_decripcion',
                                        'sga_asignaturas.descripcion AS asignatura_decripcion',
                                        'users.name AS profesor',
                                        'sga_plan_clases_encabezados.estado',
                                        'sga_plan_clases_encabezados.id')
                                    ->get();
    }
}
