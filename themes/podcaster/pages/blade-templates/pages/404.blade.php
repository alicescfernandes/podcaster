<!-- Stored in resources/views/child.blade.php -->

@extends('common.base')

@section('content')
    <h1 style="font-size:80px;text-align:center">Houston, we have a problem</h1>
    <h2 style="font-size:40px;font-weight:100;text-align:center">Ooops, it seems like you found an error or non-existing page</h2>
    <p style="text-align:center"><a href="//{{HTTP_HOST}}">Go back to homepage</a></p>
@endsection
