@set('catalog', isset($catalog) ? !!$catalog : false)
@extends($catalog ? 'content.multicatalog' : 'content.multiboard')

@section('title', trans("board.overboard"))
