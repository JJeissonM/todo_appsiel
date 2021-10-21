<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;

use Auth;
use DB;
use Input;
use Storage;

use App\Sistema\Html\MigaPan;

use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use App\Core\Tercero;

use App\Salud\ConsultaMedica;
use App\Salud\ExamenMedico;
use App\Salud\Paciente;

use App\Ventas\Cliente;

class HistoriaMedicaOcupacionalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Almacenar algunos datos del paciente
        $general = new ModeloController();
        $registro_creado = $general->crear_nuevo_registro( $request );

        /* Almacenar datos del Tercero y ... 
            Asignar datos adicionales al Paciente creado */
        $tercero = Tercero::crear_nuevo_tercero($general, $request);
        $registro_creado->core_tercero_id = $tercero->id;

        // Consecutivo Historia Clínica
        // Se obtiene el consecutivo para actualizar el logro creado
        $registro = DB::table('sys_secuencias_codigos')->where('modulo','historias_clinicas')->value('consecutivo');
        $consecutivo=$registro+1;

        // Actualizar el consecutivo
        DB::table('sys_secuencias_codigos')->where('modulo','historias_clinicas')->increment('consecutivo');
        
        $registro_creado->codigo_historia_clinica = $consecutivo;

        $registro_creado->save();

        // Crear Tercero como cliente
        if ( is_null( Cliente::where( 'core_tercero_id', $tercero->id)->get()->first() ) )
        {
            // Datos del Cliente
            $cliente = new Cliente;
            $cliente->fill( 
                            ['core_tercero_id' => $tercero->id, 'encabezado_dcto_pp_id' => 1, 'clase_cliente_id' => 1, 'lista_precios_id' => 1, 'lista_descuentos_id' => 1, 'vendedor_id' => 1,'inv_bodega_id' => 1, 'zona_id' => 1, 'liquida_impuestos' => 1, 'condicion_pago_id' => 1, 'estado' => 'Activo' ]
                             );
            $cliente->save();
        }
        

        return redirect( 'consultorio_medico/pacientes/'.$registro_creado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with( 'flash_message','Registro CREADO correctamente.' );
    }

    /**
     *  Historia Clínica
     * ruta: consultorio_medico/pacientes/{id}
     */
    public function show($id)
    {
        $secciones_consulta = json_decode( config('consultorio_medico.secciones_consulta') );

        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find( Input::get('id_modelo') );

        // Se obtiene el registro del modelo indicado y el anterior y siguiente registro
        $registro = app($modelo->name_space)->find($id);
        $reg_anterior = app($modelo->name_space)->where('id', '<', $registro->id)->max('id');
        $reg_siguiente = app($modelo->name_space)->where('id', '>', $registro->id)->min('id');
        
        
        $datos_historia_clinica = Paciente::datos_basicos_historia_clinica( $id );
        

        $miga_pan = MigaPan::get_array( Aplicacion::find( Input::get('id') ), $modelo, 'Historia Clínica' );

        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo');
        
        $model_controller = new ModeloController();
        $acciones = $model_controller->acciones_basicas_modelo( $modelo, $variables_url );

        $url_crear = $acciones->create;
        $url_edit = $acciones->edit;

        // RELATIVO A CONSULTAS
        $modelo_consultas = Modelo::where('modelo','salud_consultas')->first(); // ID:96
        
        $consultas = ConsultaMedica::where('paciente_id', $id)->orderBy('fecha','DESC')->get();

        //dd($consultas);

        $modelo_formulas_opticas = Modelo::where('modelo','salud_formulas_opticas   ')->first();
        
        return view('consultorio_medico.pacientes.show',compact('secciones_consulta','miga_pan','registro','url_crear','url_edit','reg_anterior','reg_siguiente','consultas','modelo_consultas','datos_historia_clinica','modelo_formulas_opticas','id'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $modelo = Modelo::find($request->url_id_modelo);

        // Se obtinene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        // Si se envían datos tipo file
        if ( count($request->file()) > 0) 
        {   
            // Para borrar el archivo anterior
            $registro2 = app($modelo->name_space)->find($id);
        }

        // LLamar a los campos del modelo para verificar los que son requeridos
        // y los que son únicos
        $lista_campos = $modelo->campos->toArray();
        $cant = count($lista_campos);
        for ($i=0; $i < $cant; $i++)
        {
            if ( $lista_campos[$i]['editable'] == 1 ) 
            { 
                    // Se valida solo si el campo pertenece al Modelo directamente
                    if ( in_array( $lista_campos[$i]['name'], $registro->getFillable() )  ) 
                    {
                        if ($lista_campos[$i]['requerido']) 
                        {
                            $this->validate($request,[$lista_campos[$i]['name']=>'required']);
                        }
                        if ($lista_campos[$i]['unico']) 
                        {
                            $this->validate($request,[$lista_campos[$i]['name']=>'unique:'.$registro->getTable().','.$lista_campos[$i]['name'].','.$id]);
                        }
                    }
            }
            // Cuando se edita una transacción
            if ($lista_campos[$i]['name']=='movimiento') {
                $lista_campos[$i]['value']=1;
            }
        }

        $registro->fill( $request->all() );
        $registro->save();

        $archivos_enviados = $request->file();
        foreach ($archivos_enviados as $key => $value) 
        {
            // Si se carga un nuevo archivo, Eliminar el(los) archivo(s) anterior(es)
            if ( $request->file($key) != '' ) 
            {
                Storage::delete('fotos_terceros/'.$registro2->tercero->$key);                
            }
            
            // 2do. Almacenar en disco con su extensión específica
            $archivo = $request->file($key);

            $extension =  $archivo->clientExtension();

            $nuevo_nombre = uniqid().'.'.$extension;

            Storage::put('fotos_terceros/'.$nuevo_nombre,
                file_get_contents( $archivo->getRealPath() ) 
                );

            // Guardar nombre en la BD
            $registro2->tercero->$key = $nuevo_nombre;
            $registro2->tercero->save();
        }


        // Actualizar datos del Tercero
        $registro->tercero->fill( $request->all() );
        $registro->tercero->save();

        return redirect('consultorio_medico/pacientes/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Registro MODIFICADO correctamente.');
    }


    
    public function eliminar(Request $request)
    {
        Paciente::find($request->recurso_a_eliminar_id)->delete();

        return redirect('web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Paciente ELIMINADO correctamente.');
    }
}
