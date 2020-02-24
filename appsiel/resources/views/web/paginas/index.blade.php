@extends('web.templates.main')

@section('content')

    <div class="container">

        <div class="card">
            <div class="card-header d-flex justify-content-between ">
                SETUP
                <a href="{{url('pagina/administrar').$variables_url}}" style="color: #0000FF;">Administrar Páginas</a>
            </div>
            <div class="card-body d-flex justify-content-between flex-wrap">
                <h5 class="card-title">Página</h5>
                <a href="{{url('paginas/create').$variables_url}}" style="color: #0000FF;" >+ Agregar página</a>
                <div class="form-group" style="display: inline-block; width: 100%;">
                    <select class="form-control" id="paginas" onchange="buscarSecciones(event)">
                        @foreach($paginas as $pagina)
                            <option value="{{$pagina->id}}">{{$pagina->titulo}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-footer text-muted">
                <div class="card-header d-flex justify-content-between">
                    EN ESTA PÁGINA
                    <a href="" id="seccion" style="color: #0000FF;">+ Agreagar sección</a>
                </div>
                <div class="card-body">
                     <table class="table">
                        <tbody id="secciones">
                        </tbody>
                     </table>
                </div>
            </div>
        </div>
    </div>

@endsection


@section('script')
    <script src="{{asset('assets/js/axios.min.js')}}"></script>
    <script type="text/javascript">

          $(function(){
              const select =  document.getElementById('paginas');
              rellenarSelect(select);
          });

          function buscarSecciones(event){
             let select = event.target;
             rellenarSelect(select);
          }

          function rellenarSelect(select){
              select = select.options[select.selectedIndex].value;
              const url = '{{url('')}}/'+'pagina/secciones/'+select;

              /*add el id de la pagina selecionada para armar la url para agragarle una nueva sección
               a la pania*/
              let seccion =  document.getElementById('seccion');
              seccion.setAttribute('href','{{url('pagina/addSeccion')}}/'+select+'{{$variables_url}}');

              axios.get(url)
                  .then(function (response) {
                      const data =  response.data;
                      let tbody = document.getElementById('secciones');
                      tbody.innerHTML = '';
                      let secciones = data.secciones;
                      $html = '';
                      secciones.forEach(function (item) {

                          if(item.seccion !== 'navegacion' && item.seccion !==  'pie de pagina' ){
                          $html += `<tr>
                              <td>${item.seccion}</td>
                              <td>
                                  <a href="{{url('seccion')}}/${item.widget_id}{{$variables_url}}" style="color:white;" title="Editar sección" class="btn bg-warning"><i class="fa fa-edit"></i></a>
                                  <a href="{{url('')}}/" title="Mover sección" style="color:white;" class="btn bg-info"><i class="fa fa-arrows-alt"></i></a>
                                  <a href="{{url('')}}/" title="Duplicar sección" style="color:white;" class="btn bg-primary"><i class="fa fa-venus-double"></i></a>
                                  <a href="{{url('')}}/" title="Borrar sección" style="color:white;" class="btn bg-danger"><i class="fa fa-trash"></i></a>
                              </td>
                              </tr>`;
                          }else {
                              $html += `<tr>
                                            <td colspan="2">${item.seccion}</td>
                                        </tr>`
                          }

                      });
                      tbody.innerHTML = $html;
                  });
          }

    </script>
@endsection