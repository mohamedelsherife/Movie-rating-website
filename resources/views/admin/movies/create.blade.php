@extends('layouts.app')

@section('title', 'Add Movie')

@section('content')
    <a href="{{ route('admin.movies.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to movies</a>

    <x-page-header eyebrow="New title" title="Add a movie" subtitle="Fill in the details below to add a movie to the catalogue." />

    <form action="{{ route('admin.movies.store') }}" method="POST" enctype="multipart/form-data" class="form-card" data-loading novalidate>
        @csrf

        @include('admin.movies._form')

        <div class="form-footer">
            <x-button :href="route('admin.movies.index')" variant="ghost">Cancel</x-button>
            <x-button icon="floppy-disk">Save movie</x-button>
        </div>
    </form>
@endsection
