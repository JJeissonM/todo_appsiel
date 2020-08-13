<?php


namespace App\Http\Controllers\web\services;

use App\web\Icon;
use App\AcademicoDocente\GuiaAcademica;

use App\Matriculas\Curso;

use Form;
use Illuminate\Support\Facades\Input;

class GuiasAcademicasComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    function DrawComponent()
    {
        $cursos = Curso::opciones_campo_select();

        return Form::guias_academicas( $cursos );
    }

    // No se va a gestionar desde Páginas Web
    function viewComponent()
    {
        //
    }
}
