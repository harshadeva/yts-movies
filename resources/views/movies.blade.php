@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center mb-4">Movie Library</h1>

    <!-- Filters Form -->
    <form method="GET" action="{{ url('movies') }}" class="row g-3 align-items-end mb-3">
        <!-- Include Genres Filter -->
        <div class="col-md-3">
            <label for="include_genres" class="form-label">Include Genres</label>
            <select class="form-select" name="include_genres[]" id="include_genres" multiple>
                @foreach($genres as $genre)
                    <option value="{{ $genre }}" {{ in_array($genre, $includeGenres) ? 'selected' : '' }}>
                        {{ $genre }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Exclude Genres Filter -->
        <div class="col-md-3">
            <label for="exclude_genres" class="form-label">Exclude Genres</label>
            <select class="form-select" name="exclude_genres[]" id="exclude_genres" multiple>
                @foreach($genres as $genre)
                    <option value="{{ $genre }}" {{ in_array($genre, $excludeGenres) ? 'selected' : '' }}>
                        {{ $genre }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Language Filter -->
        <div class="col-md-2">
            <label for="language" class="form-label">Language</label>
            <select class="form-select" name="language" id="language">
                <option value="">Any</option>
                @foreach($languages as $lang)
                    <option value="{{ $lang }}" {{ $lang === $language ? 'selected' : '' }}>
                        {{ $lang }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Rating Filter -->
        <div class="col-md-2">
            <label for="rating" class="form-label">Minimum Rating</label>
            <input type="number" step="0.1" min="0" max="10" class="form-control" 
                   name="rating" id="rating" value="{{ $rating }}">
        </div>

        <!-- Sort By Dropdown -->
        <div class="col-md-2">
            <label for="sort_by" class="form-label">Sort By</label>
            <select class="form-select" name="sort_by" id="sort_by">
                <option value="rating" {{ request('sort_by') === 'rating' ? 'selected' : '' }}>Rating</option>
                <option value="year" {{ request('sort_by') === 'year' ? 'selected' : '' }}>Year</option>
                <option value="date_uploaded" {{ request('sort_by') === 'date_uploaded' ? 'selected' : '' }}>Date Uploaded</option>
            </select>
        </div>

        <!-- Submit Button -->
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Selected Filters -->
    <div class="mb-3">
        @if($includeGenres)
            @foreach($includeGenres as $genre)
                <a href="{{ url('movies?' . http_build_query(array_merge(request()->except('include_genres'), ['include_genres' => array_diff($includeGenres, [$genre])]))) }}" class="badge bg-success me-2">
                    {{ $genre }} ×
                </a>
            @endforeach
        @endif

        @if($excludeGenres)
            @foreach($excludeGenres as $genre)
                <a href="{{ url('movies?' . http_build_query(array_merge(request()->except('exclude_genres'), ['exclude_genres' => array_diff($excludeGenres, [$genre])]))) }}" class="badge bg-danger me-2">
                    {{ $genre }} ×
                </a>
            @endforeach
        @endif

        @if($language)
            <a href="{{ url('movies?' . http_build_query(request()->except('language'))) }}" class="badge bg-info me-2">
                {{ $language }} ×
            </a>
        @endif

        @if($rating)
            <a href="{{ url('movies?' . http_build_query(request()->except('rating'))) }}" class="badge bg-warning me-2">
                Rating: {{ $rating }} ×
            </a>
        @endif

        @if(request('sort_by'))
            <a href="{{ url('movies?' . http_build_query(request()->except('sort_by'))) }}" class="badge bg-secondary me-2">
                Sort: {{ ucfirst(request('sort_by')) }} ×
            </a>
        @endif
    </div>

    <!-- Total Results -->
    <div class="mb-3 text-end">
        <strong>Total Results: {{ $movies->total() }}</strong>
    </div>

    <!-- Movies List -->
    <div class="row">
        @forelse($movies as $movie)
            <div class="col-6 col-md-3 col-lg-2 mb-4">
                <a href="{{ $movie->url }}" target="_blank" class="text-decoration-none text-dark">
                    <div class="card movie-card position-relative h-100">
                        <img src="{{ $movie->medium_cover_image }}" class="card-img-top" alt="{{ $movie->title }}">

                        <!-- Rating Badge -->
                        <div class="rating-badge position-absolute top-50 start-50 translate-middle">
                            <span class="badge bg-dark text-white p-2 fs-5">{{ $movie->rating }}</span>
                        </div>

                        <div class="card-body p-2">
                            <h6 class="card-title text-truncate">{{ $movie->title }}</h6>
                            <p class="mb-1 small">
                                <strong>Year:</strong> {{ $movie->year }}
                            </p>
                            <p class="mb-0 small">
                                <strong>Language:</strong> {{ $movie->language }}
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    No movies found for the selected filters.
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $movies->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Custom CSS -->
<style>
    body {
        background-color: #56748f
    }
    .movie-card {
        transition: box-shadow 0.3s ease-in-out;
    }

    .movie-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .rating-badge {
        background-color: rgba(0, 0, 0, 0.75);
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection
