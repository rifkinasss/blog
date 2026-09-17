<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Dashboard\Categories\CategoryIndex;
use App\Livewire\Dashboard\Tags\TagIndex;
use App\Livewire\Dashboard\Users\UserIndex;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardDataManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_administrator_can_create_a_category_from_the_dialog(): void
    {
        Livewire::test(CategoryIndex::class)
            ->call('openCreate')
            ->assertDispatched('modal-show')
            ->set('name', 'Platform Engineering')
            ->set('description', 'Notes about developer platforms.')
            ->call('save')
            ->assertDispatched('modal-close');

        $this->assertDatabaseHas('categories', [
            'name' => 'Platform Engineering',
            'slug' => 'platform-engineering',
        ]);
    }

    public function test_an_administrator_can_create_a_tag_from_the_dialog(): void
    {
        Livewire::test(TagIndex::class)
            ->call('openCreate')
            ->assertDispatched('modal-show')
            ->set('name', 'Kubernetes')
            ->call('save')
            ->assertDispatched('modal-close');

        $this->assertDatabaseHas('tags', [
            'name' => 'Kubernetes',
            'slug' => 'kubernetes',
        ]);
    }

    public function test_an_administrator_can_create_a_user_from_the_dialog(): void
    {
        $this->actingAs(User::create([
            'name' => 'Administrator',
            'email' => 'administrator@example.test',
            'role' => UserRole::Administrator,
            'password' => 'password',
        ]));

        Livewire::test(UserIndex::class)
            ->call('openCreate')
            ->assertDispatched('modal-show')
            ->set('name', 'Editor')
            ->set('email', 'editor@example.test')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('createUser')
            ->assertDispatched('modal-close');

        $this->assertDatabaseHas('users', [
            'name' => 'Editor',
            'email' => 'editor@example.test',
            'role' => UserRole::Editor->value,
        ]);
    }

    public function test_category_and_tag_names_must_be_unique(): void
    {
        Category::create(['name' => 'Homelab', 'slug' => 'homelab']);
        Tag::create(['name' => 'Docker', 'slug' => 'docker']);

        Livewire::test(CategoryIndex::class)
            ->set('name', 'Homelab')
            ->call('save')
            ->assertHasErrors('name');

        Livewire::test(TagIndex::class)
            ->set('name', 'Docker')
            ->call('save')
            ->assertHasErrors('name');
    }
}
