@extends('layouts.principal')

@section('content')

{{ Form::bsMigaPan($miga_pan) }}
    <hr>

    @include('layouts.mensajes')

<div class="container-fluid">
    <div class="marco_formulario">
        <h4>Nuevo registro</h4>
        <hr>
        {{ Form::open(array('url' => 'core/roles')) }}

        <div class="row" style="padding:5px;">
            {{ Form::bsText('name',null,"Nombre del perfil: ",[]) }}
        </div>

        <h4 align="center"><b>Asignación de permisos</b></h4>

        <div class='form-group'>
            <?php
                $aplicacion = 'Sin asignar';
                for($i=0;$i<count($permissions);$i++){
                    
                    /*if ($permissions[$i]['descripcion']==0) {
                        echo '<h5>Sin asignar</h5>';
                    }*/

                    if ($aplicacion!=$permissions[$i]['descripcion']) {
                        echo '<h3>  Permisos para la APP '.$permissions[$i]['descripcion'].'</h3><hr>';
                        $aplicacion=$permissions[$i]['descripcion'];
                    }
                                        
                    echo Form::checkbox('permissions[]',  $permissions[$i]['id'] );
                    echo Form::label($permissions[$i]['name'], ucfirst($permissions[$i]['name'])).'<br>';
                }

            ?>
        </div>

        {{ Form::hidden('url_id',Input::get('id'))}}
        {{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
        
        {{ Form::submit('Add', array('class' => 'btn btn-primary')) }}
        
        <a href="{{ url('web?id=7&id_modelo=9') }}" class="btn btn-danger">Cancel</a>

        {{ Form::close() }}
    </div>
</div>
<br/><br/>

@endsection