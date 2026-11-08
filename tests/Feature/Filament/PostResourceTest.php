<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_bulk_delete_skips_file_managed_posts(): void
    {
        $filePost = $this->makePost('uit-bestand', 'file');
        $adminPost = $this->makePost('uit-admin', null);

        Livewire::test(ListPosts::class)
            ->callTableBulkAction('delete', [$filePost, $adminPost])
            ->assertNotified();

        $this->assertModelExists($filePost);
        $this->assertModelMissing($adminPost);
    }

    public function test_delete_action_is_hidden_for_file_managed_posts(): void
    {
        $filePost = $this->makePost('uit-bestand', 'file');
        $adminPost = $this->makePost('uit-admin', null);

        Livewire::test(ListPosts::class)
            ->assertTableActionHidden('delete', $filePost)
            ->assertTableActionVisible('delete', $adminPost);

        Livewire::test(EditPost::class, ['record' => $filePost->getRouteKey()])
            ->assertActionHidden(DeleteAction::class);
    }

    private function makePost(string $slug, ?string $source): Post
    {
        return Post::create([
            'title' => ucfirst($slug),
            'slug' => $slug,
            'content_html' => '<p>x</p>',
            'status' => Post::STATUS_PUBLISHED,
            'source' => $source,
            'published_at' => now()->subDay(),
            'author_id' => $this->admin->id,
        ]);
    }
}
