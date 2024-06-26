<style>

.contactenos-font {
    @if( !is_null($contactenos) )
        @if( !is_null($contactenos->configuracionfuente ) )
            font-family: <?php echo $contactenos->configuracionfuente->fuente->font; ?> !important;
        @endif
    @endif
}

</style>
<link rel="stylesheet" href="{{asset('css/sweetAlert2.min.css')}}">
<div class="container-wrapper contactenos-font">
    <div class="container">
        @if($contactenos != null)
        <div class="row" style="border-radius: 10px;background-color: white;">
            <div class=".col-sm-4 col-sm-offset-8">
                <div class="" style="padding: 5px; ">
                    <address style="color: black">
                        @if( $contactenos->empresa != '' )
                        <strong style="color: black">
                            <span class="contactenos-font" title="{{ $contactenos->empresa }}"> {{str_limit($contactenos->empresa,20)}} </span>
                        </strong>
                        <br>
                        @endif
                        @if( $contactenos->correo != '' )
                        <a href="mailto:{{ $contactenos->correo }}">
                            <span class="contactenos-font" style="font-size: 22px;" title="{{ $contactenos->correo }}"> {{str_limit($contactenos->correo,20)}}<br> </span>
                        </a>
                        @endif
                        @if( $contactenos->direccion != '' )
                        <span class="contactenos-font" title="{{ $contactenos->direccion }}"> {{str_limit($contactenos->direccion,20)}} </span>
                        <br>
                        @endif
                        @if( $contactenos->ciudad != '' )
                        <span class="contactenos-font" title="{{ $contactenos->ciudad }}"> {{str_limit($contactenos->ciudad,20)}} </span>
                        <br>
                        @endif
                        @if( $contactenos->telefono != '' )
                        <i class="fa fa-whatsapp"></i> {{$contactenos->telefono}}
                        @endif
                    </address>
                    <div class="col-md-12 contactenos-font">
                        <input type="hidden" name="contactenos_id" id="contactenos_id" value="{{$contactenos->id}}">
                        <div class="form-group">
                            <input type="text" name="names" class="form-control" id="names" placeholder="Nombre completo" required="">
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-control" id="email" placeholder="Email" required="">
                        </div>
                        <div class="form-group">
                            <input type="text" name="subject" class="form-control" id="subject" placeholder="Asunto" required="">
                        </div>
                        <div class="form-group">
                            <textarea name="message" class="form-control" rows="3" id="message" placeholder="Mensaje" required=""></textarea>
                        </div>
                        <button onclick="guardar()" class="btn btn-primary">Enviar mensaje</button>
                    </div>
                </div>
            </div>
        </div>
        @else
        <p style="color: red" class="contactenos-font"><i class="fa fa-warning"></i> No ha creado formulario de contacto.</p>
        @endif
    </div>
</div>

<script src="{{asset('js/sweetAlert2.min.js')}}"></script>
<script type="text/javascript">
    function guardar() {
        var nam = $("#names").val();
        var asu = $("#subject").val();
        var corr = $("#email").val();
        var msj = $("#message").val();
        if (nam.length <= 0 || asu.length <= 0 || corr.length <= 0 || msj.length <= 0) {
            alert("complete");
            return;
        } else {
            $.ajax({
                type: 'GET',
                url: "{{url('')}}/contactenos/configuracion/" + nam + "/" + corr + "/" + asu + "/" + msj + "/guardar"
            }).done(function(msg) {
                if (msg == "SI") {
                    Swal.fire(
                        'Atención!',
                        'Mensaje Enviado.',
                        'success'
                    );
                } else {
                    Swal.fire(
                        'Error!',
                        data.message,
                        'danger'
                    );
                }
                $("#names").val("");
                $("#subject").val("");
                $("#email").val("");
                $("#message").val("");
            });
        }

    }
</script>