<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Salud\Odontograma;

class OdontogramaController extends Controller
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
        return Odontograma::all();
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
        
        $odontograma = Odontograma::find($request->odontograma_id);
        if($odontograma == null){
            $odontograma = Odontograma::create($request->all());
        }else{
            $odontograma->odontograma_data = $request->odontograma_data;
            $odontograma->observaciones = $request->observaciones;
            $odontograma->save();
        }
        
        return $odontograma->id;
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
               
        if( Odontograma::where('id_consultas',$id)->exists() )
        {
            $odontograma = Odontograma::where('id_consultas',$id)->get()->first();
        }else{
            $odontograma = Odontograma::orderBy('id_consultas', 'desc')->first();
            if($odontograma == null){
                $odontograma = 'sin-datos';
            }else{
                $odontograma->id = "";    
            }            
        }

        return $odontograma;
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
        echo "editar";
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
        echo "actualizar";
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
        echo "eliminar";
    }
}
