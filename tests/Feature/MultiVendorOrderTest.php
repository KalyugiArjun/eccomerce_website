<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiVendorOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_multi_vendor_cart_splits_into_separate_seller_sub_orders(): void
    {
        // 1. Create 2 distinct Sellers
        $userA = User::factory()->create(['role' => 'seller']);
        $sellerA = Seller::create([
            'user_id' => $userA->id,
            'store_name' => 'Seller Store A',
            'slug' => 'seller-store-a',
            'phone' => '9876543211',
            'business_email' => 'sellerA@shopkart.in',
            'business_address' => 'Shop 1, Connaught Place',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'status' => 'approved',
            'commission_rate' => 10.0,
            'trust_score' => 95,
        ]);

        $userB = User::factory()->create(['role' => 'seller']);
        $sellerB = Seller::create([
            'user_id' => $userB->id,
            'store_name' => 'Seller Store B',
            'slug' => 'seller-store-b',
            'phone' => '9876543212',
            'business_email' => 'sellerB@shopkart.in',
            'business_address' => 'Shop 2, Hazratganj',
            'city' => 'Lucknow',
            'state' => 'Uttar Pradesh',
            'pincode' => '226001',
            'status' => 'approved',
            'commission_rate' => 15.0,
            'trust_score' => 90,
        ]);

        // 2. Create Category & Products
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'commission_rate' => 10.0,
            'is_active' => true,
        ]);

        $brand = Brand::create(['name' => 'TechBrand', 'slug' => 'techbrand']);

        $productA = Product::create([
            'seller_id' => $sellerA->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Product from Seller A',
            'slug' => 'product-from-seller-a',
            'sku' => 'SKU-A-01',
            'price' => 1000.00,
            'stock' => 50,
            'status' => 'published',
        ]);

        $productB = Product::create([
            'seller_id' => $sellerB->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Product from Seller B',
            'slug' => 'product-from-seller-b',
            'sku' => 'SKU-B-01',
            'price' => 2000.00,
            'stock' => 30,
            'status' => 'published',
        ]);

        // 3. Customer & Address
        $customer = User::factory()->create(['role' => 'customer']);
        $address = Address::create([
            'user_id' => $customer->id,
            'name' => 'Rohan Gupta',
            'phone' => '9988776655',
            'house_no' => 'Flat 402',
            'street' => 'Royal Residency',
            'area' => 'Connaught Place',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'is_default' => true,
        ]);

        // 4. Build Cart with items from both sellers
        $cart = Cart::create(['user_id' => $customer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $productA->id,
            'seller_id' => $sellerA->id,
            'quantity' => 2,
            'price' => 1000.00,
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $productB->id,
            'seller_id' => $sellerB->id,
            'quantity' => 1,
            'price' => 2000.00,
        ]);

        // 5. Checkout via OrderService
        $orderService = app(OrderService::class);
        $order = $orderService->createOrder($customer, [
            'customer_name' => 'Rohan Gupta',
            'customer_email' => 'rohan@example.com',
            'customer_phone' => '9988776655',
            'shipping_address' => 'Flat 402, Royal Residency, Connaught Place, Delhi - 110001',
            'payment_method' => 'cod',
            'notes' => 'Please deliver in evening',
        ], $cart);

        // 6. Assertions
        $this->assertNotNull($order);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
        $this->assertEquals(4000.00, $order->subtotal);

        // Assert 2 distinct sub-orders were generated
        $subOrders = SellerOrder::where('order_id', $order->id)->get();
        $this->assertCount(2, $subOrders);

        $subOrderA = $subOrders->firstWhere('seller_id', $sellerA->id);
        $subOrderB = $subOrders->firstWhere('seller_id', $sellerB->id);

        $this->assertNotNull($subOrderA);
        $this->assertNotNull($subOrderB);
        $this->assertEquals(2000.00, $subOrderA->subtotal);
        $this->assertEquals(2000.00, $subOrderB->subtotal);

        // Check platform commission deduction for Seller A (10% = 200) and Seller B (15% = 300)
        $this->assertEquals(200.00, $subOrderA->platform_commission_amount);
        $this->assertEquals(300.00, $subOrderB->platform_commission_amount);

        // Check Inventory decrement
        $this->assertEquals(48, $productA->fresh()->stock);
        $this->assertEquals(29, $productB->fresh()->stock);
    }
}
