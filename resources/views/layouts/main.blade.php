@extends('layouts.static')

@section('js')
    <script type="text/javascript" id="js-app-data">
        document.getElementsByTagName('html')[0].class = "js";

        window.app = {
            'lang'     : {!! json_encode( Lang::get('widget') ) !!},

            'merchant' : "{{ env('CASHIER_SERVICE') }}",

        @if (env('APP_DEBUG'))
            'debug'      : true,

            @if (env('STRIPE_LIVE_PUBLIC') && env('CASHIER_SERVICE') == "stripe")
            'stripe_key' : "{!! env('STRIPE_TEST_PUBLIC', '') !!}",
            @endif

            @if (env('CASHIER_SERVICE') == "braintree" && isset($BraintreeClientKey))
            'braintree_key' : "{!! $BraintreeClientKey !!}",
            @endif

        @else
            'debug'      : false,

            @if (env('STRIPE_LIVE_PUBLIC') && env('CASHIER_SERVICE') == "stripe")
            'stripe_key' : "{!! env('STRIPE_LIVE_PUBLIC', '') !!}",
            @endif

            @if (env('CASHIER_SERVICE') == "braintree" && isset($BraintreeClientKey))
            'braintree_key' : "{!! $BraintreeClientKey !!}",
            @endif
        @endif

            'favicon'    : {
                'normal' : "{{ asset('static/img/assets/Favicon_Vivian.ico') }}",
                'alert'  : "{{ asset('static/img/assets/Favicon_Vivian_new.ico') }}"
            },

            'title'      : "@yield('title', 'Infinity Next')",

            'url'        : "{!! route('site.home') !!}/",

            @yield('app-js')

            'settings'   : {!! $app['settings']->getJson() !!},

            'version'    : 0
        };
    </script>

    <script type="text/javascript" id="js-app-stylist">
        {{--
            IMPORTANT
            This relies on information setup by js/app/widgets/stylist.widget.js
            However, this particular script exists outside of the scope of the
            widget framework so that the styling is injected before any document
            rendering happens.
        --}}
        var theme = localStorage.getItem('ib.setting.stylist.theme') || false;
        var css   = localStorage.getItem('ib.setting.stylist.css') || false;

        if (theme)
        {
            document.getElementById('theme-stylesheet').href = window.app.url + "/static/css/skins/" + theme;
        }

        if (css && css.length > 0)
        {
            document.getElementById('user-css').innerHTML = css;
        }
    </script>

    @yield('required-js')

    <script data-no-instant src="{{ elixir('static/js/vendor.js') }}"></script>
    <script data-no-instant src="{{ elixir('static/js/app.js') }}"></script>
    @parent
@stop

@section('footer')
<footer>
    @yield('footer-inner')

    @section('nav-footer')
        @include('nav.boardlist')
    @show

    @if (site_setting('canary'))
    <figure id="canary-bird">
        <img
            src="{{ asset('static/img/assets/canary.svg') }}"
            id="canary-img"
            alt="{{ trans('config.canary', ['site_name' => $app['settings']('siteName')]) }}"
            title="{{ trans('config.canary', ['site_name' => $app['settings']('siteName')]) }}"
        />
    </figure>
    @endif

    <section id="footnotes">
        <!-- Infinity Next is licensed under AGPL 3.0 and any modifications to this software must link to its source code which can be downloaded in a traditional format, such as a repository. -->
        <div class="copyright"><a class="agpl-compliance" href="https://github.com/infinity-next/infinity-next">Infinity Next</a> &copy; <a class="agpl-compliance" href="https://16chan.nl">Infinity Next Development Group</a> 2015-2016</div>
    </section>

    <div id="bottom"></div>
</footer>
@stop
