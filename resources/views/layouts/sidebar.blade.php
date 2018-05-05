@extends('layouts.app')

@section('css')
    @loadLocalCSS(/css/sidebar.css)
@endsection

@section('content')
<div class="container">
    <div class="row">
        @yield('top')
    </div>
    <div class="row">
        <div class="col-sm-3">
            @yield('sidebar')
        </div>
        <div class="col-sm-9">
            @yield('mainpage')
        </div>
    </div>
</div>
@endsection
