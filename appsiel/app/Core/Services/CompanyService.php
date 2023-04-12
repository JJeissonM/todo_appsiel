<?php

namespace App\Core\Services;

use App\Core\Empresa;

use Illuminate\Support\Facades\Auth;

class CompanyService
{
	public $company;

	function __construct( )
	{
        $user = Auth::user();
        if ($user != null) {
            $this->company = $user->empresa;
        }
		
        $this->company = Empresa::find( 1 );
	}
}