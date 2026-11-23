{{-- <x-search-input name="search" :value="request('search')" placeholder="Search…" /> · press "/" to focus --}}
@props([
    'name' => 'search',
    'value' => null,
    'placeholder' => 'Search…',
])

<div {{ $attributes->only('class')->class('search-field') }}>
    <i class="fa-solid fa-magnifying-glass search-icon" aria-hidden="true"></i>
    <input type="search" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}"
           autocomplete="off" data-search-shortcut {{ $attributes->except('class') }}>
    <kbd class="search-kbd" aria-hidden="true">/</kbd>
</div>
