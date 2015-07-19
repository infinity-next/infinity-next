@extends('layouts.main')

@section('title', e("{$board->title} - /{$board->board_uri}/"))
@section('description', e($board->description))

@if ($board->getStylesheet())
	@section('css-addendum', "<link href=\"/{$board->board_uri}/style.css\" rel=\"stylesheet\" />")
@endif

@if (count($board->getBanners()))
	@section('header-logo', $board->getBannerRandom()->asHTML())
@endif