@extends('web.templates.main')

@section('style')
    <link rel="stylesheet" href="{{asset('css/sweetAlert2.min.css')}}">
@endsection

@section('content')

    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between ">
                Redes Sociales
            </div>
            <div class="card-body d-flex justify-content-end flex-wrap">
                <a href="{{url('sociales/create').$variables_url}}" style="color: #0000FF;" >+ Agregar nueva red social</a>
                <table class="table">
                    <thead>
                    <th>Nombre</th>
                    <th>Enlace</th>
                    <th>Acción</th>
                    </thead>
                    <tbody id="paginas">
                      @foreach($redes as $item)
                         <tr>
                             <td><a href="" class="btn btn-info" style="border-radius: 50%; line-height: 10px;"><i class="fa fa-{{$item->icono}}"></i></a>{{' '.$item->nombre}}</td>
                             <td><a href="{{$item->enlace}}">{{$item->enlace}}</a></td>
                             <td>
                                 <a href="{{route('sociales.edit',$item->id).$variables_url}}" title="Configuración de red social" class="btn bg-warning"><i class="fa fa-edit"></i></a>
                                 <a href="{{url('')}}/" title="Eliminar página" class="btn bg-danger" onclick="eliminar(event,{{$item->id}})"><i class="fa fa-trash"></i></a>
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

        function eliminar(event,id){

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
                    const url = '{{url('')}}/'+'sociales/'+id;
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
