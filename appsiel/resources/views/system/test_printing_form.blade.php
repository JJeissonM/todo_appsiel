<!DOCTYPE html>
<html lang="en">

<head>
  <title>Testing Printing Form</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>  

</head>

<body>

  <div class="container mt-3">
    <h2>Testing Printing Form</h2>
    <form action="{{ url('/api/v1/invoices') }}" type="POST" id="main_form">
      <div class="mb-3 mt-3">
        <label for="authorization_token">*Authotization Token:</label>
        <input type="authorization_token" class="form-control" id="authorization_token"
          placeholder="002979c5-7c23-43ab-aa98-3fa7dce6e4d0" name="authorization_token" required="required">
      </div>
      <div class="mb-3">
        <label for="json">*Json:</label>
        <textarea class="form-control" rows="5" id="json" name="json" required="required"></textarea>
      </div>
      <button id="btn_send" class="btn btn-primary">Enviar</button>
    </form>

    <br><br><br>
    <button onclick="ajax_print('{{url('/sys_test_print_example_rawbt')}}',this)" class="btn btn-primary"> <span class="bi bi-print"></span> Print Example Rawbt</button>


    {{ storage_path() }}
    <div class="mt-4 p-5 bg-success text-white rounded" id="content_for_response" style="display: none;">
       
    </div>

  </div>

</body>

<script>

  $(document).ready(function(){
      
      $("#btn_send").click(function(event){
        event.preventDefault();

        $('#content_for_response').fadeOut(1000);

        if ( !validate_required() ) {
          return false;
        }

        var form = $('#main_form');
        
        $.post(
          form.attr('action'),
          form.serialize(),
          function(data, status){

            $('#content_for_response').fadeIn(1000);
            $('#content_for_response').html(data);
            
          });
      });

      function validate_required()
      {
        if ( $('#authorization_token').val() == '' ) {
          Swal.fire({
            title: 'Error!',
            text: 'El campo Authotization Token es requerido.',
            icon: 'error',
            closeButton: 'Cerrar'
          })

          return false;
        }
        
        if ( $('#json').val() == '' ) {
          Swal.fire({
            title: 'Error!',
            text: 'El campo Json es requerido.',
            icon: 'error',
            closeButton: 'Cerrar'
          })

          return false;
        }

        return true;
      }

    });
</script>

</html>