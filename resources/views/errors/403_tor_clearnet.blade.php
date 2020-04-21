@section('header-details')
<a href="http://{{env('APP_URL_HS')}}">@lang('error.403_tor_clearnet.desc')</a>
@endsection

@include('layouts.error', [
    'status_code' => 403,
    'error_name'  => "403_tor_clearnet",
    'error_html'  => "",
])
