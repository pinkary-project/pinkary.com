# The intention of this project

Pinkary is a social website at `pinkary.com`: a person puts each link on one landing page, asks and answers questions, and reads a feed of posts from people with the same interests. It is a Laravel application with Livewire and Tailwind CSS, and the community contributes to it on GitHub. It is not a package.

Install the dependencies and prepare the application with `composer setup`. Run the tests with `composer test`, which runs Rector, Pint, PHPStan, the type coverage and the Pest suite. Start the program with `php artisan serve`, `php artisan queue:work` and `npm run dev`, each in its own terminal.

- `app/` — the application: actions, Livewire components, models, queries, services, policies and the Filament admin panel
- `bootstrap/` — the boot of the application and the list of service providers
- `config/` — the configuration files that differ from the defaults of Laravel
- `database/` — the migrations, the factories and the seeders
- `public/` — the web root: the images, the fonts, the manifest and the built assets
- `resources/` — the Blade views, the CSS and the JavaScript
- `routes/` — the web, authentication, API and console routes
- `storage/` — the logs, the cache and the uploaded files
- `tests/` — the Pest tests: architecture, feature, HTTP, console and unit
