@extends('web.templates.main')

@section('style')
    <link rel="stylesheet" href="{{asset('css/sweetAlert2.min.css')}}">
@endsection

@section('content')

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
                            <td><a href="{{url('/'.str_slug($pagina->slug))}}">{{url('/'.$pagina->slug)}}</a></td>
                            <td>{{$pagina->estado}}</td>
                            <td>{{$pagina->pagina_inicio? 'Principal' : 'Default'}}</td>
                            <td>
                                <a href="{{route('paginas.edit',$pagina->id).$variables_url}}" title="Configuración de página" class="btn bg-warning"><i class="fa fa-edit"></i></a>
                                <a href="{{url('')}}/" title="Eliminar página" class="btn bg-danger" onclick="eliminarPagina(event,{{$pagina->id}})"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="{{asset('assets/js/axios.min.js')}}"></script>
    <script src="{{asset('js/sweetAlert2.min.js')}}"></script>

    <script>

        function eliminarPagina(event,id){

            event.preventDefault();

            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, bórralo!'
            }).then((result) => {
                if (result.value) {

                    const url = '{{url('')}}/'+'paginas/'+id;

                    axios.delete(url)
                        .then(function (response) {

                            const data = response.data;
                            if(data.status == 'ok'){

                                Swal.fire(

                                    'Eliminado!',
                                    'Su archivo ha sido eliminado.',
                                    'success'
                                );

                                setTimeout(function(){
                                    location.reload();
                                },3000);

                            }else {
                                Swal.fire(
                                    'Error!',
                                     data.message,
                                    'danger'
                                )
                            }

                        });

                }
            });

        }

    </script>
@endsection
