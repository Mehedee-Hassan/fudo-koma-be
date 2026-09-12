# Integrating /home/mhr/Documents/follo-cart

Inspected the local Flutter models and repository interfaces. The application has local and Firestore repository implementations; add an HTTP implementation and a backend mode in the Flutter project. This backend does not modify the mobile source. Keep Firebase Messaging for push; replace Firebase Authentication/Firestore/Storage with the API and photo URLs.

## Repository responsibilities

| Flutter interface | Backend integration |
|---|---|
| AuthRepository | Register/login, persist token securely, restore with GET /me, PATCH /me for name, GET/PATCH /me/settings for alerts, device registration; logout revokes token |
| CartRepository | GET /carts, GET /carts/{id}, GET /owner/carts; owner writes under /owner/carts; map moderation to admin API |
| FollowRepository | PUT/DELETE /carts/{id}/follow and GET /me/following; counts belong to the server |
| MediaRepository | Multipart photo upload, use returned public URL; DELETE the photo ID to remove |
| NotificationRepository | GET /me/notifications, PATCH read/read-all; POST owner updates; server creates follower inbox entries |
| ModerationRepository | POST /reports and protected /admin endpoints |

Replace Firestore streams with cancellable polling (e.g. 30 seconds foreground), refresh immediately after writes, and fetch every pagination page needed. Avoid overlapping polls. Stop account-bound streams and clear their data on logout. Do not let `push`, `pushAll`, or `adjustFollowerCount` write arbitrary recipient inboxes or counts: those operations are now server-owned. The app's owner update publisher should post one cart update; the server handles follower notifications.

Public registration always creates customers. The current Flutter role selector cannot grant owner/admin rights; expose owner onboarding with approval or have an administrator promote the account. The backend supports multiple carts per owner, while `ownedCartId`/`watchCartByOwner` currently assume one: select the first for the existing UI or add a cart switcher.

## Field mapping

Convert IDs with `.toString()`; do not pass numeric IDs into the existing `as String?` decoders. Convert numeric strings for optional schedule coordinates to doubles. All backend timestamps are UTC; parse then localize for display. Coordinates may be null when no GPS position was published; omit such carts from the map until they have a location.

| FoodCartModel | API |
|---|---|
| id / ownerId | id / owner_id, converted to string |
| category | cuisine, fallback Street food |
| locationLabel | location.address, fallback empty string |
| latitude / longitude | location.latitude / location.longitude |
| lastLocationAt | location.updated_at |
| photoRefs | photos.map(url) |
| scheduleEntries | schedules transformed below |
| isOpen | status == open |
| isFeatured | is_featured |
| isActive | moderation_status == approved; blocked-owner carts are omitted from public reads |
| followersCount | followers_count, default 0 for owner/detail responses without count |
| updatedAt | updated_at |

Schedules map `id` to string, `day_of_week` to weekday (already 1..7), `opens_at`/`closes_at` to minutes since midnight, `address` to locationLabel, `specific_date` to specificDate, `is_active` to isActive. Convert `HH:mm:ss` with hour*60 + minute. Honor the entry's timezone when interpreting serving hours; the current Flutter model needs a timezone field for travel across zones. One-off dates remain calendar dates, not UTC instants.

Users map `is_active` to the inverse of isBlocked, `created_at` to createdAt, settings.nearby_enabled to proximityAlertsEnabled. Token values are not user profile fields. `isGuest` remains client-only; guest browsing uses public endpoints. Moderation audit details such as blockedReason are not yet stored on user accounts.

Updates map `body` to message, `cart_id` to cartId, `created_at` to createdAt, `type` directly to CartUpdateType, and related cart data to cartName/ownerId/locationLabel. The single-cart update feed can use the cart already loaded by the screen.

Inbox notifications map `nearby` to NotificationType.proximity and `update` to NotificationType.announcement, `body` to message, `read_at != null` to isRead. Look up the cart name from the catalog or cart ID; distanceKm is optional. FCM data carries notification_id/cart_id/type. Read the inbox to reconcile notification state, and deduplicate pushes by notification_id.

Report creation accepts snake_case target_type/target_id/reason/note. The server derives reporter_id from authentication. Report list rows provide status/resolution_note/resolved_by/resolved_at; enrich display names from admin user/cart data. Never accept client-supplied administrator IDs as authorization.

## Device and location behavior

Use a fixed Flutter web port listed in CORS_ALLOWED_ORIGINS. Android emulator typically uses 10.0.2.2:8000; Docker currently binds to host loopback, so a real phone needs an explicitly configured LAN development endpoint or HTTPS tunnel. APP_URL must be reachable by the device for uploaded photo URLs.

Request permission in Flutter and publish coordinates only with consent. Identical positions still need periodic PUT calls to refresh their freshness timestamp. The backend cannot obtain GPS or schedule device background work. DELETE /me/location when location sharing is disabled. Server-side pruning removes user coordinates older than 24 hours.

On token refresh, register the new FCM token and delete the old device record. Before logout, delete the current device registration then revoke the API token. Disable the prototype's local proximity notification producer when server alerts are enabled to avoid duplicate notifications. Test denied permissions, token rotation, offline retries, app restart, and Android/iOS background delivery on devices.
