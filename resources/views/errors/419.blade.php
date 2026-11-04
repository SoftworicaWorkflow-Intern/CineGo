@extends('layouts.app')

@section('title', ui('error_419_title', 'Session expired'))

@section('content')
    <div class="container-page">
        @include('errors.partials.status', ['code' => 419])
    </div>
@endsection
