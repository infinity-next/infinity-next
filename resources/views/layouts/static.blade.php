<!DOCTYPE html>
@set('direction', isset($direction) ? $direction : "ltr")
@set('ltr', isset($ltr) ? $ltr : true)
@set('rtl', isset($rtl) ? $rtl : false)
<html class="no-js" data-widget="instantclick" dir="{{ $direction }}">
<head>
    <title data-original="@yield('title') - {{ site_setting('siteName', env('SITE_NAME')) }}">@ifhas('title')@yield('title') - @endif{{ site_setting('siteName') }}</title>
    <link rel="shortcut icon" id="favicon" href="{{ asset('static/img/assets/Favicon_Vivian.ico') }}"
        data-normal="{{ asset('static/img/assets/Favicon_Vivian.ico') }}"
        data-alert="{{ asset('static/img/assets/Favicon_Vivian_new.ico') }}" />

    {{-- Prevents any foreign URLs from loading. They must come in LEGALLY. --}}
    <meta http-equiv="Content-Security-Policy"
        content="default-src 'self' 'unsafe-inline' {!!config('app.url_media','')!!} {!!config('app.url_panel','')!!};
            img-src 'self' 'unsafe-inline' {!!config('app.url_media','')!!} {!!config('app.url_panel','')!!} data: blob: filesystem:;
            script-src 'self' 'unsafe-inline' 'unsafe-eval' {!!config('app.url_media','')!!} {!!config('app.url_panel','')!!};"
    />

    @section('css')
        <link rel="stylesheet" href="{{ mix('static/css/vendor.css') }}" data-no-instant id="style-vendor" />
        <link rel="stylesheet" href="{{ mix('static/css/global.css') }}" data-no-instant id="style-global" />

        @section('area-css')
            <link rel="stylesheet" href="{{ mix('static/css/public.css') }}" data-instant-track id="style-system"/>
        @show

        @section('page-css')
            <link id="page-stylesheet" rel="stylesheet" href="{{ asset('static/css/empty.css') }}" data-empty="{{ asset('static/css/empty.css') }}" data-instant-track />
        @show

        <link id="theme-stylesheet" rel="stylesheet" data-instant-track />
        <style id="user-css" type="text/css"></style>
    @show

    @yield('js')

    @section('meta')
        <meta name="viewport" content="width=device-width" />
        <meta name="csrf-token" content="{{ csrf_token() }}" data-instant-track />
    @show

    @section('widgets')
        <meta id="widget-content" data-widget="content" />
    @show

    @yield('head')
</head>

{{--
    BODY CLASSES.
        * "infinity-next", always keep.
        * "responsive", always keep (for now). Will eventually want to unset if user requests desktop view.
        * $direction, either "ltr" or "rtl".
        * "nsfw-allowed", keep. JS will turn this off if enabled.
        * The yield renders additional body classes a specific view needs.
--}}
<body class="infinity-next responsive {{ $direction }} nsfw-allowed @yield('body-class')" id="top">
    <div id="page-container">
        @section('header')
        <header class="board-header header-height-1">
            @section('nav-header')
                @if (env('APP_ESI', false))
                    <esi:include src="{{ esi_url('.internal/site/global-nav') }}" />
                @else
                    @include('nav.gnav')
                @endif
            @show

            @section('header-inner')
                <figure class="page-head">
                    @can('be-accountable')
                    <img id="logo" src="@yield('header-logo', asset('static/img/logo.png'))" alt="{{ site_setting('siteName') }}" />
                    @else
                    <img id="logo" src="@yield('header-logo', asset('static/img/logo_tor.png'))" alt="{{ site_setting('siteName') }}" />
                    @endcan
                    <figcaption class="page-details">
                        @if (!isset($hideTitles))
                            @if (array_key_exists('page-title', View::getSections()))
                            <h1 class="page-title">@yield('page-title')</h1>
                            @else
                            <h1 class="page-title">@yield('title')</h1>
                            @endif
                        <h2 class="page-desc">@yield('description')</h2>
                        @endif

                        @yield('header-details')
                    </figcaption>
                </figure>

                @include('widgets.announcement')
            @show
        </header>
        @show

        @yield('content')

        <div id="attachment-preview"><img id="attachment-preview-img" src="" /></div>
    </div>

    @yield('footer')
</body>
</html>
