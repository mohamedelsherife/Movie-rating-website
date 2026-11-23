@extends('layouts.app')

@section('title', 'Edit ' . $movie->title)

@section('content')
    <a href="{{ route('admin.movies.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to movies</a>

    <x-page-header eyebrow="Editing" :title="$movie->title" subtitle="Update the details, genres or poster for this title." />

    <form action="{{ route('admin.movies.update', $movie->id) }}" method="POST" enctype="multipart/form-data" class="form-card" data-loading novalidate>
        @csrf
        @method('PUT')

        @include('admin.movies._form', ['movie' => $movie])

        <div class="form-footer">
            <x-button :href="route('admin.movies.index')" variant="ghost">Cancel</x-button>
            <x-button icon="floppy-disk">Update movie</x-button>
        </div>
    </form>
@endsection
