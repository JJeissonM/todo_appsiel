<style type="text/css">
    .icon {
        cursor: pointer;
        float: left;
        text-align: center;
        font-size: 45px;
        color: #3d6983;
    }

    .icon > p {
        font-size: 14px;
    }

    .icon:hover {
        font-size: 40px;
        color: #9400d3 !important;
    }

    .buscar {
        margin-top: 40px;
        margin-bottom: 40px;
        height: 40px;
        padding: 15px;
        border: 2px solid;
        border-color: #3d6983;
        width: 70%;
        border-radius: 10px;
        -webkit-box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
        -moz-box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
        box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
    }

    .buscar:focus {
        border-color: #9400d3;
    }
</style>


<!-- END SCROLL TOP BUTTON -->

<!-- Start main content -->
<main>


    <div class="col-md-12">
        <div class="alert alert-success" role="alert">
            <h3 style="text-align: center;">Listado de íconos disponibles para configurar su sitio web!</h3>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <center><input class="buscar" type="text" id="buscar" placeholder="Buscar íconos..."
                               onkeyup="buscar()"/></center>
            </div>
        </div>
        <div class="col-md-12" id="txt">
            <input type="hidden" id="nombre">
            @foreach($iconos as $i)
                <div class="col-md-3 icon" id="{{$i->icono}}" onclick="seticon(this.id)">
                    <i class="fa fa-{{$i->icono}}"></i>
                    <p id="icono">{{$i->icono}}</p>
                </div>
            @endforeach
        </div>
    </div>

</main>


<script type="text/javascript">
    var iconos = <?php echo json_encode($iconos); ?>;


    function buscar() {
        $("#txt").html("");
        var texto = $("#buscar").val();
        var nuevoArray = [];
        iconos.forEach(function (i) {
            if (i.icono.indexOf(texto) != -1) {
                nuevoArray.push(i);
            }
        });
        arrayDraw(nuevoArray);
    }

    function seticon(icono) {
        //var inp = $("#iconotxt").val();
        var inp = $("#nombre").val();
        $("#"+inp).val(icono);
        $("#iconotxt" ).val(icono);
        $("#exampleModal").modal('hide');
        $("#exampleModal").removeClass('modal-open');
        $('.modal-backdrop').remove();
    }

    function arrayDraw(array) {
        var html = "";
        array.forEach(function (i) {
            html = html + "<div class='col-md-3 icon'id='" + i.icono + "' onclick='seticon(this.id)'><i class='fa fa-" + i.icono + "'></i>" +
                "<p id='icono'>" + i.icono + "</p></div>";
        });

        $("#txt").html(html);
    }
</script>

