<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_login(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Rahul Verma',
            'email' => 'rahul@example.com',
            'phone' => '9876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'rahul@example.com',
            'role' => 'customer',
        ]);
    }

    public function test_seller_onboarding_registration_creates_pending_seller(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this->actingAs($user)->post(route('seller.register.submit'), [
            'store_name' => 'Amit Electronics',
            'phone' => '9123456780',
            'business_email' => 'amit.store@example.com',
            'business_address' => 'Shop 12, MI Road',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'gst_number' => '08AAAAA0000A1Z5',
            'pan_number' => 'ABCDE1234F',
            'bank_account_name' => 'Amit Sharma',
            'bank_account_number' => '123456789012',
            'bank_ifsc' => 'SBIN0001234',
        ]);

        $response->assertRedirect(route('seller.status'));

        $seller = Seller::where('store_name', 'Amit Electronics')->first();
        $this->assertNotNull($seller);
        $this->assertEquals('pending', $seller->status);
        $this->assertEquals('seller', $user->fresh()->role);
    }

    public function test_customer_cannot_access_seller_dashboard(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->get(route('seller.dashboard'));
        $response->assertRedirect(route('seller.register'));
    }

    public function test_seller_cannot_access_admin_portal(): void
    {
        $sellerUser = User::factory()->create([
            'role' => 'seller',
        ]);

        $response = $this->actingAs($sellerUser)->get(route('admin.dashboard'));
        $response->assertRedirect(route('home'));
    }

    public function test_api_unauthorized_access_returns_403_json(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->getJson('/seller/dashboard');
        $response->assertStatus(403);
    }
}
