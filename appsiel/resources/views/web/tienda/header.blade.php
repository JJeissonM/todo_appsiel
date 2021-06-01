<?php 
    $empresa = App\Core\Empresa::find(1);
    $configuracion = App\web\Configuraciones::all()->first();
?>

<header id="navegacion-tienda">
    <div class="top-link" style="background: var(--color-primario,#42A3DC); font-size:20px">
        <div class="container" style="padding: 0 ">
            <div class="top-link-inner">
                <div class="header-tienda">
                        <div class="toplink-static d-flex justify-content-center" style="width: 100px; height: 60px;">
                            <div style="position: absolute; z-index: 10;" >
                                <a href="{{ url('') }}">
                                    <img src="{{asset( config('configuracion.url_instancia_cliente').'storage/app/logos_empresas/'.$empresa->imagen)}}" style="z-index: 11000; height: 60px; width: auto"> 
                                </a>                                  
                            </div>                                                     
                        </div>
                        <span class="welcome-msg" style="color: white; ">
                                Venta Telefónica: <a style="text-transform: none" href="https://api.whatsapp.com/send?phone=+57{{ $empresa->telefono1 }}" target="_blank">&nbsp;<i style="font-size: 16px; color: green; background-color: white; border-radius: 100%; padding: 4px; width: 24px;" class="fa fa-whatsapp text-center" aria-hidden="true"></i>&nbsp; Atención al cliente.</a>
                        </span>

                        <ul class="links">
                            
                            <li>
                                <i class="fa fa-shopping-cart" aria-hidden="true"></i>&nbsp;
                                <a href="{{route("tienda.comprar")}}" title="My Cart" class="top-link-cart">Mi Carrito</a>
                            </li>
                            @if(Auth::guest())
                                <li class=" last">
                                    <i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp; 
                                    <a href="{{route('tienda.login')}}" title="Iniciar sesión">Iniciar Sesión</a>
                                </li>
                                <li class=" last">
                                    <i class="fa fa-user-plus" aria-hidden="true"></i>&nbsp;
                                    <a
                                            href="{{route('tienda.nuevacuenta')}}"
                                            title="Registrarse"
                                            onclick="registrarse( event )">Registrarse</a>
                                </li>
                            <!--
                                <li class=" last">
                                    <button onclick="document.getElementById('id01').style.display='block'" title="Registrarse" class="_no_abrir_modal" data-elemento_id="218" style="background: transparent; border: 0px;">Registrarse 2</button>

                                </li>-->
                            @else
                                <li class="first" style="order: 1">
                                    <i class="fa fa-user-circle" aria-hidden="true"></i>&nbsp;
                                    <a href="{{route('tienda.micuenta')}}" title="Mi Cuenta">Mi Cuenta</a>
                                </li>
                                <li class=" last">
                                    <i class="fa fa-sign-out" aria-hidden="true"></i>&nbsp;
                                    <a href="{{url('/logout')}}" title="Cerra sesión">Cerrar Sesión</a>
                                </li>
                            @endif
                        </ul>
                </div>
            </div>
        </div>
    </div>
</header>

<div id="id01" class="modal">
  <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
  <form class="modal-content" action="/action_page.php">
    <div class="container">
      <h1>Sign Up</h1>
      <p>Please fill in this form to create an account.</p>
      <hr>
      <label for="email"><b>Email</b></label>
      <input type="text" placeholder="Enter Email" name="email" required>

      <label for="psw"><b>Password</b></label>
      <input type="password" placeholder="Enter Password" name="psw" required>

      <label for="psw-repeat"><b>Repeat Password</b></label>
      <input type="password" placeholder="Repeat Password" name="psw-repeat" required>
      
      <label>
        <input type="checkbox" checked="checked" name="remember" style="margin-bottom:15px"> Remember me
      </label>

      <p>By creating an account you agree to our <a href="#" style="color:dodgerblue">Terms & Privacy</a>.</p>

      <div class="clearfix">
        <button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
        <button type="submit" class="signupbtn">Sign Up</button>
      </div>
    </div>
  </form>
</div>

<script>
// Get the modal
var modal = document.getElementById('id01');

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
