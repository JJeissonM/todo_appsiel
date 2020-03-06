<div class="container-wrapper">
    <div class="container">
        @if($contactenos != null)
            <div class="row" style="border-radius: 10px;background-color: white;">
                <div class=".col-sm-4 col-sm-offset-8">
                    <div class="" style="padding: 5px; " >
                        <!-- <h3 style="color: black">Información de Contacto</h3> -->
                        <address style="color: black">
                                @if( $contactenos->empresa != '' )
                                    <strong style="color: black">
                                        {{str_limit($contactenos->empresa,20)}}
                                    </strong><br>
                                @endif
                                @if( $contactenos->correo != '' )
                                    <a href="mailto:{{ $contactenos->correo }}"> <span title="{{ $contactenos->correo }}"> {{str_limit($contactenos->correo,20)}}<br> </span> </a>
                                @endif
                                @if( $contactenos->direccion != '' )
                                    {{str_limit($contactenos->direccion,20)}}<br>
                                @endif
                                @if( $contactenos->ciudad != '' )
                                    {{str_limit($contactenos->ciudad,20)}}<br>
                                @endif
                                @if( $contactenos->telefono != '' )
                                    <i class="fa fa-whatsapp"></i> {{$contactenos->telefono}}
                                @endif
                        </address>
                        <div class="col-md-12">
                            <form  name="contact-form" method="post" action="{{route('contactenos.guardar')}}">
                                <input type="hidden" name="contactenos_id" value="{{$contactenos->id}}">
                                <input type="hidden" name="_token" value="{{csrf_token()}}">
                                <input type="hidden" name="_method" value="post">
                                <div class="form-group">
                                    <input type="text" name="name" class="form-control" placeholder="Nombre completo" required="">
                                </div>
                                <div class="form-group">
                                    <input type="email" name="email" class="form-control" placeholder="Email" required="">
                                </div>
                                <div class="form-group">
                                    <input type="text" name="subject" class="form-control" placeholder="Asunto"
                                           required="">
                                </div>
                                <div class="form-group">
                        <textarea name="message" class="form-control" rows="3" placeholder="Mensaje"
                                  required=""></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Enviar mensaje</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <p style="color: red"><i class="fa fa-warning"></i> No ha creado formulario de contacto.</p>
        @endif
    </div>
</div>