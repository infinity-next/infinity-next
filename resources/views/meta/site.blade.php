{{-- https://ogp.me/ --}}
@section('opengraph')
<meta property="og:title" content="@yield('title', config('app.name'))" />
<meta property="og:description" content="@yield('description', "")" />
<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:image" content="{{ asset('static/img/logo.png') }}" />
@stop
