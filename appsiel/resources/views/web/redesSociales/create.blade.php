@extends('web.templates.main')

@section('style')
    <link rel="stylesheet" href="{{asset('assets/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/tagsinput.css')}}">
@endsection

@section('content')

     <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between ">
                Red Social
            </div>
            <div class="card-body">
                <form action="{{route('sociales.store').$variables_url}}" method="post">
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <div class="form-row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Nombre</label>
                                <span data-toggle="tooltip" title="Establece el nombre de la red social."> <i class="fa fa-question-circle"></i></span>
                                <input type="text" name="nombre" class="form-control" placeholder="About us">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Enlace</label>
                                <span data-toggle="tooltip" title="Establece el enlace a la red social."> <i class="fa fa-question-circle"></i></span>
                                <input type="text" maxlength="158" name="enlace" class="form-control" placeholder="https://">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Icono</label>
                                <span data-toggle="tooltip" title="Establece el icono de la red social."> <i class="fa fa-question-circle"></i></span>
                                <input data-toggle="modal" data-target="#exampleModal" name="icono" type="text" id="iconotxt" placeholder="Nombre del icono" class="form-control">
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

     <div class="modal" id="exampleModal" tabindex="-1" role="dialog">
         <div class="modal-dialog modal-lg" role="document">
             <div class="modal-content">
                 <div class="modal-header">
                     <h5 class="modal-title">Seleccionar Icono</h5>
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                         <span aria-hidden="true">&times;</span>
                     </button>
                 </div>
                 <div class="modal-body">
                     <div class="col-md-12">
                         {!! Form::iconos($iconos) !!}
                     </div>
                 </div>
             </div>
         </div>
     </div>

@endsection

@section('script')
    <script src="{{asset('assets/js/select2.min.js')}}"></script>
    <script src="{{asset('assets/js/tagsinput.js')}}"></script>
    <script>
        $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'right',
            html: true
        });
    </script>
@endsection
