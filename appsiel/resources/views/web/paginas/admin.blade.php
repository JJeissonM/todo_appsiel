@extends('web.templates.main')

@section('content')

    {{ Form::bsMigaPan($miga_pan) }}

    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between ">
                PAGINAS
            </div>
            <div class="card-body d-flex justify-content-between flex-wrap">
                <h5 class="card-title">Página</h5>
                <a href="{{url('paginas/create').$variables_url}}" style="color: #0000FF;" >+ Agregar página</a>
                <table class="table">
                    <thead>
                    <th>Nombre</th>
                    <th>Enlace</th>
                    <th>Estado</th>
                    <th>Principal</th>
                    <th>Acción</th>
                    </thead>
                    <tbody id="paginas">
                    @foreach($paginas as $pagina)
                        <tr>
                            <td>{{$pagina->titulo}}</td>
                            <td><a href="{{url('/'.str_slug($pagina->titulo))}}">{{url('/'.str_slug($pagina->titulo))}}</a></td>
                            <td>{{$pagina->estado}}</td>
                            <td>{{$pagina->pagina_inicio? 'Principal' : 'Default'}}</td>
                            <td>
                                <a href="" title="Configuración de página" class="btn bg-warning"><i class="fa fa-edit"></i></a>
                                <a href="{{url('')}}/" title="Duplicar página" class="btn bg-primary"><i class="fa fa-venus-double"></i></a>
                                <a href="{{url('')}}/" title="Eliminar página" class="btn bg-danger"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

