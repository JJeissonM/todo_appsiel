<br><br>
<style>
    .odontograma{{ $consulta->id }}{
      display: grid;
      grid-template-columns: repeat(17, auto);
      grid-template-rows: repeat(5, auto);
    }
    .blankspace-left{
      grid-area: 2/1/5/4;   
    }
    .line-v{
      grid-area: 1/9/6/10;
      background-color: black;
      width: 4px;
      margin: 0 4px;
    }
    .line-h{
      grid-area: 3/1/4/18;
      background-color: black;
      height: 4px;
      margin: 8px 0;
    }
    .blankspace-right{
      grid-area: 2/15/5/span 3;
    }
    .badge-icon{
      display: inline;
      padding: 2px;
      margin-right: 1rem;
      border: 1px solid #ddd;
    }
    .badge-icon > img{
      height: 14px;
    }
    .list-group-item{
      padding: 0;
    }
    
    .red, .caries{
      color: red !important;
      fill: red !important;
    }
    .blue, .resina{
      color: blue !important;
      fill: blue !important;
    }
    .black, .amalgama{
      color: black !important;
      fill: black !important;
    }
    .green{
      color: green !important;
      fill: green !important;
    }
    .gray{
      color: rgb(200, 200, 200) !important;
      fill: rgb(200, 200, 200) !important;
    }


    /*bootstrap dropdown submenu*/
    .dropdown-menu>li /* To prevent selection of text */
    {   position:relative;
        -webkit-user-select: none; /* Chrome/Safari */        
        -moz-user-select: none; /* Firefox */
        -ms-user-select: none; /* IE10+ */
        /* Rules below not implemented in browsers yet */
        -o-user-select: none;
        user-select: none;
        cursor:pointer;
    }
    .dropdown-menu .sub-menu 
    {
        left: 100%;
        position: absolute;
        top: 0;
        display:none;
        margin-top: -1px;
        border-top-left-radius:0;
        border-bottom-left-radius:0;
        border-left-color:#fff;
        box-shadow:none;
    }
    .right-caret:after,.left-caret:after
    {  content:"";
        border-bottom: 5px solid transparent;
        border-top: 5px solid transparent;
        display: inline-block;
        height: 0;
        vertical-align: middle;
        width: 0;
        margin-left:5px;
    }
    .right-caret:after
    {   border-left: 5px solid #ffaf46;
    }
    .left-caret:after
    {   border-right: 5px solid #ffaf46;
    }
  </style>
<div class="row">
    <div class="col-md-9">
      <div class="panel panel-default">
        <div class="panel-heading">Odontograma</div>
        <div class="panel-body">
          <div class="odontograma{{ $consulta->id }}">
            <div class="line-h"></div>
            <div class="line-v"></div>
            <div class="blankspace-left"></div>
            <div class="blankspace-right"></div>
            <?php
              $teeth = array(18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28,55,54,53,52,51,61,62,63,64,65,85,84,83,82,81,71,72,73,74,75,48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38);
            ?>
            @foreach ($teeth as $n)
            <div class="dropdown">
              <a class="dropdown-toggle" type="button" id="menu{{$n}}-{{ $consulta->id }}" data-toggle="dropdown">
                  <div id="_{{$n}}-{{ $consulta->id }}" style="position: relative">
                  <div class="text-center" style="width: 50px">{{$n}}</div>
                  <img src="{{ asset("assets/img/odontograma") }}/diente.svg" alt="" width="50px">
                  <img class="all" height="50" width="50" style="position: absolute;bottom: 0; left:0;z-index: 1; border:none" src="{{ asset("assets/img/odontograma") }}/white.svg" alt="diente${n}">
          
                  <svg viewBox="0 0 52.916666 52.916668" height="50" width="50" style="position: absolute;bottom: 0; left:0;">        
                      <g transform="translate(0,-244.08332)">
                      <path transform="matrix(0.26458333,0,0,0.26458333,0,244.08332)" class="mesial" d="M 149.61354,147.35924 135.28283,133.02688 V 99.944377 66.861873 l 14.33629,-14.334634 14.33629,-14.334634 1.28223,1.329751 c 3.25535,3.375981 6.98493,8.215465 10.00463,12.98195 12.46816,19.680488 16.73876,44.050683 11.67837,66.642574 -1.69295,7.55811 -3.88764,13.67091 -7.43202,20.70018 -2.83567,5.62373 -4.75495,8.747 -8.2191,13.37507 -2.32265,3.10302 -6.87999,8.46948 -7.19251,8.46948 -0.073,0 -6.58158,-6.44957 -14.46347,-14.33237 z" style="opacity:1;fill:white;fill-opacity:1;stroke:white;stroke-width:0.95447487;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;paint-order:markers stroke fill"/>
                      </g>
                  </svg>
                  <svg viewBox="0 0 52.916666 52.916668" height="50" width="50" style="position: absolute;bottom: 0; left:0;">           
                      <g transform="translate(0,-244.08332)">
                      <path transform="matrix(0.26458333,0,0,0.26458333,0,244.08332)" class="bucal" d="M 53.369544,49.600015 39.011871,35.240684 40.238392,34.094713 c 5.670138,-5.29776 12.386677,-9.844387 20.41942,-13.822521 7.692529,-3.809645 13.827069,-5.912889 22.097087,-7.576047 27.062281,-5.4424112 55.875211,2.402264 76.348351,20.786781 l 1.97587,1.774295 -14.34941,14.351062 -14.34941,14.351063 H 100.05376 67.727218 Z" style="opacity:1;fill:white;fill-opacity:1;stroke:white;stroke-width:0.95447487;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;paint-order:markers stroke fill"/>
                      </g>
                  </svg>
                  <svg viewBox="0 0 52.916666 52.916668" height="50" width="50" style="position: absolute;bottom: 0; left:0;">            
                      <g transform="translate(0,-244.08332)">
                      <path transform="matrix(0.26458333,0,0,0.26458333,0,244.08332)" class="distal" style="opacity:1;fill:white;fill-opacity:1;stroke:white;stroke-width:0.95447487;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;paint-order:markers stroke fill" d="M 33.60909,159.22936 C 20.647209,144.69202 12.822605,126.41083 11.269003,107.03458 10.896493,102.38869 11.088431,93.327938 11.651872,88.960622 14.030898,70.520446 21.988295,53.424365 34.631775,39.589416 l 1.384737,-1.515229 14.277821,14.267567 14.27782,14.267566 v 33.336711 33.336709 l -14.206101,14.20443 c -7.813356,7.81244 -14.286078,14.20444 -14.38383,14.20444 -0.09775,0 -1.165659,-1.10802 -2.373132,-2.46225 z" inkscape:connector-curvature="0" />
                      </g>
                  </svg>
                  <svg viewBox="0 0 52.916666 52.916668" height="50" width="50" style="position: absolute;bottom: 0; left:0;">     
                      <g transform="translate(0,-244.08332)">
                      <path transform="matrix(0.26458333,0,0,0.26458333,0,244.08332)" class="lingual" d="m 91.972541,188.69389 c -18.995548,-1.75201 -37.47979,-9.87045 -51.644049,-22.68252 l -1.38896,-1.25636 14.267567,-14.28488 14.267566,-14.28488 h 32.579095 32.5791 l 14.2467,14.24838 14.2467,14.24837 -1.46712,1.34586 c -2.44759,2.24527 -6.65474,5.52828 -9.85537,7.69055 -11.6807,7.89118 -24.46745,12.71697 -38.7646,14.62996 -3.70949,0.49634 -15.178082,0.70417 -19.066629,0.34552 z" style="opacity:1;fill:white;fill-opacity:1;stroke:white;stroke-width:0.95447487;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;paint-order:markers stroke fill"/>
                      </g>
                  </svg>
                  <svg viewBox="0 0 52.916666 52.916668" height="50" width="50" style="position: absolute;bottom: 0; left:0;">     
                      <g transform="translate(0,-244.08332)">
                      <path transform="matrix(0.26458333,0,0,0.26458333,0,244.08332)" class="oclusal" d="M 68.612763,100.0723 V 67.999956 H 99.927492 131.24222 V 100.0723 132.14464 H 99.927492 68.612763 Z" style="opacity:1;fill:white;fill-opacity:1;stroke:white;stroke-width:0.95447487;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;paint-order:markers stroke fill"/>
                      </g>
                  </svg>
          
                  </div>
              </a>
              <ul class="dropdown-menu" role="menu" aria-labelledby="menu{{$n}}-{{ $consulta->id }}">    
                  <li>
                  <a class="trigger right-caret"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/caries.svg" alt=""></span>Caries</a>
                  <ul class="dropdown-menu sub-menu">
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'bucal',feat:'caries'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/caries.svg" alt=""></span>Caries bucal</a></li>
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'lingual',feat:'caries'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/caries.svg" alt=""></span>Caries lingual</a></li>
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'distal',feat:'caries'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/caries.svg" alt=""></span>Caries distal</a></li>
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'mesial',feat:'caries'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/caries.svg" alt=""></span>Caries mesial</a></li>
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'oclusal',feat:'caries'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/caries.svg" alt=""></span>Caries oclusal</a></li>
                  </ul>
                  </li>
                  <li>
                  <a class="trigger right-caret"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/resina.svg" alt=""></span>Resina</a>
                  <ul class="dropdown-menu sub-menu">
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'bucal',feat:'resina'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/resina.svg" alt=""></span>Resina bucal</a></li>
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'lingual',feat:'resina'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/resina.svg" alt=""></span>Resina lingual</a></li>
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'distal',feat:'resina'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/resina.svg" alt=""></span>Resina distal</a></li>
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'mesial',feat:'resina'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/resina.svg" alt=""></span>Resina mesial</a></li>
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'oclusal',feat:'resina'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/resina.svg" alt=""></span>Resina oclusal</a></li>
                  </ul>
                  </li>
                  <li>
                  <a class="trigger right-caret"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/amalgama.svg" alt=""></span>Amalgama</a>
                  <ul class="dropdown-menu sub-menu">
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'bucal',feat:'amalgama'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/amalgama.svg" alt=""></span>Amalgama bucal</a></li>
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'lingual',feat:'amalgama'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/amalgama.svg" alt=""></span>Amalgama lingual</a></li>
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'distal',feat:'amalgama'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/amalgama.svg" alt=""></span>Amalgama distal</a></li>
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'mesial',feat:'amalgama'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/amalgama.svg" alt=""></span>Amalgama mesial</a></li>
                      <li><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'oclusal',feat:'amalgama'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/amalgama.svg" alt=""></span>Amalgama oclusal</a></li>
                  </ul>
                  </li>
                  <li class="presentation"><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'all',feat:'sellante'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/sellante.svg" alt=""></span>Sellante</a></li>
                  <li class="presentation"><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'all',feat:'sellanteindicado'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/sellanteindicado.svg" alt=""></span>Sellante Indicado</a></li>
                  <li class="presentation"><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'all',feat:'extraccionindicada'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/extraccionindicada.svg" alt=""></span>Extraccion Indicada</a></li>
                  <li class="presentation"><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'all',feat:'conendodoncia'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/conendodoncia.svg" alt=""></span>Con endodoncia</a></li>
                  <li class="presentation"><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'all',feat:'protesis'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/protesis.svg" alt=""></span>Protesis</a></li>
                  <li class="presentation"><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'all',feat:'necrosispulpar'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/necrosispulpar.svg" alt=""></span>Necrosis Pulpar</a></li>
                  <li class="presentation"><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'all',feat:'protesisindicada'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/protesisindicada.svg" alt=""></span>Protesis Indicada</a></li>
                  <li class="presentation"><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'all',feat:'clinicamenteausente'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/clinicamenteausente.svg" alt=""></span>Clinicamente Ausente</a></li>
                  <li class="presentation"><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'all',feat:'coronadesadaptada'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/coronadesadaptada.svg" alt=""></span>Corona desadaptada</a></li>
                  <li class="presentation"><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'all',feat:'reseciongingival'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/reseciongingival.svg" alt=""></span>Resecion gingival</a></li>
                  <li class="presentation"><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'all',feat:'sano'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/sano.svg" alt=""></span>Sano</a></li>
                  <li class="presentation"><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'all',feat:'corona'})"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/corona.svg" alt=""></span>Corona</a></li>
                  <li class="presentation"><a role="menuitem" tabindex="-1" href="javascript:grama{{ $consulta->id }}({n:{{$n}},nombre:'all',feat:'white'})">Reset</a></li>
              </ul>
              </div>
            @endforeach
          </div>    
        </div>
      </div>
      
    </div>
    <div class="col-md-3">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Caracteristicas</h3>
        </div>
        <div class="panel-body">
          <ul class="list-group">
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/caries.svg" alt=""></span>Caries</li>
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/resina.svg" alt=""></span>Resina</li>
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/amalgama.svg" alt=""></span>Amalgama</li>
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/sellante.svg" alt=""></span>Sellante</li>
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/sellanteindicado.svg" alt=""></span>Sellante Indicado</li>
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/extraccionindicada.svg" alt=""></span>Extraccion Indicada</li>
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/conendodoncia.svg" alt=""></span>Con endodoncia</li>
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/protesis.svg" alt=""></span>Protesis</li>
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/necrosispulpar.svg" alt=""></span>Necrosis Pulpar</li>
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/protesisindicada.svg" alt=""></span>Protesis Indicada</li>
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/clinicamenteausente.svg" alt=""></span>Clinicamente Ausente</li>
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/coronadesadaptada.svg" alt=""></span>Corona desadaptada</li>
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/reseciongingival.svg" alt=""></span>Resecion gingival</li>
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/sano.svg" alt=""></span>Sano</li>
            <li class="list-group-item"><span class="badge-icon"><img src="{{ asset("assets/img/odontograma") }}/corona.svg" alt=""></span>Corona</li>
          </ul>    
        </div>
      </div>
      
    </div>  
  </div>
  <div class="row">
    <div class="panel panel-default">
      <div class="panel-heading">Detalles:</div>
      <div class="panel-body">
        <div id="datails{{ $consulta->id }}" style="height: 100px;">

        </div>    
      </div>
    </div>      
  </div>  
  <div class="row">
    <form id="formulario_odontograma{{ $consulta->id }}" action="{{ url('consultorio_medico/odontograma') }}" method="POST">
      <input type="hidden" name="_token"  value="{{@csrf_token()}}">
      <input type="hidden" name="odontograma_id" id="odontograma_id{{ $consulta->id }}">
      <input type="hidden" name="id_consultas" value="{{ $consulta->id }}">
      <input type="hidden" name="odontograma_data" id="odontograma_data{{ $consulta->id }}">
      <div class="col-md-3">Observaciones</div>
      <div class="col-md-9"><input class="form-control" name="observaciones" type="text"></div>
      <button class="btn btn-primary" type="submit">GUARDAR</button>
    </form>
    
  </div>  
<script>
  'use strict'

    document.addEventListener('DOMContentLoaded', function(){
    let odontograma{{ $consulta->id }} = document.querySelector(".odontograma{{ $consulta->id }}");

    document.querySelector('#formulario_odontograma{{ $consulta->id }}').addEventListener('submit', function(evt){
      evt.preventDefault();
      var url = "{{ url('consultorio_medico/odontograma') }}"
      var data = $('#formulario_odontograma{{ $consulta->id }}').serialize();
      $.post(url, data, function (datos) {
          //odontograma_id{{ $consulta->id }}
          let dato = datos;
          $('#odontograma_id{{ $consulta->id }}').val(dato);
      });
    })
    
      var url = `{{ url('consultorio_medico/odontograma') }}/{{ $consulta->id }}`
      $.get(url, function (datos) {
          //odontograma_id{{ $consulta->id }}
          console.log(datos)
          var data = JSON.parse(datos.odontograma_data)
          for (const prop in data) {
            //console.log(`obj.${prop} = ${data[prop]}`);
            for (const propi in data[prop]) {
              console.log(`n.${prop} pd.${propi} feat.${data[prop][propi]}`);
              let ms = data[prop][propi];
              grama{{ $consulta->id }}({n:prop,nombre:propi,feat:ms});
            }
          }
          if(datos.id != 0){
            $('#odontograma_id{{ $consulta->id }}').val(datos.id);
          }            
      });


    });  

    let dientes{{ $consulta->id }} = {}

    function grama{{ $consulta->id }}({n,nombre,feat}){
        if(nombre == 'all'){
            document.querySelector(`#_${n}-{{ $consulta->id }}`).querySelector(`.bucal`).classList = "bucal"
            document.querySelector(`#_${n}-{{ $consulta->id }}`).querySelector(`.lingual`).classList = "lingual"
            document.querySelector(`#_${n}-{{ $consulta->id }}`).querySelector(`.distal`).classList = "distal"
            document.querySelector(`#_${n}-{{ $consulta->id }}`).querySelector(`.mesial`).classList = "mesial"
            document.querySelector(`#_${n}-{{ $consulta->id }}`).querySelector(`.oclusal`).classList = "oclusal"
            document.querySelector(`#_${n}-{{ $consulta->id }}`).querySelector(`.all`).setAttribute('src',`{{ asset("assets/img/odontograma") }}/${feat}.svg`)    
            dientes{{ $consulta->id }}[`${n}`] = {all: feat}
            document.querySelector("#datails{{ $consulta->id }}").innerHTML = `${n} ${dientes{{ $consulta->id }}[`${n}`]['all']}`
        }else{
            dientes{{ $consulta->id }}[`${n}`] = {...dientes{{ $consulta->id }}[`${n}`], all: ""}
            delete dientes{{ $consulta->id }}[`${n}`].all    
            document.querySelector(`#_${n}-{{ $consulta->id }}`).querySelector(`.all`).setAttribute('src',`{{ asset("assets/img/odontograma") }}/white.svg`)  
            document.querySelector(`#_${n}-{{ $consulta->id }}`).querySelector(`.${nombre}`).classList.toggle(`${feat}`)
            var detalles = ""
            if(nombre == "bucal"){
                dientes{{ $consulta->id }}[`${n}`] = {...dientes{{ $consulta->id }}[`${n}`], bucal: `${feat}`}
            }
            if(nombre == "lingual"){
                dientes{{ $consulta->id }}[`${n}`] = {...dientes{{ $consulta->id }}[`${n}`], lingual: `${feat}`}
            }
            if(nombre == "distal"){
                dientes{{ $consulta->id }}[`${n}`] = {...dientes{{ $consulta->id }}[`${n}`], distal: `${feat}`}
            }
            if(nombre == "mesial"){
                dientes{{ $consulta->id }}[`${n}`] = {...dientes{{ $consulta->id }}[`${n}`], mesial: `${feat}`}
            }
            if(nombre == "oclusal"){
                dientes{{ $consulta->id }}[`${n}`] = {...dientes{{ $consulta->id }}[`${n}`], oclusal: `${feat}`}
            }
            
            detalles += dientes{{ $consulta->id }}[`${n}`].bucal != undefined ? `${n} bucal ${dientes{{ $consulta->id }}[`${n}`].bucal} <br>` : ''
            detalles += dientes{{ $consulta->id }}[`${n}`].lingual != undefined ? `${n} lingual ${dientes{{ $consulta->id }}[`${n}`].lingual} <br>` : ''
            detalles += dientes{{ $consulta->id }}[`${n}`].distal != undefined ? `${n} distal ${dientes{{ $consulta->id }}[`${n}`].distal} <br>` : ''
            detalles += dientes{{ $consulta->id }}[`${n}`].mesial != undefined ? `${n} mesial ${dientes{{ $consulta->id }}[`${n}`].mesial} <br>` : ''
            detalles += dientes{{ $consulta->id }}[`${n}`].oclusal != undefined ? `${n} oclusal ${dientes{{ $consulta->id }}[`${n}`].oclusal} <br>` : ''
            document.querySelector("#datails{{ $consulta->id }}").innerHTML = detalles    
        }
            if(feat == "white"){
                delete dientes{{ $consulta->id }}[`${n}`]
            }
            document.querySelector('#odontograma_data{{ $consulta->id }}').value = JSON.stringify(dientes{{ $consulta->id }});
            
    
    }

/*
fetch('data.json')
.then(response => response.json())
.then(data => {
  for (const prop in data) {
    //console.log(`obj.${prop} = ${data[prop]}`);
    for (const propi in data[prop]) {
      console.log(`n.${prop} pd.${propi} feat.${data[prop][propi]}`);
      let ms = data[prop][propi];
      grama({n:prop,nombre:propi,feat:ms});
    }
  }
});*/
</script>  

  @section('scripts2')
  <script>

    $(function(){
    $(".dropdown-menu > li > a.trigger").on("click",function(e){
        var current=$(this).next();
        var grandparent=$(this).parent().parent();
        if($(this).hasClass('left-caret')||$(this).hasClass('right-caret'))
            $(this).toggleClass('right-caret left-caret');
        grandparent.find('.left-caret').not(this).toggleClass('right-caret left-caret');
        grandparent.find(".sub-menu:visible").not(current).hide();
        current.toggle();
        e.stopPropagation();
    });
    $(".dropdown-menu > li > a:not(.trigger)").on("click",function(){
        var root=$(this).closest('.dropdown');
        root.find('.left-caret').toggleClass('right-caret left-caret');
        root.find('.sub-menu:visible').hide();
    });
    });     
  </script>
      
  @endsection