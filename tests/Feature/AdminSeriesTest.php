<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Director;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSeriesTest extends TestCase
{
    use RefreshDatabase;

    private Director $gilligan;
    private Director $nolan;
    private Genre $crime;
    private Genre $drama;
    private Genre $scifi;

    protected function setUp(): void
    {
        parent::setUp();

        // Admin pages require a logged-in admin.
        $this->actingAs(Admin::create(['name' => 'Admin', 'email' => 'admin', 'password' => 'secret']), 'admin');

        $this->gilligan = Director::create(['name' => 'Vince Gilligan']);
        $this->nolan = Director::create(['name' => 'Christopher Nolan']);
        $this->crime = Genre::create(['name' => 'Crime']);
        $this->drama = Genre::create(['name' => 'Drama']);
        $this->scifi = Genre::create(['name' => 'Sci-Fi']);
    }

    private function makeSeries(string $title, Director $director, int $start, ?int $end, array $genres, int $seasons = 3): Series
    {
        $series = Series::create([
            'title'       => $title,
            'director_id' => $director->id,
            'start_year'  => $start,
            'end_year'    => $end,
            'seasons'     => $seasons,
        ]);
        $series->genres()->sync(collect($genres)->pluck('id'));

        return $series;
    }

    private function makeMovie(string $title): Movie
    {
        $movie = Movie::create(['title' => $title, 'director_id' => $this->nolan->id, 'release_year' => 2010]);
        $movie->genres()->sync([$this->scifi->id]);

        return $movie;
    }

    private function validSeries(array $overrides = []): array
    {
        return array_merge([
            'title'       => 'Breaking Bad',
            'director_id' => $this->gilligan->id,
            'start_year'  => 2008,
            'end_year'    => 2013,
            'seasons'     => 5,
            'episodes'    => 62,
            'genre_ids'   => [$this->crime->id, $this->drama->id],
            'description' => 'A chemistry teacher turns to crime.',
        ], $overrides);
    }

    private function seedCatalogue(): void
    {
        $this->makeSeries('Breaking Bad', $this->gilligan, 2008, 2013, [$this->crime, $this->drama]);
        $this->makeSeries('Better Call Saul', $this->gilligan, 2015, 2022, [$this->crime, $this->drama]);
        $this->makeSeries('Westworld', $this->nolan, 2016, 2022, [$this->scifi, $this->drama]);
        $this->makeSeries('Severance', $this->nolan, 2022, null, [$this->scifi]);
    }

    private function titlesFor(array $query): array
    {
        return $this->get(route('admin.series.index', $query))
            ->assertOk()
            ->viewData('series')
            ->pluck('title')
            ->sort()
            ->values()
            ->all();
    }

    // ---------- CRUD ----------

    public function test_index_lists_series_as_cards(): void
    {
        $this->makeSeries('Severance', $this->nolan, 2022, null, [$this->scifi], 2);

        $this->get(route('admin.series.index'))
            ->assertOk()
            ->assertSee('class="movie-card series-card"', false)
            ->assertSee('Severance')
            ->assertSee('Christopher Nolan')
            ->assertSee('2022 – Present')
            ->assertSee('2 seasons')
            ->assertSee('Ongoing')
            ->assertDontSee('<table', false);
    }

    public function test_index_shows_empty_state(): void
    {
        $this->get(route('admin.series.index'))->assertOk()->assertSee('No series found.');
    }

    public function test_admin_can_add_a_series_with_genres_and_poster(): void
    {
        Storage::fake('public');

        $this->post(route('admin.series.store'), $this->validSeries([
            'poster' => UploadedFile::fake()->image('poster.jpg'),
        ]))->assertRedirect(route('admin.series.index'))
            ->assertSessionHas('success', 'TV series created successfully');

        $series = Series::firstWhere('title', 'Breaking Bad');
        $this->assertNotNull($series);
        $this->assertSame(2013, $series->end_year);
        $this->assertSame(62, $series->episodes);
        $this->assertEqualsCanonicalizing(['Crime', 'Drama'], $series->genres->pluck('name')->all());
        Storage::disk('public')->assertExists('series/' . $series->poster);
    }

    public function test_ongoing_series_has_no_end_year(): void
    {
        $this->post(route('admin.series.store'), $this->validSeries(['end_year' => null, 'episodes' => null]))
            ->assertSessionHasNoErrors();

        $series = Series::first();
        $this->assertTrue($series->isOngoing());
        $this->assertSame('2008 – Present', $series->yearsLabel());
    }

    public function test_store_validates_input(): void
    {
        $this->post(route('admin.series.store'), [])
            ->assertSessionHasErrors(['title', 'director_name', 'start_year', 'seasons', 'genre_ids']);

        $this->post(route('admin.series.store'), $this->validSeries(['end_year' => 2001]))
            ->assertSessionHasErrors('end_year');

        $this->post(route('admin.series.store'), $this->validSeries(['seasons' => 0]))
            ->assertSessionHasErrors('seasons');

        $this->post(route('admin.series.store'), $this->validSeries(['seasons' => 5, 'episodes' => 3]))
            ->assertSessionHasErrors('episodes');

        $this->assertSame(0, Series::count());
    }

    public function test_new_director_and_genre_are_created_without_duplicates(): void
    {
        $this->post(route('admin.series.store'), $this->validSeries([
            'director_id'   => null,
            'director_name' => '  vince   GILLIGAN ',
            'genre_ids'     => [],
            'new_genres'    => ['crime', 'Mystery'],
        ]))->assertSessionHasNoErrors();

        $series = Series::first();
        $this->assertSame($this->gilligan->id, $series->director_id);
        $this->assertSame(2, Director::count());
        $this->assertEqualsCanonicalizing(['Crime', 'Mystery'], $series->genres->pluck('name')->all());
        $this->assertSame(4, Genre::count());
    }

    public function test_edit_form_is_prefilled(): void
    {
        $series = $this->makeSeries('Breaking Bad', $this->gilligan, 2008, 2013, [$this->crime], 5);

        $this->get(route('admin.series.edit', $series->id))
            ->assertOk()
            ->assertSee('value="Breaking Bad"', false)
            ->assertSee('value="Vince Gilligan"', false)
            ->assertSee('value="2008"', false)
            ->assertSee('value="2013"', false);
    }

    public function test_admin_can_update_a_series(): void
    {
        $series = $this->makeSeries('Breaking Bad', $this->gilligan, 2008, null, [$this->crime]);

        $this->put(route('admin.series.update', $series->id), $this->validSeries([
            'title'     => 'Breaking Bad (Remastered)',
            'end_year'  => 2013,
            'genre_ids' => [$this->drama->id],
        ]))->assertRedirect(route('admin.series.index'));

        $series->refresh();
        $this->assertSame('Breaking Bad (Remastered)', $series->title);
        $this->assertSame(2013, $series->end_year);
        $this->assertSame(['Drama'], $series->genres->pluck('name')->all());
    }

    public function test_edit_missing_series_returns_404(): void
    {
        $this->get(route('admin.series.edit', 999))->assertNotFound();
    }

    public function test_admin_can_delete_a_series_and_pivot_rows_are_removed(): void
    {
        $series = $this->makeSeries('Breaking Bad', $this->gilligan, 2008, 2013, [$this->crime, $this->drama]);

        $this->get(route('admin.series.index'))->assertSee('data-confirm=', false);

        $this->delete(route('admin.series.destroy', $series->id))
            ->assertRedirect(route('admin.series.index'));

        $this->assertModelMissing($series);
        $this->assertSame(0, DB::table('series_genres')->count());
        $this->assertSame(2, Director::count());
        $this->assertSame(3, Genre::count());
    }

    // ---------- Search & filters ----------

    public function test_search_and_filters_combine(): void
    {
        $this->seedCatalogue();

        $this->assertSame(['Better Call Saul', 'Breaking Bad'], $this->titlesFor(['search' => 'b']));
        $this->assertSame(['Better Call Saul', 'Breaking Bad'], $this->titlesFor(['director_id' => $this->gilligan->id]));
        $this->assertSame(['Severance', 'Westworld'], $this->titlesFor(['genre_id' => $this->scifi->id]));
        $this->assertSame(['Westworld'], $this->titlesFor(['start_year' => 2016]));
        $this->assertSame(['Severance'], $this->titlesFor(['status' => 'ongoing']));
        $this->assertCount(3, $this->titlesFor(['status' => 'ended']));

        $this->assertSame(['Westworld'], $this->titlesFor([
            'search' => 'west', 'director_id' => $this->nolan->id, 'genre_id' => $this->drama->id,
            'start_year' => 2016, 'status' => 'ended',
        ]));
        $this->assertSame([], $this->titlesFor(['director_id' => $this->gilligan->id, 'genre_id' => $this->scifi->id]));
        $this->assertCount(4, $this->titlesFor(['search' => '', 'director_id' => '', 'status' => '']));
    }

    public function test_year_filter_lists_only_existing_start_years(): void
    {
        $this->seedCatalogue();

        $years = $this->get(route('admin.series.index'))->viewData('years')->all();
        $this->assertSame([2022, 2016, 2015, 2008], array_map('intval', $years));
    }

    // ---------- Movies and series stay independent ----------

    public function test_movies_and_series_are_independent(): void
    {
        $this->makeMovie('Inception');
        $this->makeSeries('Severance', $this->nolan, 2022, null, [$this->scifi]);

        $this->get(route('admin.movies.index'))->assertSee('Inception')->assertDontSee('Severance');
        $this->get(route('admin.series.index'))->assertSee('Severance')->assertDontSee('Inception');

        // Shared directors and genres relate to both content types.
        $this->assertSame(1, $this->nolan->movies()->count());
        $this->assertSame(1, $this->nolan->series()->count());
        $this->assertSame(['Inception'], $this->scifi->movies->pluck('title')->all());
        $this->assertSame(['Severance'], $this->scifi->series->pluck('title')->all());

        // Deleting the series leaves the movie untouched.
        $this->delete(route('admin.series.destroy', Series::first()->id));
        $this->assertSame(1, Movie::count());
        $this->assertSame(1, DB::table('movie_genres')->count());
    }

    public function test_content_type_switch_links_both_catalogues_and_keeps_shared_filters(): void
    {
        $this->get(route('admin.movies.index', ['search' => 'bad', 'release_year' => 2010]))
            ->assertSee('aria-label="Content type"', false)
            ->assertSee(route('admin.series.index', ['search' => 'bad']), false);

        $this->get(route('admin.series.index'))
            ->assertSee('aria-label="Content type"', false)
            ->assertSee(route('admin.movies.index'), false);
    }

    public function test_series_pages_share_the_master_layout(): void
    {
        $series = $this->makeSeries('Severance', $this->nolan, 2022, null, [$this->scifi]);

        foreach ([
            route('admin.series.index'),
            route('admin.series.create'),
            route('admin.series.edit', $series->id),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('class="navbar"', false)
                ->assertSee(route('admin.series.index'), false);
        }
    }

    public function test_dashboard_content_type_filter(): void
    {
        $this->makeMovie('Inception');
        $this->seedCatalogue();

        $all = $this->get(route('admin.dashboard'))->assertOk();
        $this->assertSame(1, $all->viewData('movieCount'));
        $this->assertSame(4, $all->viewData('seriesCount'));
        $this->assertCount(1, $all->viewData('latestMovies'));
        $this->assertCount(4, $all->viewData('latestSeries'));
        $this->assertSame('Sci-Fi', $all->viewData('topGenres')->firstWhere('name', 'Sci-Fi')->name);
        $this->assertSame(3, $all->viewData('topGenres')->firstWhere('name', 'Sci-Fi')->titles_count);

        $movies = $this->get(route('admin.dashboard', ['type' => 'movies']))->assertOk()->assertDontSee('Recently added TV series');
        $this->assertCount(0, $movies->viewData('latestSeries'));
        $this->assertSame(['Sci-Fi'], $movies->viewData('topGenres')->pluck('name')->all());

        $series = $this->get(route('admin.dashboard', ['type' => 'series']))->assertOk()->assertDontSee('Recently added movies');
        $this->assertCount(0, $series->viewData('latestMovies'));
        $this->assertSame(['Drama', 'Crime', 'Sci-Fi'], $series->viewData('topGenres')->pluck('name')->all());

        $this->get(route('admin.dashboard', ['type' => 'nonsense']))->assertOk()->assertViewHas('type', 'all');
    }

    public function test_pivot_rejects_duplicate_series_genre_pairs(): void
    {
        $series = $this->makeSeries('Severance', $this->nolan, 2022, null, [$this->scifi]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('series_genres')->insert(['series_id' => $series->id, 'genre_id' => $this->scifi->id]);
    }
}
