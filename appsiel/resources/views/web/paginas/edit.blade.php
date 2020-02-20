@extends('web.templates.main')

@section('style')
    <link rel="stylesheet" href="{{asset('assets/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/tagsinput.css')}}">
@endsection

@section('content')

     <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between ">
                PAGINAS
            </div>
            <div class="card-body">
                {!! Form::open(['url' => route('paginas.update',$pagina->id).$variables_url, 'method' => 'put','files'=>true]) !!}
                    <div class="form-row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Titulo</label>
                                <input type="text" name="titulo" class="form-control" placeholder="About us" value="{{$pagina->titulo}}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Descripción</label>
                                <input type="text" maxlength="158" name="descripcion" class="form-control" placeholder="máximo 158 caracteres" value="{{$pagina->descripcion}}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Google Analytics</label>
                                <input type="text" name="codigo_google_analitics" class="form-control" placeholder="UA-149024927-1" value="{{$pagina->codigo_google_analitics}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Keywords</label>
                                <input type="text" data-role="tagsinput" name="meta_keywords" placeholder="palabras claves" value="{{$pagina->meta_keywords}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="customFile" for="customFile">Icono(opcional)</label>
                                <input type="file" class="form-control" id="" name="favicon">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Pagina Principal</label>
                                {{Form::select('pagina_inicio', ['0' => 'Default', '1' => 'Principal'], $pagina->pagina_inicio,['class'=>"form-control"])}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="estado">Estado</label>
                                {{Form::select('estado', ['Activa' => 'Activa', 'Inactiva' => 'Inactiva'], $pagina->estado,['class'=>"form-control"])}}
                            </div>
                        </div>
                        <div class="col-md-12 d-flex flex-row-reverse">
                            <button class="btn btn-info" style="margin-left: 10px;">Guardar</button>
                            <a href="{{url('paginas').$variables_url}}" class="btn btn-danger" style="margin-left: 10px; color: white">Cancelar</a>
                            <button type="reset" class="btn btn-warning" style="color: white;">Limpiar</button>
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="{{asset('assets/js/select2.min.js')}}"></script>
    <script src="{{asset('assets/js/tagsinput.js')}}"></script>
@endsection
