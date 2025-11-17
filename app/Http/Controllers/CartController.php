<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\CartItem;
use App\Models\Item;

class CartController extends Controller
{
    // List all items in user's cart for a specific owner/bakery
    public function listCart(Request $request, $user_id, $owner_id)
    {
        $cartItems = CartItem::with('item')
            ->where('user_id', $user_id)
            ->where('owner_id', $owner_id)
            ->get();

        return response()->json(['data' => $cartItems], 200);
    }

    // Add item to cart
    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:app_users,id',
            'owner_id' => 'required|exists:app_owner_shops,shop_id',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = Item::findOrFail($request->item_id);

        // Ensure item belongs to this owner
        if ($item->owner_id != $request->owner_id) {
            return response()->json([
                'message' => 'This item does not belong to this owner.'
            ], 400);
        }

        // Check if item is already in the cart
        $cartItem = CartItem::where([
            'user_id' => $request->user_id,
            'owner_id' => $request->owner_id,
            'item_id' => $request->item_id
        ])->first();

        if ($cartItem) {
            // Increase quantity
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'user_id' => $request->user_id,
                'owner_id' => $request->owner_id,
                'item_id' => $request->item_id,
                'quantity' => $request->quantity,
                'price' => $item->price,
                'offer_price' => $item->offer_price
            ]);
        }

        return response()->json([
            'message' => 'Item added to cart',
            'data' => $cartItem
        ], 201);
    }

    // Update quantity
    public function updateCart(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cartItem = CartItem::findOrFail($id);
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'message' => 'Cart updated successfully',
            'data' => $cartItem
        ], 200);
    }

    // Delete item from cart
    public function removeFromCart($id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cartItem->delete();

        return response()->json(['message' => 'Item removed from cart'], 200);
    }

    // Clear cart for particular owner
    public function clearCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:app_users,id',
            'owner_id' => 'required|exists:app_owner_shops,shop_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        CartItem::where('user_id', $request->user_id)
            ->where('owner_id', $request->owner_id)
            ->delete();

        return response()->json(['message' => 'Cart cleared successfully'], 200);
    }
}