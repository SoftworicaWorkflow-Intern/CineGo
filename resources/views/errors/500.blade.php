@extends('layouts.app')

@section('title', ui('error_500_title', 'Server error'))

@section('content')
    <div class="container-page">
        @include('errors.partials.status', ['code' => 500])
    </div>
@endsection
