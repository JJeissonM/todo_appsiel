<link href="{{ asset('assets/css/jquery.magicsearch.min.css') }}" rel="stylesheet">

<div class="form-group">
	<label class="control-label col-sm-3" for="{{$name}}"><?php echo $lbl?>:</label>
	<div class="col-sm-9"></div>
        <input name="{{$name}}" class="magicsearch" id="basic" placeholder="Seleccionar...">
	</div>
</div>

<script src="{{asset('assets/js/jquery.magicsearch.min.js')}}"></script>

@section('multiselect')
<script>
    $(document).ready(function() {
            var dataSource = {{ $opciones }};
            $('#basic').magicsearch({
                dataSource: dataSource,
                fields: ['firstName', 'lastName'],
                id: 'id',
                format: '%firstName% · %lastName%',
                multiple: true,
                focusShow: true,
                multiField: 'firstName',
                multiStyle: {
                    space: 5,
                    width: 80
                }
            });
        });
</script>
@endsection
