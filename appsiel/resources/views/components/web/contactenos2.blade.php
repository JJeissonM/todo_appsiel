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
<div class="container-wrapper contactenos-font  p-md-4 p-sm-1">
    <div class="container">
        @if($contactenos != null)
        <div class="row contactenos-font">
            <div class="col-md-12">
                <div class="section-header">
                    <h2 class="section-title text-center wow fadeInDown contactenos-font">CONTÁCTENOS</h2>
                    <p class="text-center wow fadeInDown contactenos-font" style="font-weight: bold; font-size: 20px; color: #000;">{{ config('pagina_web.mensaje_de_contectenos') }}</p>
                </div>
            </div>
            <div class="col-md-3"></div>
            <div class="col-md-6" style="padding: 20px; margin: 10px !important; font-size: 14px; border-radius: 20px !important; -webkit-box-shadow: 1px 1px 100px var(--color-terciario); -moz-box-shadow: 1px 1px 100px var(--color-terciario); box-shadow: 1px 1px 100px var(--color-terciario);">
                <div class="" style="padding: 5px;">
                    <address style="color: black; text-align: center; font-size: 16px;">
                        @if( $contactenos->empresa != '' )
                        <strong style="color: black; font-size: 28px;">
                            <span class="contactenos-font" title="{{ $contactenos->empresa }}"> {{$contactenos->empresa}} </span>
                        </strong>
                        <br>
                        @endif
                        @if( $contactenos->correo != '' )
                        <a href="mailto:{{ $contactenos->correo }}">
                            <span class="contactenos-font" style="font-size: 22px; color: var(--color-primario)" title="{{ $contactenos->correo }}"> {{$contactenos->correo}}<br> </span>
                        </a>
                        @endif
                        @if( $contactenos->direccion != '' )
                        <span class="contactenos-font" title="{{ $contactenos->direccion }}"> {{$contactenos->direccion}} </span>
                        <br>
                        @endif
                        @if( $contactenos->ciudad != '' )
                        <span class="contactenos-font" title="{{ $contactenos->ciudad }}"> {{$contactenos->ciudad}} </span>
                        <br>
                        @endif
                        @if( $contactenos->telefono != '' )
                        <i class="fa fa-whatsapp"></i> {{$contactenos->telefono}}
                        @endif
                    </address>
                    <div class="col-md-12" class="contactenos-font">
                        <input type="hidden" name="contactenos_id" id="contactenos_id" value="{{$contactenos->id}}">
                        <div class="form-group">
                            <input type="text" name="names" class="form-control" id="names" placeholder="Nombre completo" required="">
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-control" id="email" placeholder="Email" required="">
                        </div>
                        <div class="form-group">
                            <input type="number" name="numtel" class="form-control" id="numtel" placeholder="Numero de Telefono" required="">
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
            <div class="col-md-3"></div>
        </div>
        @else
        <p style="color: red" class="contactenos-font"><i class="fa fa-warning"></i> No ha creado formulario de contacto.</p>
        @endif
    </div>
</div>

<script src="{{asset('js/sweetAlert2.min.js')}}"></script>
<script type="text/javascript">
'use strict'
    function guardar() {
        var nam = $("#names").val();
        var asu = $("#subject").val();
        var ntel = $("#numtel").val();
        var corr = "<a href='mailto:"+$("#email").val()+"'>"+$("#email").val()+"<&#47;a> | "+ntel;
        var msj = $("#message").val();
        if (nam.length <= 0 || asu.length <= 0 || corr.length <= 0 || msj.length <= 0 || ntel.length <= 0) {
            alert("Complete los campos.");
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