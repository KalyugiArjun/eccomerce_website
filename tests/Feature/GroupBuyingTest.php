<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Services\GroupBuyingService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupBuyingTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_buying_lifecycle_creation_and_completion(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Deals Store',
            'slug' => 'deals-store',
            'phone' => '9876543210',
            'business_address' => 'Delhi',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'status' => 'approved',
        ]);

        $category = Category::create(['name' => 'Fashion', 'slug' => 'fashion', 'is_active' => true]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Cotton Kurta Set',
            'slug' => 'cotton-kurta-set',
            'sku' => 'KURTA-01',
            'price' => 1000,
            'stock' => 50,
            'status' => 'published',
        ]);

        $user1 = User::factory()->create(['role' => 'customer']);
        $user2 = User::factory()->create(['role' => 'customer']);

        $service = app(GroupBuyingService::class);

        // 1. User 1 creates a group buying campaign with target 2 buyers
        $group = $service->createGroup($user1, $product, 2);

        $this->assertNotNull($group);
        $this->assertEquals(1, $group->current_buyers);
        $this->assertEquals(2, $group->target_buyers);
        $this->assertEquals('active', $group->status);
        $this->assertEquals(900.00, $group->discounted_price); // 10% discount tier

        // 2. User 1 attempts to join again -> throws duplicate exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("You have already joined this group buying deal!");
        $service->joinGroup($user1, $group->group_code);
    }

    public function test_second_user_joining_completes_the_group_campaign(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Deals Store',
            'slug' => 'deals-store',
            'phone' => '9876543210',
            'business_address' => 'Delhi',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'status' => 'approved',
        ]);

        $category = Category::create(['name' => 'Fashion', 'slug' => 'fashion', 'is_active' => true]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Cotton Kurta Set',
            'slug' => 'cotton-kurta-set',
            'sku' => 'KURTA-01',
            'price' => 1000,
            'stock' => 50,
            'status' => 'published',
        ]);

        $user1 = User::factory()->create(['role' => 'customer']);
        $user2 = User::factory()->create(['role' => 'customer']);

        $service = app(GroupBuyingService::class);

        $group = $service->createGroup($user1, $product, 2);

        // User 2 joins
        $updatedGroup = $service->joinGroup($user2, $group->group_code);

        $this->assertEquals(2, $updatedGroup->current_buyers);
        $this->assertEquals('completed', $updatedGroup->status);
    }
}
