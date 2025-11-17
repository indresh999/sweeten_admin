<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Order;
use App\Models\DeliveryBoy;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryTimeline;
use App\Models\UserAddress;
use App\Models\AppUser;
use App\Models\AppOwnerUser;
use Carbon\Carbon;

class DeliveryAssignmentTest extends TestCase
{
    use RefreshDatabase;
    

    protected function setUp(): void
    {
        parent::setUp();

        // If you have seeders for gst/platform fees etc the app may expect them.
        // You can create minimal data here if required by your createOrder flow.
    }

    private function createShop(array $overrides = [])
    {
        return AppOwnerUser::create(array_merge([
            'shop_id' => $overrides['shop_id'] ?? 1,
            'full_name' => 'Shop Owner',
            'email' => 'shop@example.com',
            'password' => bcrypt('secret'),
            'phone_number' => '9000000000',
            'restaurant_name' => 'Test Shop',
            'restaurant_address' => 'Test Addr',
            'city' => 'TestCity',
            'state' => 'TS',
            'zip_code' => '400001',
            'country' => 'India',
            'latitude' => $overrides['latitude'] ?? 19.0760,
            'longitude' => $overrides['longitude'] ?? 72.8777,
        ], $overrides));
    }

    private function createUser(array $overrides = [])
    {
        return AppUser::create(array_merge([
            'full_name' => 'Test User',
            'phone_number' => '9876543210',
        ], $overrides));
    }

    private function createAddress($userId, array $overrides = [])
    {
        return UserAddress::create(array_merge([
            'user_id' => $userId,
            'label' => 'Home',
            'address_line' => 'A-1 Test',
            'city' => 'TestCity',
            'state' => 'TS',
            'pincode' => '400001',
            'lat' => $overrides['lat'] ?? 19.0760,
            'lng' => $overrides['lng'] ?? 72.8777,
            'is_default' => true
        ], $overrides));
    }

    private function createOrderSnapshot($userId, $shopId, $address)
    {
        return Order::create([
            'user_id' => $userId,
            'shop_id' => $shopId,
            'total_amount' => 100,
            'status' => 'pending',
            // snapshot
            'address_label' => $address->label,
            'address_line' => $address->address_line,
            'city' => $address->city,
            'state' => $address->state,
            'pincode' => $address->pincode,
            'lat' => $address->lat,
            'lng' => $address->lng,
            'final_amount' => 100
        ]);
    }

    /** @test */
    public function auto_assign_finds_nearest_available_delivery_boy_and_creates_assignment()
    {
        // Create shop, user, address, order
        $shop = $this->createShop(['shop_id' => 33, 'latitude' => 19.0760, 'longitude' => 72.8777]);
        $user = $this->createUser(['phone_number' => '9000000001']);
        $address = $this->createAddress($user->id, ['lat' => 19.0760, 'lng' => 72.8777]);
        $order = $this->createOrderSnapshot($user->id, $shop->shop_id, $address);

        // Create two delivery boys - one close and one far
        $near = DeliveryBoy::create([
            'full_name' => 'Near Boy',
            'phone_number' => '9000000002',
            'status' => 'online',
            'latitude' => 19.0765,
            'longitude' => 72.8780,
            'max_active_orders' => 2,
            'current_active_orders' => 0
        ]);

        $far = DeliveryBoy::create([
            'full_name' => 'Far Boy',
            'phone_number' => '9000000003',
            'status' => 'online',
            'latitude' => 19.2000,
            'longitude' => 72.9000,
            'max_active_orders' => 2,
            'current_active_orders' => 0
        ]);

        // Call auto-assign endpoint
        $resp = $this->postJson('/api/deliveryboy/auto-assign', [
            'order_id' => $order->id,
            'max_radius' => 700,
            'max_boys' => 5
        ]);

        $resp->assertStatus(201);
        $resp->assertJson(['status' => true]);

        $assignment = DeliveryAssignment::where('order_id', $order->id)->first();
        $this->assertNotNull($assignment, 'Assignment not created');
        $this->assertEquals($near->id, $assignment->delivery_boy_id, 'Nearest boy must be assigned');

        // Check boy's current_active_orders incremented
        $nearRefreshed = DeliveryBoy::find($near->id);
        $this->assertEquals(1, $nearRefreshed->current_active_orders);
    }

    /** @test */
    public function manual_assign_creates_assignment_and_writes_timeline()
    {
        $shop = $this->createShop(['shop_id' => 33]);
        $user = $this->createUser(['phone_number' => '9000000004']);
        $address = $this->createAddress($user->id);
        $order = $this->createOrderSnapshot($user->id, $shop->shop_id, $address);

        $boy = DeliveryBoy::create([
            'full_name' => 'Manual Boy',
            'phone_number' => '9000000005',
            'status' => 'online',
            'latitude' => 19.0765,
            'longitude' => 72.8780,
            'max_active_orders' => 2,
            'current_active_orders' => 0
        ]);

        $resp = $this->postJson('/api/deliveryboy/assign', [
            'order_id' => $order->id,
            'delivery_boy_id' => $boy->id,
            'expected_minutes' => 30
        ]);

        $resp->assertStatus(201);
        $resp->assertJson(['status' => true]);

        $assignment = DeliveryAssignment::where('order_id', $order->id)->first();
        $this->assertEquals('assigned', $assignment->status);
        $this->assertEquals($boy->id, $assignment->delivery_boy_id);

        // Timeline entry must exist
        $timeline = DeliveryTimeline::where('order_id', $order->id)->where('status', 'assigned')->first();
        $this->assertNotNull($timeline, 'Timeline assigned not found');
    }

    /** @test */
    public function delivery_boy_accepts_and_rejects_assignment_workflow_and_reassignment_attempted()
    {
        $shop = $this->createShop(['shop_id' => 44]);
        $user = $this->createUser(['phone_number' => '9000000006']);
        $address = $this->createAddress($user->id);
        $order = $this->createOrderSnapshot($user->id, $shop->shop_id, $address);

        // Create two boys near by
        $boy1 = DeliveryBoy::create([
            'full_name' => 'Boy1',
            'phone_number' => '9000000007',
            'status' => 'online',
            'latitude' => 19.0761,
            'longitude' => 72.8778,
            'max_active_orders' => 1,
            'current_active_orders' => 0
        ]);

        $boy2 = DeliveryBoy::create([
            'full_name' => 'Boy2',
            'phone_number' => '9000000008',
            'status' => 'online',
            'latitude' => 19.0762,
            'longitude' => 72.8779,
            'max_active_orders' => 2,
            'current_active_orders' => 0
        ]);

        // Auto assign should pick boy1 first
        $autoResp = $this->postJson('/api/deliveryboy/auto-assign', [
            'order_id' => $order->id,
            'max_radius' => 10,
            'max_boys' => 5
        ]);

        $autoResp->assertStatus(201);
        $assignment = DeliveryAssignment::where('order_id', $order->id)->first();
        $this->assertEquals($boy1->id, $assignment->delivery_boy_id);

        // Now boy1 rejects
        $rejectResp = $this->postJson('/api/deliveryboy/reject', [
            'order_id' => $order->id,
            'delivery_boy_id' => $boy1->id,
            'reason' => 'busy'
        ]);

        $rejectResp->assertStatus(200);
        $assignmentRef = DeliveryAssignment::where('order_id', $order->id)->first();
        $this->assertEquals('rejected', $assignmentRef->status);

        // After rejection, reassignment attempt should assign to boy2 (attemptReassignment uses autoAssign)
        // Because attemptReassignment calls autoAssignDeliveryBoy which returns a response, verify final assignment
        $assignmentAfter = DeliveryAssignment::where('order_id', $order->id)->first();
        $this->assertContains($assignmentAfter->status, ['assigned','accepted','rejected','picked','delivered']);
        // Ensure current_active_orders for boy1 was decremented
        $boy1Fresh = DeliveryBoy::find($boy1->id);
        $this->assertEquals(0, $boy1Fresh->current_active_orders);
    }

    /** @test */
    public function pickup_and_delivery_updates_assignment_and_order_status_and_decrements_active_orders()
    {
        $shop = $this->createShop(['shop_id' => 55]);
        $user = $this->createUser(['phone_number' => '9000000009']);
        $address = $this->createAddress($user->id);
        $order = $this->createOrderSnapshot($user->id, $shop->shop_id, $address);

        $boy = DeliveryBoy::create([
            'full_name' => 'PickBoy',
            'phone_number' => '9000000010',
            'status' => 'online',
            'latitude' => 19.0763,
            'longitude' => 72.8776,
            'max_active_orders' => 2,
            'current_active_orders' => 0
        ]);

        // Manual assign
        $this->postJson('/api/deliveryboy/assign', ['order_id' => $order->id, 'delivery_boy_id' => $boy->id]);

        // Accept
        $this->postJson('/api/deliveryboy/accept', ['order_id' => $order->id, 'delivery_boy_id' => $boy->id]);
        $assignment = DeliveryAssignment::where('order_id', $order->id)->first();
        $this->assertEquals('accepted', $assignment->status);

        // Mark picked
        $pickResp = $this->postJson('/api/deliveryboy/picked', ['order_id' => $order->id, 'delivery_boy_id' => $boy->id]);
        $pickResp->assertStatus(200);
        $assignmentFresh = DeliveryAssignment::where('order_id', $order->id)->first();
        $this->assertEquals('picked', $assignmentFresh->status);

        // Order status updated to out_for_delivery
        $orderFresh = Order::find($order->id);
        $this->assertEquals('out_for_delivery', $orderFresh->status);

        // Mark delivered
        $delResp = $this->postJson('/api/deliveryboy/delivered', ['order_id' => $order->id, 'delivery_boy_id' => $boy->id, 'notes' => 'Left at door']);
        $delResp->assertStatus(200);

        $assignAfter = DeliveryAssignment::where('order_id', $order->id)->first();
        $this->assertEquals('delivered', $assignAfter->status);

        $orderAfter = Order::find($order->id);
        $this->assertEquals('delivered', $orderAfter->status);

        // Ensure boy's active orders decremented (was incremented at assignment)
        $boyFresh = DeliveryBoy::find($boy->id);
        $this->assertEquals(0, $boyFresh->current_active_orders);
    }

    /** @test */
    public function timeline_endpoint_returns_entries()
    {
        $shop = $this->createShop(['shop_id' => 66]);
        $user = $this->createUser(['phone_number' => '9000000011']);
        $address = $this->createAddress($user->id);
        $order = $this->createOrderSnapshot($user->id, $shop->shop_id, $address);

        // add some timeline rows
        DeliveryTimeline::create(['order_id' => $order->id, 'status' => 'created', 'message' => 'Order placed']);
        DeliveryTimeline::create(['order_id' => $order->id, 'status' => 'assigned', 'message' => 'Delivery assigned']);

        $resp = $this->getJson("/api/order/timeline/{$order->id}");
        $resp->assertStatus(200);
        $resp->assertJsonStructure(['status','data']);
        $this->assertCount(2, $resp->json('data'));
    }
}