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

@php
    $hay_alerta_global = (is_array($registro_alerta_mosoridad) || $registro_alerta_mosoridad instanceof Countable) && count($registro_alerta_mosoridad) > 0;
    $mensaje_alerta_global = $hay_alerta_global ? (string)($registro_alerta_mosoridad[0]->message ?? '') : '';
    $mensaje_alerta_global_plano = html_entity_decode(strip_tags($mensaje_alerta_global), ENT_QUOTES, 'UTF-8');
    $mensaje_alerta_global_plano = preg_replace('/\x{00A0}|\s+/u', '', $mensaje_alerta_global_plano);
    $tipo_alerta_global = $hay_alerta_global ? ($registro_alerta_mosoridad[0]->type ?? 'info') : 'info';
@endphp

@if( $hay_alerta_global && $mensaje_alerta_global_plano !== '' )
    <div class="alert alert-{{ $tipo_alerta_global }} alert-dismissible" role="alert">
        {!! $mensaje_alerta_global !!}
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
