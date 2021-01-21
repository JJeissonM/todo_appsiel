@if(Session::has('flash_message'))
	<div class="container" style="clear: both; width: auto;">
		<div class="alert alert-success alert-dismissible">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<em> {!! session('flash_message') !!}</em>
		</div>
	</div>
@endif
 
@if(Session::has('mensaje_error'))
	<div class="container" style="clear: both; width: auto;">      
		<div class="alert alert-danger alert-dismissible">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<em> {!! session('mensaje_error') !!}</em>
		</div>
	</div>
@endif 

<div class="row">
	<div class="col-md-8 col-md-offset-2">              
		  @include ('errors.list') {{--Including error file --}}
	</div>
</div>