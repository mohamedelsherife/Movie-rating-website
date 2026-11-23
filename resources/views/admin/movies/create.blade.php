@extends('layouts.app')

@section('title', 'Add Movie')

@section('content')
    <a href="{{ route('admin.movies.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to movies</a>

    <header class="page-head">
        <div>
            <p class="eyebrow">New title</p>
            <h1 class="page-title">Add a movie</h1>
            <p class="page-sub">Fill in the details below to add a movie to the catalogue.</p>
        </div>
    </header>

    <form action="{{ route('admin.movies.store') }}" method="POST" enctype="multipart/form-data" class="form-card">
        @csrf

        @include('admin.movies._form')

        <div class="form-footer">
            <a href="{{ route('admin.movies.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save movie</button>
        </div>
    </form>
@endsection
