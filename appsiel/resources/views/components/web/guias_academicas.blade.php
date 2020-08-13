
<div class="container-fluid">
    <h1>Consulta de guías académicas</h1>
    <hr>

    <form class="form-inline" action="/action_page.php">
    
        <div class="col-md-4">
            <div class="form-group">
                <label for="curso_id">Seleccionar un curso:</label> &nbsp;&nbsp;
                {{ Form::select('curso_id', $cursos, null, [ 'id' => 'curso_id', 'class' => 'form-control', 'onchange' => 'cargarAsignaturas(this)' ] ) }}
            </div> 
        </div>

        <div class="col-md-4">

            <div class="form-group">
                {{ Form::Spin(32) }}
            </div>
            
            <div class="form-group">
                <label for="curso_id">Seleccionar una asignatura:</label> &nbsp;&nbsp;
                {{ Form::select('asignatura_id', [], null, [ 'id' => 'asignatura_id', 'class' => 'form-control', 'onchange' => 'habiltarBtnConsulta(this)' ] ) }}
            </div>  
        </div>
        
        <div class="col-md-4">
            <button class="btn btn-primary" id="btn_consultar" >Consultar</button>
        </div>

    </form>

    <hr>
    
    {{ Form::Spin2(128) }}

    <div id="div_lista_guias_academicas">
    </div>

</div>

<script type="text/javascript">

    function cargarAsignaturas( obj )
    {
        
        $("#asignatura_id").html('');
        $('#div_lista_guias_academicas').html( '' );
        document.getElementById('btn_consultar').setAttribute("disabled", "disabled");
        
        if ( obj.value == '' )
        {
            alert('Debe seleccionar un curso.');
            document.getElementById('curso_id').focus();
            return false;
        }

        $('#div_spin').show();
        var curso_id = obj.value;
        var xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200)
            {
                $('#div_spin').hide();
                            
                $("#asignatura_id").html( this.responseText );

                $("#asignatura_id").focus();
            }
        };

        xhttp.open("GET", "{{ url('get_select_asignaturas') }}" + "/" + curso_id + "/" + null, true);
        xhttp.send();
    }

    function habiltarBtnConsulta( obj )
    {
        if ( obj.value == '' )
        {
            alert('Debe seleccionar una asignatura.');
            document.getElementById('btn_consultar').setAttribute("disabled", "disabled");
            obj.focus();
            return false;
        }

        document.getElementById('btn_consultar').removeAttribute("disabled");
    }

    document.getElementById("btn_consultar").addEventListener("click", function(event){
        
        event.preventDefault();
        
        $('#div_spin2').show();
        
        var curso_id = document.getElementById('curso_id').value;
        var asignatura_id = document.getElementById('asignatura_id').value;

        var xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200)
            {
                $('#div_spin2').hide();
                            
                $("#div_lista_guias_academicas").html( this.responseText );
            }
        };

        xhttp.open("GET", "{{ url('pw_guias_planes_clases') }}" + "/" + curso_id + "/" + asignatura_id, true);
        xhttp.send();

    });

</script>
