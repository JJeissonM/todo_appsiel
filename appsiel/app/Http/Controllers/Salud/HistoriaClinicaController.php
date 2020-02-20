<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Core\ModeloEavController;

use Auth;
use DB;
use Input;
use Storage;
use View;

use App\Sistema\Modelo;
use App\Core\Tercero;
use App\Core\Empresa;

use App\Salud\ConsultaMedica;
use App\Salud\ExamenMedico;
use App\Salud\Paciente;
use App\Salud\ProfesionalSalud;
use App\Salud\ResultadoExamenMedico;
use App\Salud\ExamenTieneOrganos;
use App\Salud\ExamenTieneVariables;

class HistoriaClinicaController extends Controller
{
    /*
      Se copio de ConsultaController para hacer aquí el ingreso de consultas, anamnesis, exámenes, formulas y diagnóstico en un solo formulario.
      Agregar el route.
    */


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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
        //
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
