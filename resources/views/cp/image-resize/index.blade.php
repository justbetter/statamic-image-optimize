@extends('statamic::layout')

@section('content')
    <justbetter-statamic-optimize-image-form title="Resize images" total-assets="{{ $total_assets }}" unoptimized-assets="{{ $unoptimized_assets }}"/>
@stop
