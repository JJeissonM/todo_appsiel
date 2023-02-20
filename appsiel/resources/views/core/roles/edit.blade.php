@extends('layouts.principal')

@section('content')

{{ Form::bsMigaPan($miga_pan) }}
    <hr>

    @include('layouts.mensajes')

<div class="container-fluid">
    <div class="marco_formulario">
        <h4>Modificanco el registro {{$role->name}}</h4>
        <hr>

        {{ Form::model($role, array('route' => array('core.roles.update', $role->id), 'method' => 'PUT')) }}

        <div class="row" style="padding:5px;">
            {{ Form::bsText('name',null,"Nombre del perfil: ",['readonly','readonly']) }}
        </div>

        <h4 align="center"><b>Asignación de permisos</b></h4>

        <div class='form-group'>
            <?php
                $aplicacion = 'Sin asignar';
                for($i=0;$i<count($permissions);$i++){

                    if ($aplicacion!=$permissions[$i]['descripcion']) {
                        echo '<h3>  Permisos para la APP '.$permissions[$i]['descripcion'].'</h3><hr>';
                        $aplicacion=$permissions[$i]['descripcion'];
                    }
                                        
                    echo Form::checkbox('permissions[]',  $permissions[$i]['id'], $role->permissions );
                    echo Form::label($permissions[$i]['name'], $permissions[$i]['id'] . ' - ' . ucfirst($permissions[$i]['name'])).'<br>';
                }

            ?>
        </div>

        {{ Form::hidden('url_id',Input::get('id'))}}
        {{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}

        <br>
        {{ Form::submit('Save', array('class' => 'btn btn-primary')) }}
        
        <a href="{{ url('web?id=7&id_modelo=9') }}" class="btn btn-danger">Cancel</a>

        {{ Form::close() }}
        
    </div>
</div>
<br/><br/>
@endsection