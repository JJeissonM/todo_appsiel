<?php 

    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\DB;

    $registro_alerta_mosoridad = [];

    if ( Schema::hasTable( 'sys_erp_alert' ) )
    {
        $registro_alerta_mosoridad = DB::table( 'sys_erp_alert' )->get();
    }

    //dd($registro_alerta_mosoridad);
?>

@if( !empty($registro_alerta_mosoridad) )
    <div class="alert alert-{{ $registro_alerta_mosoridad[0]->type }} alert-dismissible" role="alert">
        {!! $registro_alerta_mosoridad[0]->message !!}
    </div>
@else
    @if(config('configuracion.usuario_en_mora') == 'true')	
        <?php
            $user = Auth::user();
        ?>
        @if ($user->hasRole('UsuarioMora'))
            <div style="position: absolute; display: flex; justify-content: center; align-items: center; width: 100%; height: 100%; z-index: 100; background-color: rgba(0, 0, 0, 0.37)">
                <div class="panel panel-danger" style="font-size: 22px">
                    <div class="panel-heading">
                    <h3 class="panel-title"  style="font-size: 30px">Usuario en Mora</h3>
                    </div>
                    <div class="panel-body">
                        Estimado/a Cliente, le recordamos que tiene un saldo vencido. Lo invitamos a poner su cuenta al día. Para más información escriba al {{ config('configuracion.numero_contacto_paula') }} <br> Su acceso ha sido bloqueado y este se normalizará al momento de pagar el total de la deuda.
                    </div>
                </div>
            </div>	    
        @endif

        <div class="alert alert-danger alert-dismissible mora" role="alert">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            Estimado/a Cliente, le recordamos que tiene un saldo vencido. Lo invitamos a poner su cuenta al día. Para más información escríbanos al {{ config('configuracion.numero_contacto_paula') }}
            <style>
                .navbar{
                    margin-bottom: 0;
                }
                .mora{
                    margin: 2px 8px;
                }
            </style>
        </div>	

    @endif
@endif