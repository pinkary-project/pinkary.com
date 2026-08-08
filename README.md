<p align="center">
    <img src="https://pinkary.com/img/logo.svg" width="600" alt="Illustration of Pinkary logo. The logo is composed of stylized white text spelling out 'Pinkary' with a pink dot at the end.">
</p>

------

**Welcome to Pinkary!** Pinkary is a landing page for all your links and a place to connect with like-minded individuals without the noise.

Initially, it was created to help people share their links in a more organized way. In just 15 hours, we went from `composer create-project` to production, and after 24 hours, we reached over 1,000 users.

The source code still shows some signs of the rush; that's why we think **it's important to share it with you—so you can see how we've built it**, combining fast pace given the circumstances with the quality we always aim for.

Over time, we've managed to add more features, such as feed, explore, questions, likes, and more. We've also improved the design, added tests, and improved the overall quality of the code. There is still a lot to do, but most importantly, there is a huge opportunity to make this a **community-driven project**.

- Wish to contribute? Here is how:

## Installation

Pinkary is a regular Laravel application built on Laravel 13 and uses Livewire v4 / Tailwind CSS v4 for the frontend. If you are familiar with Laravel, you should feel right at home.

In terms of local development, you can use the following requirements:

- PHP 8.4 - with MySQL, GD, and other common extensions.
- Node.js 22 or more recent.

If you have these requirements, you can start by cloning the repository and installing the dependencies:

```bash
git clone https://github.com/pinkary-project/pinkary.com.git

cd pinkary.com

git checkout -b feat/your-feature # or fix/your-fix
```

> **Don't push directly to the `main` branch**. Instead, create a new branch and push it to your branch.

Next, install the dependencies using [Composer](https://getcomposer.org) and [NPM](https://www.npmjs.com):

```bash
composer install

npm install
```

After that, set up your `.env` file:

```bash
cp .env.example .env

php artisan key:generate
```

Prepare your MySQL database connection and run the migrations:

```bash
php artisan migrate
```

Link the storage to the public folder:

```bash
php artisan storage:link
```

In a **separate terminal**, build the assets in watch mode:

```bash
npm run dev
```

Also in a **separate terminal**, run the queue worker:

```bash
php artisan queue:work
```

Finally, start the development server:

```bash
php artisan serve
```

> Note: By default, emails are sent to the `log` driver. You can change this in the `.env` file to something like `mailtrap`.

Once you are done with the code changes, be sure to run the test suite to ensure everything is still working:

```bash
composer test
```

If everything is green, push your branch and create a pull request:

```bash
git commit -am "Your commit message"

git push
```

Visit [github.com/pinkary-project/pinkary.com/pulls](https://github.com/pinkary-project/pinkary.com/pulls) and create a pull request.

## Tooling

Pinkary uses a few tools to ensure the code quality and consistency. Of course, [Pest](https://pestphp.com) is the testing framework of choice, and we also use [PHPStan](https://phpstan.org) for static analysis.  Pest's type coverage is at 100%, and the test suite is also at 100% coverage.

In terms of code style, we use [Laravel Pint](https://laravel.com/docs/11.x/pint) to ensure the code is consistent and follows the Laravel conventions. We also use [Rector](https://getrector.org) to ensure the code is up to date with the latest PHP version.

You run these tools individually using the following commands:

```bash
# Lint the code using Pint
composer lint
composer test:lint

# Refactor the code using Rector
composer refactor
composer test:refactor

# Run PHPStan
composer test:types

# Run the test suite
composer test:unit

# Run all the tools
composer test
```

Pull requests that don't pass the test suite will not be merged. So, as suggested on the [Installation](#installation) section, be sure to run the test suite before pushing your branch.

## Production

Pinkary is Hosted on Laravel Cloud with MySQL as the primary database and S3-compatible object storage for uploaded files.

The only service we use is [Mailcoach](https://mailcoach.app) to manage the send emails. Besides that, MySQL is used as database driver, sessions driver, queue driver, cache driver, etc.

---

Pinkary is an open-sourced software licensed under the **[GNU Affero General Public License](LICENSE.md)**

