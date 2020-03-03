<div class="container-wrapper">
    <div class="container">
        @if($contactenos != null)
            <div class="row" style="border-radius: 10px;background-color: white; width: 250px; height: 500px;">
                <div class=".col-sm-4 col-sm-offset-8">
                    <div class="contact-form " style="padding: 20px; " >
                        <h3 style="color: black">Contact Info</h3>
                        <address style="color: black">
                            <strong style="color: black">
                            {{$contactenos->empresa}}</strong><br>
                            {{$contactenos->correo}}<br>
                            {{$contactenos->direccion}}<br>
                            {{$contactenos->ciudad}}<br>
                            <abbr title="Phone">Tel:</abbr> {{$contactenos->telefono}}
                        </address>
                        <form id="main-contact-form" name="contact-form" method="post" action="{{route('contactenos.guardar')}}">
                            <input type="hidden" name="contactenos_id" value="{{$contactenos->id}}">
                            <input type="hidden" name="_toker" value="{{csrf_token()}}">
                            <input type="hidden" name="_method" value="post">
                            <div class="form-group">
                                <input type="text" name="name" class="form-control" placeholder="Name" required="">
                            </div>
                            <div class="form-group">
                                <input type="email" name="email" class="form-control" placeholder="Email" required="">
                            </div>
                            <div class="form-group">
                                <input type="text" name="subject" class="form-control" placeholder="Subject"
                                       required="">
                            </div>
                            <div class="form-group">
                        <textarea name="message" class="form-control" rows="3" placeholder="Message"
                                  required=""></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Enviar mensaje</button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <p style="color: red"><i class="fa fa-warning"></i> No ha creado formulario de contacto.</p>
        @endif
    </div>
</div>