@extends('layouts.principal')

@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    <hr>

    <?php
        //print_r($users->toArray());
        $i = 0;
        foreach ($users as $fila) {
            if ( $fila->email != 'administrator@appsiel.com.co') {
                $correos[$i] = $fila->email;
                $i++;
            }
                
        }
    ?>
    @include('layouts.mensajes')

    <div class="row">
        <div class="col-lg-10 col-lg-offset-1 marco_formulario">
            <h4 style="color: gray;">Crear nuevo</h4>
            <hr>

            <form action="{{ route('messages.store') }}" method="post">
                {{ csrf_field() }}

                <div class="row">
                    <div class="col-md-6">
                        <div class="row" style="padding:5px;">
                            {{ Form::bsText( 'subject', old('subject'), 'Asunto', ['required'=>'required'] ) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row" style="padding:5px;">
                            {{ Form::bsTextArea( 'message', old('message'), 'Mensaje', ['required'=>'required'] ) }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="row" style="padding:5px;">
                            <div class="ui-widget">

                                {{ Form::bsText( 'recipients', old('recipients'), 'Remitente(s)', ['required'=>'required'] ) }}

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="row" style="padding:5px;">
                            <button type="submit" class="btn btn-primary form-control">Enviar</button>
                            <br/><br/>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
@stop

@section('scripts')
    <script type="text/javascript">
        $( function() {
            var availablerecipients = <?php echo json_encode($correos); ?>;

            function split( val ) {
              return val.split( /,\s*/ );
            }

            function extractLast( term ) {
              return split( term ).pop();
            }
         
            $( "#recipients" )
              // don't navigate away from the field on tab when selecting an item
              .on( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                    $( this ).autocomplete( "instance" ).menu.active ) {
                  event.preventDefault();
                }
              })
              .autocomplete({
                minLength: 0,
                source: function( request, response ) {
                  // delegate back to autocomplete, but extract the last term
                  response( $.ui.autocomplete.filter(
                    availablerecipients, extractLast( request.term ) ) );
                },
                focus: function() {
                  // prevent value inserted on focus
                  return false;
                },
                select: function( event, ui ) {
                  var terms = split( this.value );
                  // remove the current input
                  terms.pop();
                  // add the selected item
                  terms.push( ui.item.value );
                  // add placeholder to get the comma-and-space at the end
                  terms.push( "" );
                  this.value = terms.join( ", " );
                  return false;
                }
              });
          } );
    </script>
@endsection