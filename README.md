<p align="center">
    <img src="https://pinkary.com/img/logo.svg" width="600" alt="Illustration of Pinkary logo. The logo is composed of stylized white text spelling out 'Pinkary' with a pink dot at the end.">
</p>

------
# Welcome to Pinkary!
> Telegram group: **[pinkary.com/telegram »](https://pinkary.com/telegram)**.

Pinkary is a landing page for all your links and a place to connect with like-minded individuals without the noise.

Initially, it was created to help people share their links in a more organized way. In just 15 hours, we went from `composer create-project` to production, and after 24 hours, we reached over 1,000 users.

The source code still shows some signs of the rush; that's why we think **it's important to share it with you—so you can see how we've built it**, combining fast pace given the circumstances with the quality we always aim for.

Over time, we've managed to add more features, such as feed, explore, questions, likes, and more. We've also improved the design, added tests, and improved the overall quality of the code. There is still a lot to do, but most importantly, there is a huge opportunity to make this a **community-driven project**.

- Telegram group: **[pinkary.com/telegram »](https://pinkary.com/telegram)**
- Follow us on X at **[@PinkaryProject »](https://x.com/PinkaryProject)**

---

## Table of Contents

1. [Welcome to Pinkary](#welcome-to-pinkary)
2. [Installation](#installation)
   - [Requirements](#requirements)
   - [Steps to Install](#steps-to-install)
3. [Contributing](#contributing)
   - [Step-by-step Contribution Guide](#step-by-step-contribution-guide)
4. [Tooling](#tooling)
   - [Running the Tools](#running-the-tools)
5. [Production](#production)

---

## Installation

Pinkary is a Laravel application built on Laravel 11 and uses Livewire / Tailwind CSS for the frontend. If you are familiar with Laravel, you should feel right at home.

### Requirements
Make sure you have the following installed before starting:

- PHP 8.3 - with SQLite, GD, and other common extensions.
- Node.js 16 or more recent.

Once the requirements are met, follow these steps to set up the project locally:

1. **Clone the repository**:
   ```bash
   git clone https://github.com/pinkary-project/pinkary.com.git
   cd pinkary.com
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Set up the `.env` file**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Prepare your database**:
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

5. **Link the storage**:
   ```bash
   php artisan storage:link
   ```

6. **Build assets and run the queue worker**:
   Open a separate terminal and run:
   ```bash
   npm run dev
   php artisan queue:work
   ```

7. **Start the development server**:
   ```bash
   php artisan serve
   ```

> **Note**: Emails are sent to the `log` driver by default. You can change this to something like `mailtrap` in the `.env` file.

---

## Contributing

We welcome contributions to Pinkary! To ensure a smooth process, please follow the guidelines below.

- Before jumping into a PR be sure to search existing [PRs](https://github.com/pinkary-project/pinkary.com/pulls) or [issues](https://github.com/pinkary-project/pinkary.com/issues) for an open or closed item that relates to your submission.

### Step-by-step Contribution Guide

1. **Fork the repository**:
   Head over to the [Pinkary GitHub repository](https://github.com/pinkary-project/pinkary.com) and click the **Fork** button to create a copy under your own account.

2. **Clone your fork**:
   After forking, clone your own copy of the repository to your local machine:
   ```bash
   git clone https://github.com/your-username/pinkary.com.git
   cd pinkary.com
   ```

3. **Create a new branch**:
   For any feature or bug fix, create a new branch:
   ```bash
   git checkout -b feat/your-feature # or fix/your-fix
   ```

4. **Make your changes**:
   Implement your changes or new features as needed.

5. **Run the test suite**:
   Before committing, ensure everything works as expected:
   ```bash
   composer test
   ```
   > **Note**:    Pull requests that don't pass the test suite will not be merged. Always ensure tests are green before submitting.

6. **Commit and push your changes**:
   Once your changes are tested and ready, commit them:
   ```bash
   git commit -am "Description of your changes"
   git push origin feat/your-feature
   ```

7. **Create a pull request**:
   Go to the original Pinkary repository and [create a pull request](https://github.com/pinkary-project/pinkary.com/pulls) from your branch to the `main` branch.

---

## Tooling

We use several tools to ensure code quality and consistency. The key ones include:

- [Pest](https://pestphp.com) for testing.
- [PHPStan](https://phpstan.org) for static analysis.
- [Laravel Pint](https://laravel.com/docs/11.x/pint) for code style.
- [Rector](https://getrector.org) for keeping code up to date with the latest PHP version.

### Running the Tools

You can run these tools individually with the following commands:

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

## Production

Pinkary is hosted on [DigitalOcean](https://www.digitalocean.com) and uses [Laravel Forge](https://forge.laravel.com) to manage the server and deployments. The server is running on Ubuntu 22.04 (LTS) x64 and is a 2 vCPUs 2GB / 25GB Disk droplet.

The only service we use is [Mailcoach](https://mailcoach.app) to manage the send emails. Besides that, SQLite is used as database driver, sessions driver, queue driver, cache driver, etc.

Server backups are done daily by Digital Ocean.

---

Pinkary is an open-sourced software licensed under the **[GNU Affero General Public License](LICENSE.md)**

