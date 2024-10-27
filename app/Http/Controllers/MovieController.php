<?php

namespace App\Http\Controllers;

use App\Jobs\FetchMoviesJob;
use App\Models\Genre;
use App\Models\MetaData;
use App\Models\Movie;
use App\Models\MovieGenre;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MovieController extends Controller
{
    public function startFetchingMovies()
    {
        // Dispatch the initial job to start the process
        FetchMoviesJob::dispatch();

        return response()->json(['message' => 'Movie fetching started.'], 200);
    }

    public function render(Request $request)
    {
        // Get filter inputs
        $includeGenres = $request->input('include_genres', []);
        $excludeGenres = $request->input('exclude_genres', []);
        $language = $request->input('language');
        $rating = $request->input('rating');

        // Build the query with filters
        $query = Movie::with('genres');

        if (!empty($includeGenres)) {
            $query->whereHas('genres', function ($q) use ($includeGenres) {
                $q->whereIn('name', $includeGenres);
            });
        }

        if (!empty($excludeGenres)) {
            $query->whereDoesntHave('genres', function ($q) use ($excludeGenres) {
                $q->whereIn('name', $excludeGenres);
            });
        }

        if ($language) {
            $query->where('language', $language);
        }

        if ($rating) {
            $query->where('rating', '>=', $rating);
        }

        if (isset($request['sort_by'])) {
            $query->orderBy($request['sort_by'], 'desc');
        }

        // Paginate results
        $movies = $query->paginate(48);

        // Get unique genres and languages for filter options
        $genres = Genre::pluck('name')->unique();
        $languages = Movie::pluck('language')->unique();

        return view('movies', compact('movies', 'genres', 'languages', 'includeGenres', 'excludeGenres', 'language', 'rating'));
    }

    public function syncMovies()
    {
        DB::beginTransaction();
        try {
            $metaData = MetaData::latest()->first();
            $page = $metaData ? $metaData->last_sync_page + 1 : 1;
            $limit = $metaData->limit ?? 50;

            // Call the API with the next page
            $response = Http::get("https://yts.mx/api/v2/list_movies.json?sort=date_added", [
                'limit' => $limit,
                'page' => $page
            ]);

            // Check if the response was successful
            if ($response->successful()) {
                // Update the total movie count if provided
                $newMeta['total_movie_count'] = $response['data']['movie_count'] ?? 0;

                $movies = $response->json('data.movies');

                // Iterate through each movie
                foreach ($movies as $movieData) {
                    // Save movie data
                    $movie = Movie::updateOrCreate(
                        ['movie_id' => $movieData['id']],  // Check for existing entry by movie_id
                        [
                            'url' => $movieData['url'] ?? null,
                            'imdb_code' => $movieData['imdb_code'] ?? null,
                            'title' => $movieData['title'] ?? null,
                            'title_english' => $movieData['title_english'] ?? null,
                            'title_long' => $movieData['title_long'] ?? null,
                            'slug' => $movieData['slug'] ?? null,
                            'year' => $movieData['year'] ?? null,
                            'rating' => $movieData['rating'] ?? null,
                            'runtime' => $movieData['runtime'] ?? null,
                            'summary' => $movieData['summary'] ?? null,
                            'description_full' => $movieData['description_full'] ?? null,
                            'synopsis' => $movieData['synopsis'] ?? null,
                            'yt_trailer_code' => $movieData['yt_trailer_code'] ?? null,
                            'language' => $movieData['language'] ?? null,
                            'mpa_rating' => $movieData['mpa_rating'] ?? null,
                            'background_image' => $movieData['background_image'] ?? null,
                            'background_image_original' => $movieData['background_image_original'] ?? null,
                            'small_cover_image' => $movieData['small_cover_image'] ?? null,
                            'medium_cover_image' => $movieData['medium_cover_image'] ?? null,
                            'large_cover_image' => $movieData['large_cover_image'] ?? null,
                            'state' => $movieData['state'] ?? null,
                            'date_uploaded' => $movieData['date_uploaded'] ?? null,
                        ]
                    );

                    // Attach genres
                    if (!empty($movieData['genres'])) {
                        foreach ($movieData['genres'] as $genreName) {
                            $genre = Genre::firstOrCreate(['name' => $genreName]);
                            MovieGenre::firstOrCreate(['genre_id' => $genre->id, 'movie_id' => $movie->id]);
                        }
                    }
                }

                $count = count($movies);
                if($count == 0) $page = max($page - 1 ,0);
                // Update the meta data
                $newMeta['last_sync_page'] = $page;
                $newMeta['limit'] = $limit;
                $newMeta['get_count'] = $count;
                $newMeta['total_synced_count'] = $metaData ? $metaData->total_synced_count + $count : $count;
                $latestMeta = MetaData::create($newMeta);
                DB::commit();
                return ['data'=>$latestMeta,'status'=>200];
            }

            return ['message' => 'Failed to fetch movies','status'=>500];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function startSyncMovies(){
        $result = $this->syncMovies();
        return response()->json($result, 200);
    }
}
