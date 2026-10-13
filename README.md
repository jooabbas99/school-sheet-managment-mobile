# School Sheet Management: Mobile (Android)

Arabic, right-to-left Android app for school staff, built with Laravel 13 + [NativePHP Mobile](https://nativephp.com/docs/mobile) + Livewire 4. It stores no school data itself: everything goes through the backend REST API ([school-sheet-managment](https://github.com/jooabbas99/school-sheet-managment)).

Only free NativePHP plugins are used (network, share). The login token is kept in an encrypted file in the app's private storage.

## Setup

Requirements: PHP 8.3+, Composer, Node ≥ 20.19 (see `.nvmrc`), Android Studio with SDK Platform 36, Build-Tools and Platform-Tools.

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
# API_BASE_URL in .env: emulator → http://10.0.2.2:8000/api/v1, phone → http://<your-computer-LAN-IP>:8000/api/v1
npm run build
php artisan native:install      # first time, or after upgrading nativephp (--force)
php artisan native:run          # build and launch on an emulator or phone
```

You can also run it in a browser while developing: `php artisan serve --port=8001`.

## How it is organised

- `app/Services/Api/ApiClient.php`: the only HTTP client; turns API errors into `ApiException` (Arabic message + field errors), `UnauthenticatedException` (back to login) and `OfflineException` (no connection screen).
- `app/Services/Auth/*TokenStore.php`: where the bearer token is kept.
- `app/Support/Navigation.php`: sections shown according to the user's permissions (the server enforces them anyway).
- `app/Livewire/`: screens. All texts are in `lang/ar/`.

## Tests

```bash
php artisan test                                   # Pest, with the API faked
./vendor/bin/pint
./vendor/bin/phpstan analyse --memory-limit=1G
```
