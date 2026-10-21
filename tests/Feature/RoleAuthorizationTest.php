<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_transactions_but_cannot_view_products(): void
    {
        $this->withoutVite();

        $manager = User::factory()->create(['role' => 'manager']);

        $this->actingAs($manager)
            ->get('/transactions')
            ->assertOk();

        $this->actingAs($manager)
            ->get('/products')
            ->assertForbidden();
    }
}
