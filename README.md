# CodeDiff

CodeDiff is a Laravel 12 application for private code and text comparison. The current release covers the Phase 1 MVP: guest and registered comparisons, usage limits, opt-in private history, email verification, lifetime access requests, and administrator review and grant management.

## Local setup

1. Install PHP and Composer dependencies with `composer install`.
2. Install JavaScript dependencies with `npm install`.
3. Configure the application database and mail transport in `.env`.
4. Run `php artisan migrate`.
5. Build frontend assets with `npm run build`.
6. Serve the application with `php artisan serve` or the project's Lando setup.

Email verification and password reset use Laravel's configured mail transport. In local development, configure a mail catcher or the `log` mailer so verification links can be opened.

## Administrator access

New users are regular users. Promote a verified account from an authorized shell with:

```sh
php artisan tinker --execute="App\\Models\\User::where('email', 'admin@example.com')->update(['is_admin' => true]);"
```

Administrators can open `/admin` to review lifetime requests, manage users and grants, and inspect the audit log. Grant and revoke actions require a reason and are recorded with the acting administrator and request IP.

## Initial limits

The first release uses the configurable starting values in `config/codediff.php` (override them with the matching `CODEDIFF_*` environment variables):

- Guests: 100,000 characters per version and 30 compare requests per minute.
- Free accounts: 50 comparisons per month, 50,000 characters per version, and 5 saved comparisons.
- Lifetime Pro: 500,000 characters per version, no monthly comparison cap, and no saved-comparison cap.
- All comparisons: 800 lines per version.

Unsaved source is processed for the comparison response but is not stored in the comparison table. Saved comparisons are private and are only created when the user opts in. The application never executes uploaded code and records comparison usage without recording source text.

## Current and later phases

The comparison workspace supports UTF-8 text/code uploads, side-by-side and unified views, whitespace/case options, line and changed-word highlighting, copy, swap, clear, and `.diff` download. Phase 2 and 3 items such as payment checkout, PDF/Word/spreadsheet extraction, image and folder comparison, private share links, and collaboration remain roadmap items.
