@extends('layouts.app')

@section('title', 'Add TV Series')

@section('content')
    <a href="{{ route('admin.series.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to TV series</a>

    <x-page-header eyebrow="New title" title="Add a TV series" subtitle="Fill in the details below to add a series to the catalogue." />

    <form action="{{ route('admin.series.store') }}" method="POST" enctype="multipart/form-data" class="form-card" data-loading novalidate>
        @csrf

        @include('admin.series._form')

        <div class="form-footer">
            <x-button :href="route('admin.series.index')" variant="ghost">Cancel</x-button>
            <x-button icon="floppy-disk">Save series</x-button>
        </div>
    </form>
@endsection
