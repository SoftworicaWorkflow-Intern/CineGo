@extends('layouts.app')

@section('title', ui('error_404_title', 'Page not found'))

@section('content')
    <div class="container-page">
        @include('errors.partials.status', ['code' => 404])
    </div>
@endsection
