@extends('layouts.main')

@section('header-inner')
    {{-- No header --}}
@endsection

@section('content')
<main id="frontpage">
    <div class="grid-container">
        @include('content.index.modules.warning')

        <section id="site-info">
            <div class="grid-20 tablet-grid-100 mobile-grid-100 {{ $rtl ? 'push-80' : ''}}">
                @include('content.index.modules.logo')
            </div>

            <div class="grid-40 tablet-grid-50 mobile-grid-100 {{ $rtl ? 'push-20 tablet-push-50' : ''}}">
                @include('content.index.modules.description')
            </div>

            <div class="grid-40 tablet-grid-50 mobile-grid-100 {{ $rtl ? 'pull-60 tablet-pull-50' : ''}}">
                @include('content.index.modules.statistics')
            </div>
        </section>
    </div>

    @include('widgets.announcement')

    @include('content.index.activity')
</main>
@endsection
