@extends('layouts.app')

@section('title', ui('error_403_title', 'Access denied'))

@section('content')
    <div class="container-page">
        @include('errors.partials.status', ['code' => 403])
    </div>
@endsection
