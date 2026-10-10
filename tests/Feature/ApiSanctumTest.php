<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSanctumTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_user_registration_and_login_generates_token(): void
    {
        // 1. Register API
        $regResponse = $this->postJson('/api/register', [
            'name' => 'Vikram Patel',
            'email' => 'vikram@example.com',
            'phone' => '9876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $regResponse->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'token',
                'user' => ['id', 'name', 'email', 'role'],
            ]);

        // 2. Login API
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'vikram@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['success', 'token', 'user']);

        $token = $loginResponse->json('token');

        // 3. Authenticated Profile Request
        $profileResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/profile');

        $profileResponse->assertStatus(200)
            ->assertJsonPath('user.email', 'vikram@example.com');
    }

    public function test_api_public_product_and_categories_endpoints(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'API Store',
            'slug' => 'api-store',
            'phone' => '9876543210',
            'business_address' => 'Delhi',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'status' => 'approved',
        ]);

        $category = Category::create([
            'name' => 'Laptops',
            'slug' => 'laptops',
            'is_active' => true,
        ]);

        Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Gaming Laptop',
            'slug' => 'gaming-laptop',
            'sku' => 'LAP-GAME-1',
            'price' => 75000,
            'stock' => 15,
            'status' => 'published',
        ]);

        // Products Endpoint
        $prodResponse = $this->getJson('/api/products');
        $prodResponse->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'price', 'seller']]]);

        // Categories Endpoint
        $catResponse = $this->getJson('/api/categories');
        $catResponse->assertStatus(200)
            ->assertJsonStructure(['success', 'categories' => [['id', 'name', 'slug']]]);

        // Sellers Endpoint
        $sellersResponse = $this->getJson('/api/sellers');
        $sellersResponse->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'store_name', 'city']]]);
    }
}
