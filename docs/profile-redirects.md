# Profile redirects

Pinkary shuts down on December 1, 2026. Before then, account owners can set one HTTPS destination for their profile URL. After the shutdown time, `https://pinkary.com/@username` permanently redirects (301) to that destination instead of rendering the profile.

The activation time is fixed in `App\Http\Controllers\UserController::SUNSET_AT`. There is no environment variable or deployment configuration for it.

## What redirects

- `GET /@{username}` only.
- Questions, links, QR codes, the API, and every other URL keep their existing behavior.
- Configured profiles redirect with a 301.
- Profiles without a destination return the sunset page with a `410 Gone` status.

The mapping lives on the user row (`users.redirect_url`), so it follows the current username when an account is renamed. Former usernames stop resolving, as before this feature. Deleted accounts are removed from the users table, so their mappings are removed too.

## User configuration

Users configure the destination in **Profile settings → Profile redirect**:

- Enter a URL beginning with `https://`.
- Leave the field empty to remove the redirect.
- The page previews the old Pinkary URL and its destination.

Destinations are limited to 2048 characters. HTTP, other schemes, embedded credentials, and URLs on `pinkary.com` or its subdomains are rejected. Rejecting Pinkary URLs prevents trivial redirect loops. The destination is public information once the redirect is active.

## Export the mappings

Run the export from the application environment that has access to the production database, with `APP_URL` set to `https://pinkary.com`:

```bash
APP_URL=https://pinkary.com php artisan profile-redirects:export
```

The default output is `storage/app/profile-redirects.csv`. Pass a path to write somewhere else:

```bash
php artisan profile-redirects:export /tmp/pinkary-profile-redirects.csv
```

The command refuses to run unless `APP_URL` uses HTTPS. Each line contains the profile URL, the destination, and the status code. The file has no header row, as required by Cloudflare Bulk Redirects:

```csv
https://pinkary.com/@alice,https://alice.dev,301
https://pinkary.com/@bob,https://github.com/bob,301
```

Only users with a configured destination are exported. The file contains no email addresses or other private user data.

## Deploy with Cloudflare Bulk Redirects

1. Run the export after the final round of user changes.
2. In the Cloudflare dashboard, open **Bulk Redirects** and create a Bulk Redirect List.
3. Import the CSV file into the list. Do not add a header row.
4. Create a Bulk Redirect Rule that references the list.
5. Test several mappings before the switchover:

   ```bash
   curl -I https://pinkary.com/@alice
   ```

   The response should be `301` with the destination in `Location`.

6. Activate the rule at the shutdown time. From then on, Cloudflare can serve the configured profile redirects without the Laravel application.

The export only contains profiles that configured a destination. Profiles without one need a catch-all sunset page, or the origin/static page must continue serving `/@*` requests. Cloudflare Bulk Redirects match exact source URLs and cannot match every `/@*` path with a single list entry.

The Bulk Redirect List and the `pinkary.com` DNS zone must remain active after the application infrastructure is removed. The DNS records must stay proxied through Cloudflare, and the list must fit within your plan's URL redirect quota. Otherwise the redirects stop working.

## Operational notes

- Permanent redirects are cached by browsers and intermediaries. Changing a destination after activation may not reach visitors immediately.
- A destination can point to a URL that later redirects back to Pinkary or somewhere unexpected. The application validates the destination once, but does not monitor it.
- Re-run the export and update the Cloudflare list if users change destinations before the shutdown. Remove the rule or list to roll back.
- This repository does not change any production infrastructure. Importing the list and activating the rule are deliberate deployment steps.
