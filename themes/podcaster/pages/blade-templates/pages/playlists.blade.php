<!-- Stored in resources/views/child.blade.php -->

@extends('common.base')

@section('content')
    @include('blocks.jumbotron-playlists')
    @include('sections.playlists-episodes', ["show_loop"=>true])
@endsection
