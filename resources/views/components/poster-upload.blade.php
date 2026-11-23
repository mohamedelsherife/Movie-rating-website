{{-- Poster picker with live preview. <x-poster-upload name="poster" :url="$currentPosterUrl" /> --}}
@props([
    'name' => 'poster',
    'url' => null,
])

<div {{ $attributes->class('poster-upload') }} data-poster-upload>
    <label for="{{ $name }}" class="poster-drop {{ $url ? 'has-image' : '' }}">
        <img data-poster-preview @if ($url) src="{{ $url }}" @else hidden @endif alt="Poster preview">
        <span class="poster-placeholder">
            <i class="fa-solid fa-image" aria-hidden="true"></i>
            <strong>Upload poster</strong>
            <small>JPG, PNG or WEBP · max 10 MB</small>
        </span>
        <span class="poster-change"><i class="fa-solid fa-arrow-up-from-bracket" aria-hidden="true"></i> Change</span>
    </label>
    <input type="file" name="{{ $name }}" id="{{ $name }}" accept=".jpg,.jpeg,.png,.webp" class="visually-hidden" data-poster-input>
    <p class="poster-filename" data-poster-filename hidden></p>
</div>
