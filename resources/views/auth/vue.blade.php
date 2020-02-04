@extends(\Larapress\Dashboard\Rendering\BladeCRUDViewProvider::getThemeViewName('layouts.master'))

@section('page-title', trans('dashboard.pages.home.title'))
@section('lang-direction', '')
@section('body-class', '')

@section('meta')
    @if (!is_null(auth()->user()))
        <meta name="jwt-token" content="{{ auth()->guard('api')->tokenById(auth()->user()->id) }}">
    @endif
@endsection

@section('pre-styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('/storage/vendor/larapress-auth/css/app.css') }}">
@endsection

@section('body')
    <div id="App">
    </div>
@endsection

@section('pre-scripts')
    <script>
        window.DashboardConfig = {!! json_encode($config) !!};
        console.log(window.DashboardConfig);
    </script>
    <script src="{{ asset('/storage/vendor/larapress-auth/js/manifest.js') }}"></script>
    <script src="{{ asset('/storage/vendor/larapress-auth/js/vendor.bundle.js') }}"></script>
    <script src="{{ asset('/storage/vendor/larapress-auth/js/app.bundle.js') }}"></script>
@endsection

@section('scripts')
@endsection