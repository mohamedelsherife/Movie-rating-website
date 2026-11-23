@extends('layouts.app')

@section('title', 'Edit ' . $movie->title)

@section('content')
    <a href="{{ route('admin.movies.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to movies</a>

    <header class="page-head">
        <div>
            <p class="eyebrow">Editing</p>
            <h1 class="page-title">{{ $movie->title }}</h1>
            <p class="page-sub">Update the details, genres or poster for this title.</p>
        </div>
    </header>

    <form action="{{ route('admin.movies.update', $movie->id) }}" method="POST" enctype="multipart/form-data" class="form-card">
        @csrf
        @method('PUT')

        @include('admin.movies._form', ['movie' => $movie])

        <div class="form-footer">
            <a href="{{ route('admin.movies.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Update movie</button>
        </div>
    </form>
@endsection
