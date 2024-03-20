<p align="center">
    <img src="https://pinkary.com/img/logo.svg" width="600" alt="PEST">
    <p align="center">
        <a href="https://github.com/pinkary-project/pinkary.com/actions"><img alt="GitHub Workflow Status (master)" src="https://img.shields.io/github/actions/workflow/status/pinkary-project/pinkary.com/tests.yml?branch=main&label=Tests"></a>
        <a href="https://packagist.org/packages/pinkary-project/pinkary.com"><img alt="License" src="https://img.shields.io/packagist/l/pinkary-project/pinkary.com"></a>
    </p>
</p>

------

**Welcome to Pinkary's early access!** Pinkary is a landing page for all your links and a place to connect with like-minded individuals without the noise.

Initially, it was created to help people share their links in a more organized way. In just 15 hours, we went from `composer create-project` to production, and after 24 hours, we reached over 1,000 users.

The source code still shows some signs of the rush; that's why we think it's important to share it with you—so you can see how we've built it, combining fast pace given the circumstances with the quality we always aim for.

- Sponsor us on **[GitHub »](https://github.com/sponsors/nunomaduro)**
- Follow us on X at **[@PinkaryProject »](https://twitter.com/PinkaryProject)**
- Join us on **[t.me/+Yv9CUTC1q29lNzg8 »](https://t.me/+Yv9CUTC1q29lNzg8)**
- Wish to contribute? Here is how:

## Installation

Pinkary is a regular Laravel application. You can install it using the following commands:

```bash
# Fork the project, clone it, and create a new branch
git clone git@github.com:<your-username>/pinkary.com.git pinkary.com

cd pinkary.com

git checkout -b feat/your-feature # or fix/your-fix
```

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

Prepare your database and run the migrations:

```bash
touch database/database.sqlite

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

Visit [github.com/<your-username>/pinkary.com](https://github.com/<your-username>/pinkary.com) and create a pull request.

---

This project's code is still private and will be open-sourced soon. **Do not share it with anyone yet**.

Pinkary is an open-source project licensed under the **[GNU Affero General Public License](LICENSE.md)**.
