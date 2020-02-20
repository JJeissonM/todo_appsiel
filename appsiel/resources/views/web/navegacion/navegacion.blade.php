@extends('web.templates.main')

@section('content')

    {{ Form::bsMigaPan($miga_pan) }}

    <div class="title" style="margin: 0 0 20px 20px;">
        <h1>Vista Previa del Menú de Navegación</h1>
    </div>

    {{Form::navegacion('logo')}}

    <main style="margin-top: 150px">

        <section id="">
            <div class="container">
                <div class="card">
                    <div class="card-header">
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-primary" data-toggle="modal"
                                data-target="#exampleModalCenter">
                            Nuevo Item
                        </button>

                    </div>
                    <div class="card-body">
                        <table class="table table-responsive" style="margin-top: 20px;">
                            <thead>
                            <th>Id</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Enlace</th>
                            <th>Acciones</th>
                            </thead>
                            <tbody>
                            <tr>
                                <td>algo</td>
                                <td>algo</td>
                                <td>algo</td>
                                <td>algo</td>
                                <td>
                                    <a href="" class="btn btn-info"></a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Modales -->
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Creando Nuevo Item para el menú de
                        navegación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{route('navegacion.store')}}">
                        {!! csrf_field() !!}
                        <input type="hidden" name="parent" value="0">
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="formGroupExampleInput">Nombre</label>
                                    <input type="text" class="form-control" id="formGroupExampleInput" placeholder="" name="titulo">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="formGroupExampleInput">Descripción</label>
                                    <input type="text" class="form-control" id="formGroupExampleInput" placeholder="" name="descripcion">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="customFile" for="customFile">Icono(opcional)</label>
                                    <input type="file" class="form-control" id="" name="icono">
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label class="customFile" for="customFile">Enlace hacia donde apunta el item</label>
                                    <input type="text" class="form-control" id="" name="enlace">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>

@endsection