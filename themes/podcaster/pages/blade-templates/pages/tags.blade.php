<!-- Stored in resources/views/child.blade.php -->

@extends('common.base')

@section('content')

<div class="podcaster-categories-tags podcaster-section">
    <h1>Episodes with tag {{$tag}}</h1>
</div>

@include('sections.playlists-episodes', ["show_loop"=>false])
@endsection
