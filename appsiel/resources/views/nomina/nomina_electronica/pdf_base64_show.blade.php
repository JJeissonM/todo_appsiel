@extends('layouts.principal')

@section('content')
    <embed src="data:application/pdf;base64,{{$documento_electronico}}" type="application/pdf" width="100%" height="100%"/>
@endsection