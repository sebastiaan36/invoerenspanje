<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Reviews\Pages\CreateReview;
use App\Filament\Resources\Reviews\Pages\EditReview;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReviewResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_admin_can_list_reviews(): void
    {
        $reviews = Review::factory()->count(2)->create();

        Livewire::test(ListReviews::class)
            ->assertOk()
            ->assertCanSeeTableRecords($reviews);
    }

    public function test_admin_can_create_a_review(): void
    {
        Livewire::test(CreateReview::class)
            ->fillForm([
                'name' => 'Jan en Marieke',
                'location' => 'Estepona',
                'rating' => 5,
                'reviewed_on' => '2026-09-14',
                'source' => 'Google',
                'text' => 'Alles netjes geregeld.',
                'is_published' => true,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $review = Review::sole();
        $this->assertSame('Jan en Marieke', $review->name);
        $this->assertTrue($review->is_published);
        $this->assertSame('2026-09-14', $review->reviewed_on->toDateString());
    }

    public function test_name_rating_date_and_text_are_required(): void
    {
        Livewire::test(CreateReview::class)
            ->fillForm(['name' => '', 'rating' => null, 'reviewed_on' => null, 'text' => ''])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'rating' => 'required', 'reviewed_on' => 'required', 'text' => 'required']);

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_admin_can_unpublish_a_review(): void
    {
        $review = Review::factory()->published()->create();

        Livewire::test(EditReview::class, ['record' => $review->getRouteKey()])
            ->fillForm(['is_published' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($review->fresh()->is_published);
    }

    public function test_customers_cannot_open_the_reviews_admin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/reviews')
            ->assertForbidden();
    }
}
