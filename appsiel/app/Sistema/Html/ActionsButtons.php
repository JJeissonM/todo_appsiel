<?php

namespace App\Sistema\Html;

use Illuminate\Support\Facades\Input;

class ActionsButtons
{    
    public function render( $actions )
    {
        $buttons = [];

        foreach ($actions as $action) {
            $buttons[] = new Boton($action);
        }       
        
        return view('system.actions_buttons', compact('buttons'));
    }

}