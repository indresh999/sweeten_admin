# SWEETAN API — ZEPTO/BLINKIT STYLE
## Complete API Reference

Base URL: `https://your-domain.com/api`

---

## 1. HOME & DISCOVERY

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/home?lat=&lng=&radius=` | All home data: banners, categories, deals, shops, featured items |
| GET | `/nearby-shops?latitude=&longitude=&radius=` | Nearby shops with open/closed status |
| GET | `/shop/{id}` | Shop details + rating + schedule |
| GET | `/shop/{id}/schedule` | Shop open/close hours |
| GET | `/shop/{shopId}/reviews` | Shop reviews |

---

## 2. SEARCH

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/search?q=&lat=&lng=&user_id=` | Search shops + items |
| GET | `/search/suggestions?q=&lat=&lng=&user_id=` | Autocomplete suggestions |
| DELETE | `/search-history?user_id=` | Clear search history |

---

## 3. CATEGORIES & ITEMS

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/categories` | All active categories |
| GET | `/subcategories?category_id=` | Subcategories (filter by category) |
| GET | `/home-filters` | App home filter chips |
| GET | `/items/by-subcategory?subcategory_id=&shop_id=` | Items in subcategory |
| GET | `/items/similar?item_id=` | Similar items |
| GET | `/items/by-shop/{id}` | Items by shop |
| GET | `/items/{id}` | Item detail with variants, commission pricing |

---

## 4. BANNERS & ADS

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/banners?type=hero` | Active banners (type: hero/strip/popup/deals/category) |
| POST | `/banners/{id}/click` | Track banner click |

---

## 5. DEALS / FLASH SALES

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/deals` | Active deals with countdown timer |
| GET | `/deals/{id}` | Single deal with items |

---

## 6. USER AUTH

| Method | Endpoint | Body | Description |
|--------|----------|------|-------------|
| POST | `/auth/send-otp` | `{email}` | Send OTP |
| POST | `/auth/verify-otp` | `{email, otp, fcm_token?, referral_code?}` | Verify OTP + auto referral |
| POST | `/auth/logout` | — | Logout |
| POST | `/auth/update-profile` | `{user_id, full_name?, phone_number?, dob?, gender?, picture?}` | Update profile |
| GET | `/auth/profile?user_id=` | — | Get profile + wallet |
| DELETE | `/auth/delete-account` | `{user_id}` | Soft delete account |

---

## 7. ADDRESSES

| Method | Endpoint | Body | Description |
|--------|----------|------|-------------|
| GET | `/addresses/{user_id}` | — | List user addresses |
| POST | `/addresses` | `{user_id, label?, address_line, city, state, pincode, lat?, lng?, is_default?}` | Add address |
| PUT | `/addresses/{id}` | partial fields | Update address |
| DELETE | `/addresses/{id}` | — | Delete address |
| POST | `/addresses/{id}/set-default` | — | Set as default |

---

## 8. CART

| Method | Endpoint | Body | Description |
|--------|----------|------|-------------|
| GET | `/cart/{user_id}` | — | Cart with totals, GST, delivery charge |
| POST | `/cart/add` | `{user_id, owner_id, item_id, variant_id?, quantity}` | Add item (blocks multi-shop) |
| PUT | `/cart/{id}` | `{user_id, quantity}` | Update quantity |
| DELETE | `/cart/{id}?user_id=` | — | Remove item |
| POST | `/cart/clear` | `{user_id}` | Clear full cart |
| POST | `/cart/apply-coupon` | `{user_id, coupon_code, order_amount}` | Validate + apply coupon |
| GET | `/cart/coupons/available?user_id=&order_amount=` | — | List applicable coupons |

---

## 9. ORDERS

| Method | Endpoint | Body | Description |
|--------|----------|------|-------------|
| POST | `/orders` | `{user_id, shop_id, address_id, payment_method, coupon_code?, wallet_use?, special_instructions?}` | Create order |
| GET | `/orders?user_id=&status=&from_date=&to_date=&per_page=` | — | List user orders |
| GET | `/orders/{id}` | — | Order detail |
| POST | `/orders/{id}/cancel` | `{cancel_reason_id, cancel_remark?}` | Cancel order |
| POST | `/orders/{id}/rate` | `{user_id, shop_rating, delivery_rating?, comment?}` | Rate order |
| GET | `/orders/{id}/timeline` | — | Amazon-style order timeline |
| GET | `/orders/{id}/tracking` | — | Tracking info (no live GPS) |
| GET | `/orders/{id}/status` | — | Quick status check |
| POST | `/orders/{id}/reorder` | `{user_id}` | Reorder previous order |

---

## 10. WALLET

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/wallet/balance?user_id=` | Wallet balance |
| GET | `/wallet/transactions?user_id=&per_page=` | Transaction history |

---

## 11. WISHLIST

| Method | Endpoint | Body | Description |
|--------|----------|------|-------------|
| GET | `/wishlist/{user_id}` | — | Get wishlist |
| POST | `/wishlist` | `{user_id, item_id}` | Add to wishlist |
| DELETE | `/wishlist/remove` | `{user_id, item_id}` | Remove from wishlist |

---

## 12. NOTIFICATIONS

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notifications?user_id=&per_page=` | Get notifications |
| POST | `/notifications/{id}/read` | Mark as read |
| POST | `/notifications/read-all` | Mark all read |

---

## 13. COUPONS (Admin)

| Method | Endpoint | Body | Description |
|--------|----------|------|-------------|
| GET | `/admin/coupons` | — | List all coupons |
| POST | `/admin/coupons` | `{code, title, discount_type, discount_value, min_order_amount?, max_discount_amount?, usage_limit?, usage_per_user?, valid_from, valid_until}` | Create coupon |
| PUT | `/admin/coupons/{id}` | partial fields | Update coupon |
| DELETE | `/admin/coupons/{id}` | — | Delete coupon |
| POST | `/coupons/validate` | `{user_id, coupon_code, order_amount, shop_id?}` | Validate coupon |

---

## 14. DEALS (Admin)

| Method | Endpoint | Body | Description |
|--------|----------|------|-------------|
| POST | `/admin/deals` | `{title, deal_type, discount_type, discount_value, starts_at, ends_at, items:[{item_id, deal_price?, deal_discount_percent?, stock_limit?}]}` | Create deal |
| PUT | `/admin/deals/{id}` | partial fields | Update deal |
| DELETE | `/admin/deals/{id}` | — | Delete deal |

---

## 15. BANNERS (Admin)

| Method | Endpoint | Body | Description |
|--------|----------|------|-------------|
| POST | `/admin/banners` | `{image_url, banner_type, target_type?, target_id?, sort_order?}` | Create banner |
| PUT | `/admin/banners/{id}` | partial fields | Update banner |
| DELETE | `/admin/banners/{id}` | — | Delete banner |

---

## 16. SHOP OWNER

| Method | Endpoint | Body | Description |
|--------|----------|------|-------------|
| POST | `/shop/register` | `{full_name, email, password, restaurant_name, ...}` | Register shop |
| POST | `/shop/login` | `{email, password}` | Shop login |
| PUT | `/shop/{id}` | partial fields | Update shop |
| POST | `/shop/{id}/toggle-status` | — | Toggle active/inactive |
| GET | `/shop/{id}/dashboard` | — | Dashboard stats |
| PUT | `/shop/{id}/schedule` | `{schedules: [{day_of_week 0-6, open_time, close_time, is_closed}]}` | Set 7-day schedule |
| GET | `/shop/{id}/orders?status=&per_page=` | — | Shop orders |

### Shop Images
| POST | `/shop/images/upload` | `{shop_id, image file, tag?}` | Upload image |
| DELETE | `/shop/images/{id}` | — | Delete image |
| GET | `/shop/{id}/images` | — | List images |

---

## 17. ITEMS (Shop Owner)

| Method | Endpoint | Body | Description |
|--------|----------|------|-------------|
| POST | `/items` | `{shop_id, category_id, item_name, is_veg, variants:[{label, price, gst_percent}], images[]}` | Add item |
| PUT | `/items/{id}` | partial + variants | Update item |
| DELETE | `/items/{id}` | — | Delete item |
| POST | `/items/{id}/toggle-status` | — | Toggle active/inactive |

---

## 18. DELIVERY BOY

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/delivery/register` | Register |
| POST | `/delivery/login` | Login (returns sanctum token) |
| POST | `/delivery/logout` | Logout (requires token) |
| GET | `/delivery/me` | Profile + docs (requires token) |
| PUT | `/delivery/me` | Update profile |
| POST | `/delivery/location` | Update GPS location |
| POST | `/delivery/availability` | Toggle online/offline |
| POST | `/delivery/documents` | Upload document (aadhar/pan/license etc.) |
| GET | `/delivery/documents` | List documents |
| GET | `/delivery/earnings?period=today|week|month` | Earning report |
| GET | `/delivery/orders?status=` | My assigned orders |

---

## 19. DELIVERY OPERATIONS (Admin/System)

| Method | Endpoint | Body | Description |
|--------|----------|------|-------------|
| POST | `/delivery-ops/auto-assign` | `{order_id}` | Auto assign nearest delivery boy |
| POST | `/delivery-ops/manual-assign` | `{order_id, delivery_boy_id, expected_minutes?}` | Manual assign |
| POST | `/delivery-ops/accept` | `{order_id, delivery_boy_id}` | DB accepts order |
| POST | `/delivery-ops/reject` | `{order_id, delivery_boy_id, reason?}` | Reject + auto-reassign |
| POST | `/delivery-ops/picked` | `{order_id, delivery_boy_id}` | Mark picked up |
| POST | `/delivery-ops/delivered` | `{order_id, delivery_boy_id, notes?}` | Mark delivered + auto earn |
| GET | `/delivery-ops/timeline/{orderId}` | — | Order timeline |

---

## ORDER STATUS FLOW

```
placed → confirmed → out_for_delivery → delivered
              ↓
          cancelled (only from pending/confirmed)
```

## PAYMENT METHODS
- `cod` — Cash on Delivery
- `online` — Online (Razorpay/Stripe integration at frontend)
- `wallet` — Sweetan Wallet

## WALLET EVENTS
- **Sign up with referral code** → referee gets ₹30, referrer gets ₹50
- **Order cancellation** → wallet amount refunded automatically
- **Order payment** → debited from wallet if wallet_use=true

