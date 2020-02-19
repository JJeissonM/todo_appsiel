<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;

use Input;
use DB;

use App\Sistema\Modelo;

use App\Salud\ExamenTieneVariables;
use App\Salud\ExamenTieneOrganos;
use App\Salud\ExamenMedico;

class AnamnesisController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $general = new ModeloController();

        return $general->create();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd( $request->all() );

        // Registro del Modelo Entidad en EAV
        $modelo = Modelo::find($request->url_id_modelo);

        // Datos para el Modelo auxiliar asociado a la Entidad
        $modelo_pacientes = Modelo::where('modelo','salud_pacientes')->first();
        $modelo_principal_id = $modelo_pacientes->id;
        $registro_modelo_principal_id = $request->paciente_id;


        $modelo_relacionado_id = $request->url_id_modelo; // Entidad

        // Se va a crear un registro por cada Atributo (campo) que tenga un Valor distinto a vacío 
        foreach ( $request->all() as $key => $value) 
        {
            // Si el name del campo enviado tiene la palabra core_campo_id
            if ( strpos( $key, "core_campo_id") !== false ) 
            {
                $core_campo_id = explode("-", $key)[1]; // Atributo
                $valor = $value; // Valor

                if ( $valor != '' ) 
                {
                    app($modelo->name_space)->create( [ "modelo_principal_id" => $modelo_principal_id, "registro_modelo_principal_id" => $registro_modelo_principal_id, "modelo_relacionado_id" => $modelo_relacionado_id, "core_campo_id" => $core_campo_id, "valor" => $valor ] );
                }
            }
        }

        return redirect( 'consultorio_medico/pacientes/'.$request->paciente_id.'?id='.$request->url_id.'&id_modelo='.$modelo_pacientes->id )->with( 'flash_message','ANAMNESIS creada correctamente.' );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $general = new ModeloController();
        return $general->edit( $id );
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
        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find($request->url_id_modelo);

        // Se obtinene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        // LLamar a los campos del modelo para verificar los que son requeridos
        // y los que son únicos
        $lista_campos = $modelo->campos->toArray();
        $cant = count($lista_campos);
        for ($i=0; $i < $cant; $i++) {
            if ( $lista_campos[$i]['editable'] == 1 ) 
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
            // Cuando se edita una transacción
            if ($lista_campos[$i]['name']=='movimiento') {
                $lista_campos[$i]['value']=1;
            }
        }

        //dd( $request->all() );

        $registro->fill( $request->all() );
        $registro->save();

        return redirect('consultorio_medico/pacientes/'.$request->paciente_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Fórmula MODIFICADA correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}   