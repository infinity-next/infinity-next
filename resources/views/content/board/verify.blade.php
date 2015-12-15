{{--
	This simple view is meant to be used as a catch-all password form for
	board actions that require verification. In cases we want only the form,
	we would not call this.
--}}

@extends('layouts.main.simplebox')
@section('title', trans('board.verify.title'))

@section('body')
	@include('content.board.verify.password')
	
	@if ($mod)
		@include('content.board.verify.mod')
	@endif
@stop
