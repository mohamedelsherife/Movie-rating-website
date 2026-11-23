<?php

namespace Tests\Feature;

use App\Models\Director;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMovieTest extends TestCase
{
    use RefreshDatabase;

    private Director $nolan;
    private Director $spielberg;
    private Genre $action;
    private Genre $drama;
    private Genre $scifi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nolan = Director::create(['name' => 'Christopher Nolan']);
        $this->spielberg = Director::create(['name' => 'Steven Spielberg']);
        $this->action = Genre::create(['name' => 'Action']);
        $this->drama = Genre::create(['name' => 'Drama']);
        $this->scifi = Genre::create(['name' => 'Sci-Fi']);
    }

    private function makeMovie(string $title, Director $director, int $year, array $genres): Movie
    {
        $movie = Movie::create([
            'title'        => $title,
            'director_id'  => $director->id,
            'release_year' => $year,
        ]);
        $movie->genres()->sync(collect($genres)->pluck('id'));

        return $movie;
    }

    // ---------- Display ----------

    public function test_index_lists_movies_with_director_and_genres(): void
    {
        $this->makeMovie('Inception', $this->nolan, 2010, [$this->action, $this->scifi]);

        $this->get(route('admin.movies.index'))
            ->assertOk()
            ->assertSee('Inception')
            ->assertSee('Christopher Nolan')
            ->assertSee('Sci-Fi')
            ->assertSee('2010');
    }

    public function test_index_shows_empty_state(): void
    {
        $this->get(route('admin.movies.index'))->assertOk()->assertSee('No movies found.');
    }

    // ---------- Create ----------

    public function test_create_form_lists_directors_and_genres(): void
    {
        $this->get(route('admin.movies.create'))
            ->assertOk()
            ->assertSee('Steven Spielberg')
            ->assertSee('Drama');
    }

    public function test_admin_can_add_a_movie_with_multiple_genres_and_poster(): void
    {
        Storage::fake('public');

        $this->post(route('admin.movies.store'), [
            'title'        => 'Interstellar',
            'director_id'  => $this->nolan->id,
            'release_year' => 2014,
            'description'  => 'Space travel.',
            'genre_ids'    => [$this->drama->id, $this->scifi->id],
            'poster'       => UploadedFile::fake()->image('poster.jpg'),
        ])->assertRedirect(route('admin.movies.index'))
          ->assertSessionHas('success', 'Movie created successfully');

        $movie = Movie::where('title', 'Interstellar')->firstOrFail();
        $this->assertSame($this->nolan->id, $movie->director_id);
        $this->assertEqualsCanonicalizing(
            [$this->drama->id, $this->scifi->id],
            $movie->genres->pluck('id')->all()
        );
        $this->assertDatabaseCount('movie_genres', 2);
        Storage::disk('public')->assertExists('movies/' . $movie->poster);
    }

    public function test_store_validates_input(): void
    {
        $this->post(route('admin.movies.store'), [
            'title'        => '',
            'director_id'  => 999,
            'release_year' => 1500,
            'genre_ids'    => [],
        ])->assertSessionHasErrors(['title', 'director_id', 'release_year', 'genre_ids']);

        $this->post(route('admin.movies.store'), [
            'title'        => 'X',
            'director_id'  => $this->nolan->id,
            'release_year' => 2020,
            'genre_ids'    => [999],
        ])->assertSessionHasErrors(['genre_ids.0']);

        $this->assertDatabaseCount('movies', 0);
    }

    // ---------- Edit ----------

    public function test_edit_form_is_prefilled(): void
    {
        $movie = $this->makeMovie('Inception', $this->nolan, 2010, [$this->action]);

        $this->get(route('admin.movies.edit', $movie->id))
            ->assertOk()
            ->assertSee('value="Inception"', false)
            ->assertSee('value="2010"', false);
    }

    public function test_admin_can_update_a_movie_and_its_genres(): void
    {
        $movie = $this->makeMovie('Inceptoin', $this->nolan, 2009, [$this->action, $this->scifi]);

        $this->put(route('admin.movies.update', $movie->id), [
            'title'        => 'Jurassic Park',
            'director_id'  => $this->spielberg->id,
            'release_year' => 1993,
            'genre_ids'    => [$this->drama->id],
        ])->assertRedirect(route('admin.movies.index'))
          ->assertSessionHas('success', 'Movie updated successfully');

        $movie->refresh();
        $this->assertSame('Jurassic Park', $movie->title);
        $this->assertSame($this->spielberg->id, $movie->director_id);
        $this->assertSame(1993, (int) $movie->release_year);
        $this->assertSame([$this->drama->id], $movie->genres->pluck('id')->all());
        $this->assertDatabaseCount('movie_genres', 1);
    }

    public function test_edit_missing_movie_returns_404(): void
    {
        $this->get(route('admin.movies.edit', 999))->assertNotFound();
    }

    // ---------- Delete ----------

    public function test_admin_can_delete_a_movie_and_pivot_rows_are_removed(): void
    {
        $movie = $this->makeMovie('Inception', $this->nolan, 2010, [$this->action, $this->scifi]);

        $this->delete(route('admin.movies.destroy', $movie->id))
            ->assertRedirect(route('admin.movies.index'))
            ->assertSessionHas('success', 'Movie deleted successfully');

        $this->assertDatabaseMissing('movies', ['id' => $movie->id]);
        $this->assertDatabaseCount('movie_genres', 0);
        // Directors and genres themselves are untouched.
        $this->assertDatabaseHas('directors', ['id' => $this->nolan->id]);
        $this->assertDatabaseCount('genres', 3);
    }

    public function test_delete_button_asks_for_confirmation(): void
    {
        $this->makeMovie('Inception', $this->nolan, 2010, [$this->action]);

        $this->get(route('admin.movies.index'))
            ->assertSee('data-confirm="&quot;Inception&quot; will be permanently removed', false)
            ->assertSee('id="confirm-dialog"', false);
    }

    // ---------- Layout & dashboard ----------

    public function test_all_admin_pages_share_the_master_layout(): void
    {
        $movie = $this->makeMovie('Inception', $this->nolan, 2010, [$this->action]);

        foreach ([
            route('admin.dashboard'),
            route('admin.movies.index'),
            route('admin.movies.create'),
            route('admin.movies.edit', $movie->id),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('class="navbar"', false)
                ->assertSee('class="footer"', false)
                ->assertSee('css/style.css', false);
        }
    }

    public function test_movies_are_rendered_as_cards_not_a_table(): void
    {
        $this->makeMovie('Inception', $this->nolan, 2010, [$this->action, $this->scifi]);

        $this->get(route('admin.movies.index'))
            ->assertSee('class="movie-card"', false)
            ->assertDontSee('<table', false);
    }

    public function test_dashboard_shows_counts_latest_movies_and_genres(): void
    {
        $this->seedCatalogue();

        $response = $this->get(route('admin.dashboard'))->assertOk();
        $this->assertSame(5, $response->viewData('movieCount'));
        $this->assertSame(2, $response->viewData('directorCount'));
        $this->assertSame(3, $response->viewData('genreCount'));
        $this->assertCount(4, $response->viewData('latestMovies'));
        $this->assertSame(['Drama', 'Sci-Fi', 'Action'], $response->viewData('topGenres')->pluck('name')->all());
    }

    // ---------- Search & filters ----------

    private function seedCatalogue(): void
    {
        $this->makeMovie('Inception', $this->nolan, 2010, [$this->action, $this->scifi]);
        $this->makeMovie('Interstellar', $this->nolan, 2014, [$this->drama, $this->scifi]);
        $this->makeMovie('Oppenheimer', $this->nolan, 2023, [$this->drama]);
        $this->makeMovie('Jurassic Park', $this->spielberg, 1993, [$this->action, $this->scifi]);
        $this->makeMovie('Lincoln', $this->spielberg, 2012, [$this->drama]);
    }

    private function titlesFor(array $query): array
    {
        return $this->get(route('admin.movies.index', $query))
            ->assertOk()
            ->viewData('movies')
            ->pluck('title')
            ->sort()
            ->values()
            ->all();
    }

    public function test_search_by_title_is_partial(): void
    {
        $this->seedCatalogue();

        // "In" also matches the middle of "Lincoln" (LIKE %In%).
        $this->assertSame(['Inception', 'Interstellar', 'Lincoln'], $this->titlesFor(['search' => 'In']));
        $this->assertSame(['Interstellar'], $this->titlesFor(['search' => 'stellar']));
        $this->assertSame(['Jurassic Park'], $this->titlesFor(['search' => 'park']));
    }

    public function test_filter_by_director(): void
    {
        $this->seedCatalogue();

        $this->assertSame(['Jurassic Park', 'Lincoln'], $this->titlesFor(['director_id' => $this->spielberg->id]));
    }

    public function test_filter_by_release_year(): void
    {
        $this->seedCatalogue();

        $this->assertSame(['Oppenheimer'], $this->titlesFor(['release_year' => 2023]));
    }

    public function test_filter_by_genre(): void
    {
        $this->seedCatalogue();

        $this->assertSame(
            ['Inception', 'Interstellar', 'Jurassic Park'],
            $this->titlesFor(['genre_id' => $this->scifi->id])
        );
    }

    public function test_multiple_filters_combine(): void
    {
        $this->seedCatalogue();

        // Nolan + Sci-Fi
        $this->assertSame(
            ['Inception', 'Interstellar'],
            $this->titlesFor(['director_id' => $this->nolan->id, 'genre_id' => $this->scifi->id])
        );

        // Nolan + Sci-Fi + 2014
        $this->assertSame(
            ['Interstellar'],
            $this->titlesFor(['director_id' => $this->nolan->id, 'genre_id' => $this->scifi->id, 'release_year' => 2014])
        );

        // Search + director + genre
        $this->assertSame(
            ['Jurassic Park'],
            $this->titlesFor(['search' => 'park', 'director_id' => $this->spielberg->id, 'genre_id' => $this->action->id])
        );

        // Conflicting filters -> nothing
        $this->assertSame([], $this->titlesFor(['search' => 'Lincoln', 'director_id' => $this->nolan->id]));
    }

    public function test_empty_filters_return_all_movies(): void
    {
        $this->seedCatalogue();

        $this->assertCount(5, $this->titlesFor(['search' => '', 'director_id' => '', 'release_year' => '', 'genre_id' => '']));
    }

    public function test_year_dropdown_only_lists_years_that_exist(): void
    {
        $this->seedCatalogue();

        $years = $this->get(route('admin.movies.index'))->viewData('years')->all();
        $this->assertSame([2023, 2014, 2012, 2010, 1993], array_map('intval', $years));
    }

    // ---------- Relationships ----------

    public function test_relationships(): void
    {
        $this->seedCatalogue();

        $this->assertCount(3, $this->nolan->movies);
        $this->assertSame('Steven Spielberg', Movie::where('title', 'Lincoln')->first()->director->name);
        $this->assertCount(3, $this->scifi->movies);
        $this->assertCount(2, Movie::where('title', 'Inception')->first()->genres);
    }

    public function test_pivot_rejects_duplicate_movie_genre_pairs(): void
    {
        $movie = $this->makeMovie('Inception', $this->nolan, 2010, [$this->action]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('movie_genres')->insert(['movie_id' => $movie->id, 'genre_id' => $this->action->id]);
    }
}
