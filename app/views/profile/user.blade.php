@extends('layout.main')

@section('content')
	<p>{{ $user->username }}</p>
	<p>{{ $user->email }}</p>
@stop