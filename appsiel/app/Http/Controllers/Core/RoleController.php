<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use Input;
//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Session;

class RoleController extends Controller {

    public function __construct() {
        $this->middleware(['auth', 'SuperAdmin']);//isAdmin middleware lets only users with a //specific permission permission to access these resources
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $permissions = Permission::leftJoin('sys_aplicaciones','sys_aplicaciones.id','=','permissions.core_app_id')
                            ->orderBy('permissions.core_app_id','ASC')
                            ->select('permissions.id','permissions.name','sys_aplicaciones.descripcion')
                            ->get()
                            ->toArray();//Get all permissions

        $miga_pan = [
                ['url'=>'configuracion?id='.Input::get('id'),'etiqueta'=>'Configuración'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=>'Perfiles'],
                ['url'=>'NO','etiqueta'=>'Crear nuevo']
            ];

        return view('core.roles.create', compact('permissions','miga_pan'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
    //Validate name and permissions field
        $this->validate($request, [
            'name'=>'required|unique:roles|max:100',
            'permissions' =>'required',
            ]
        );

        $name = $request['name'];
        $role = new Role();
        $role->name = $name;

        $permissions = $request['permissions'];

        $role->save();
        //Looping thru selected permissions
        foreach ($permissions as $permission) {
            $p = Permission::where('id', '=', $permission)->firstOrFail(); 
            //Fetch the newly created role and assign permission
            $role = Role::where('name', '=', $name)->first(); 
            $role->givePermissionTo($p);
        }

        return redirect('web?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Role'. $role->name.' Creado!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        return redirect('roles');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $role = Role::findOrFail($id);
        $permissions = Permission::leftJoin('sys_aplicaciones','sys_aplicaciones.id','=','permissions.core_app_id')
                            ->orderBy('permissions.core_app_id','ASC')
                            ->select('permissions.id','permissions.name','sys_aplicaciones.descripcion')
                            ->get()
                            ->toArray();

        $miga_pan = [
                ['url'=>'configuracion?id='.Input::get('id'),'etiqueta'=>'Configuración'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=>'Perfiles'],
                ['url'=>'NO','etiqueta'=>'Modificar']
            ];

        return view('core.roles.edit', compact('role', 'permissions','miga_pan'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {

        $role = Role::findOrFail($id);//Get role with the given id
    //Validate name and permission fields
        $this->validate($request, [
            'name'=>'required|max:100|unique:roles,name,'.$id,
            'permissions' =>'required',
        ]);

        $input = $request->except(['permissions','url_id','url_id_modelo']);
        $permissions = $request['permissions'];
        $role->fill($input)->save();

        $p_all = Permission::all();//Get all permissions

        foreach ($p_all as $p) {
            $role->revokePermissionTo($p); //Remove all permissions associated with role
        }

        foreach ($permissions as $permission) {
            $p = Permission::where('id', '=', $permission)->firstOrFail(); //Get corresponding form //permission in db
            $role->givePermissionTo($p);  //Assign permission to role
        }

        return redirect('web?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Role'. $role->name.' actualizado!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return redirect()->route('core.roles.index')
            ->with('flash_message',
             'Role deleted!');

    }
}