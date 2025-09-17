
class FormBuilder
{
    constructor()
    {
        this.index = 2;
        this.cantidad_campos = 0;
        this.action = 'create';
    }
    

    get_html_form( dataform )
    {
        // Create the form element
        var form = $('<form/>', {
            id: dataform.form_info.id,
            action: dataform.form_info.action,
            method: 'POST'
        });

        form.append('<input name="_token" type="hidden" value="fDrIarmy1MH7mweM0TWORplLwxskXIr5HX0SGAkQ">');

        var fields = dataform.fields;
        var row = '';
        var cell = '';

        fields.forEach((field) => {
                 
            if( this.index % 2 == 0 ) // Si index es par
            {
                row += '<div class="row">';
                cell += '<div class="col-md-6">';
            }else{
                cell += '<div class="col-md-6">';
            }

            /*
            if ( !isset(field.id) ) {
                field.id = 0;
            }
            */
            
            if ( this.action == 'show') {
                cell += '<div class="row" style="padding:5px;">' + this.mostrar_campo( field.id, field.value, 'show' ) + '</div>';
            }else{
                cell += '<div class="row" style="padding:5px;">' + this.render_input(field) + '</div>';
            }

            cell += '</div>';

            row += cell;
            cell = '';

            this.index++;
            if(this.index % 2 == 0)
            {
                row += '</div>';
                
                form.append( row );

                row = '';
                cell = '';
            }

            this.cantidad_campos++;

        });

        if( this.cantidad_campos % 2 != 0)
        {
            // Celda Adicional y cierra fila
            form.append( '<div class="col-md-6"></div> </div>' );
        }

        return form;
    }

    render_input( field )
    {        
        if (field.requerido) {
            field.descripcion = "<i class='fa fa-asterisk'></i>" + field.descripcion;
        }

        var input;

        switch (field.tipo) {
            
            case 'bsText':
                input = this.TextInput( field );
                break;
            
            case 'bsEmail':
                input = this.EmailInput( field );
                break;

            case 'bsTextArea':
                input = this.TextAreaInput( field );
                break;

            case 'hidden':
                input = this.HiddenInput( field );
                break;

            case 'select':
                input = this.SelectInput( field );
                break;
                
            case 'fecha':
                input = this.FechaInput( field );
                break;

            case 'personalizado':
                input = '<div id="' + field.name + '">' + field.value + '</div>';
                break;

            case 'constante':
                input = this.HiddenInput( field );
                break;

            default:
                input = '<div class="alert alert-danger"><strong>¡Error!</strong> Tipo de campo (elemento input) no existe.</div>';
                break;
        }

        return input;
    }

    TextInput( field )
    {
        return '<div class="form-group"> <label class="control-label col-sm-3 col-md-3" for="' + field.name + '">' + field.descripcion + ':</label> <div class="col-sm-9 col-md-9"><input type="text" id="' + field.name + '" name="' + field.name + '" placeholder="' + this.get_placeholder( field ) + '" value="' + this.get_value( field ) + '" class="form-control" autocomplete="off" ' + (( field.requerido == 1 ) ? ' required="required"' : '') + '> </div> </div>';
    }

    EmailInput( field )
    {
        return '<div class="form-group"> <label class="control-label col-sm-3" for="' + field.name + '">' + field.descripcion + ':</label> <div class="col-sm-9 col-md-9"> <input type="email" name="' + field.name + '" id="' + field.name + '" placeholder="' + this.get_placeholder( field ) + '" value="' + this.get_value( field ) + '" class="form-control"' + (( field.requerido == 1 ) ? ' required="required"' : '') + '> </div> </div>';
    }

    TextAreaInput( field )
    {
        return '<div class="form-group" style="padding-left: 10px;"> <label class="control-label" for="' + field.name + '" style="padding-left: 5px;"> ' + field.descripcion + ':</label> <div class="col-sm-12"> <textarea name="' + field.name + '" id="' + field.name + '" rows="4" cols="50"' + (( field.requerido == 1 ) ? ' required="required"' : '') + '>' + this.get_value( field ) + '</textarea> </div> </div>';
    }

    HiddenInput( field )
    {
        return '<input type="hidden" name="' + field.name + '" id="' + field.name + '" value="' + field.value + '">';
    }

    SelectInput( field )
    {
        var input = '<div class="form-group"> <label class="control-label col-sm-3" for="' + field.name + '">' + field.descripcion + ':</label> <div class="col-sm-9 col-md-9"> <select name="' + field.name + '" id="' + field.name + '" class="form-control"' + (( field.requerido == 1 ) ? ' required="required"' : '') + '>';

        var opciones = field.opciones;

		opciones.forEach((opcion) => { 
			input += '<option value="' + opcion.id + '"';
            
            if( field.value == opcion.id )
            {
                input += ' selected="selected"';
            }
			
            input += '>' + opcion.label + '</option>';
        });
	    
        input += '</select> </div> </div>';

        return input;
    }

    FechaInput()
    {
        return '<div class="form-group"> <label class="control-label col-sm-3" for="' + field.name + '">' + field.descripcion + ':</label> <div class="col-sm-9"><input type="date" id="' + field.name + '" name="' + field.name + '" value="' + field.value + '" class="form-control"' + (( field.requerido == 1 ) ? ' required="required"' : '') + '> </div> </div>';
    }

    get_value( field )
    {
        var value = '';
        if ( field.value != null) {
            value = field.value;
        }
        return value;
    }

    get_placeholder( field )
    {
        var placeholder = field.descripcion;
        if ( field.descripcion.includes("<i class='fa fa-asterisk'></i>")
        ) {
            placeholder = field.descripcion.substring(30);
        }
        return placeholder;
    }

}