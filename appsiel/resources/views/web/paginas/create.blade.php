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
                <form action="{{route('paginas.store').$variables_url}}" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <div class="form-row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Titulo</label>
                                <input type="text" name="titulo" class="form-control" placeholder="About us">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Descripción</label>
                                <input type="text" maxlength="158" name="descripcion" class="form-control" placeholder="máximo 158 caracteres">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Google Analytics</label>
                                <input type="text" name="codigo_google_analitics" class="form-control" placeholder="UA-149024927-1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Keywords</label>
                                <input type="text" data-role="tagsinput" name="meta_keywords" placeholder="palabras claves">
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
                                <select class="form-control" name="pagina_inicio" >
                                    <option value="0">Default</option>
                                    <option value="1">Principal</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="estado">Estado</label>
                                <select id="estado" class="form-control" name="estado" >
                                    <option value="Activa">Activa</option>
                                    <option value="Inactiva">Inactiva</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 d-flex flex-row-reverse">
                            <button class="btn btn-info" style="margin-left: 10px;">Guardar</button>
                            <a href="{{url('paginas').$variables_url}}" class="btn btn-danger" style="margin-left: 10px; color: white">Cancelar</a>
                            <button type="reset" class="btn btn-warning" style="color: white;">Limpiar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="{{asset('assets/js/select2.min.js')}}"></script>
    <script src="{{asset('assets/js/tagsinput.js')}}"></script>
@endsection
