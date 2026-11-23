@extends('layouts.app')

@section('title', 'Edit ' . $series->title)

@section('content')
    <a href="{{ route('admin.series.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to TV series</a>

    <x-page-header eyebrow="Editing" :title="$series->title" subtitle="Update the details, seasons, genres or poster for this series." />

    <form action="{{ route('admin.series.update', $series->id) }}" method="POST" enctype="multipart/form-data" class="form-card" data-loading novalidate>
        @csrf
        @method('PUT')

        @include('admin.series._form', ['series' => $series])

        <div class="form-footer">
            <x-button :href="route('admin.series.index')" variant="ghost">Cancel</x-button>
            <x-button icon="floppy-disk">Update series</x-button>
        </div>
    </form>
@endsection
