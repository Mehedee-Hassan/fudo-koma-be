We want to make a php (laravel) simple application to handle food cart mobile app.

We will build api for each feature
- backed will be saved in mysql
- mobile app is here: https://github.com/Mehedee-Hassan/fudo-koma
- current mobile app can do this:

```
Follo Cart Current Status
Updated: 2026-09-12

Currently working
Flutter web app runs in Chrome.
Explore opens on a map-first screen.
The map uses flutter_map with real tile data.
OpenStreetMap tiles are used when no Mapbox token is configured.
Mapbox streets can be enabled by adding a token in lib/config/mapbox_config.dart.
The app requests the user's location and centers the map when permission is granted.
A 5 km radius is drawn around the user's location.
The location button retries location detection.
A demo-area fallback is shown when location services or permission are unavailable.
Seven sample food carts appear as coordinate-based map markers.
Users can select carts and follow or unfollow them.
Followed carts appear in the Following tab.
Updates appear in the Updates tab.
Customer, Cart Owner, and Admin role previews are available.
Proximity checks use a 5 km threshold and a 10-minute check interval.
Firebase service seams and data models are present for future backend integration.
Flutter analysis passes and the test suite passes.
Current limitations
This is still a functional prototype, not a production release.

The cart data is local demo data and does not come from Firestore.
Follow state is temporary and is lost when the app closes.
The Mapbox token is still a placeholder by default.
OpenStreetMap public tiles are suitable for development preview only; production should use an approved tile provider.
Location permission and GPS behavior depend on the browser or device environment.
The 10-minute proximity logic is implemented as a tested service, but background scheduling and OS-level notifications are not live yet.
Firebase configuration files contain placeholders until a real Firebase project is connected.
There is no real authentication, registration, or persistent user account flow.
Customer, cart owner, and admin roles are UI previews only.
Cart owner updates, photo uploads, schedules, and live location publishing are not connected to a backend.
Push notifications and real Firestore writes are not enabled.
Admin moderation actions are demo interactions only.
iOS build testing requires macOS and Xcode.
Next development phase
Add a real Mapbox token and approved production tile configuration.
Connect Firestore for carts, users, follows, schedules, and updates.
Add Firebase Authentication and enforce user roles.
Connect cart-owner GPS publishing and live cart status.
Add background location scheduling and FCM push notifications for 5 km alerts.
Add photo uploads through Firebase Storage.
Replace role previews with protected customer, owner, and admin workflows.
Add production permissions, privacy messaging, error handling, and device testing.
```

Develop api so that I can support the mobile app.
We will change fire base to mysql

- mysql table
- user follow
- user id , catt owner id, follow flag, last updated
- nearby cart table:
- cart location, last updated at
- user nearby table:
- user location, last updated at,
- user configuration table
- check for user settings
- notify table : 
- push notification using firebase/push notification server
- authorization management


App devlopment
- create dashboard for all these db management ( with adming login)
- add api for potenial mobile app support
- add configurations to dashboard
- add photo uplaod facility 
- add 1-2 minutes schedule job to sent all new updates to all new users notification.

- add redis cache support if needed : 
- keep option to turn it on off as I might not be able to add a redis cache at first


suggest othe system design improvement in a doc folder with md file

- add tests for each api with fake data so that i can easily understand
- add commets to code
