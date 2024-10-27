<?php

namespace App\Jobs;

use App\Http\Controllers\MovieController;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class FetchMoviesJob implements ShouldQueue
{
    use Queueable;
    public $iteration;

    /**
     * Create a new job instance.
     *
     * @param int $iteration
     */
    public function __construct($iteration = 1)
    {
        $this->iteration = $iteration;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Instantiate the MovieController and call the 'get' function
            $controller = App::make(MovieController::class);
            $result = $controller->syncMovies();
            
            // On server
            // if($result['data']['get_count'] > 0){
            //     FetchMoviesJob::dispatch($this->iteration + 1)->delay(now()->addSeconds(5));
            // }
            // else{
            //     Log::info('Iteration stops after fetching all data',[$result]);
            // }

            // On local machine
            $maxIterations = 20;
            if ($this->iteration < $maxIterations) {
                Log::info('Iteration '.$this->iteration.' completed. Starting next...',[$result]);
                FetchMoviesJob::dispatch($this->iteration + 1)->delay(now()->addSeconds(5));
            } else {
                Log::info('Completed '.$maxIterations.' iterations of movies fetching.',[$result]);
            }
        } catch (Exception $e) {
            // Handle exceptions if needed
            Log::error('Failed to fetch movies: ' . $e->getMessage());
            throw $e;
        }
    }
}
