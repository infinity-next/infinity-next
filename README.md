
Infinity Next was never completed and is currently in an unusable state. I am currently working on refactoring it to Laravel 6 and modernizing some backend work, but it is just something I am doing for myself. For some reason, I just can't stand to see it unfinished.

Use at your own risk: I can't help you.

---

**[Infinity Next](https://16chan.nl)** is an imageboard using the [Laravel Framework](https://github.com/laravel/laravel). It was conceived as a way to replace existing free imageboard software that have aged poorly. Infinity Next is completely free to use, but modifications to the source code must be made open source as well.

# Submitting Issues
If you are submitting an [issue](https://github.com/infinity-next/infinity-next/issues) to developers, please supply the following:

- A concise description of your problem.
- An _exact case_ of the error. If the problem is code related, reference the file and line number.
- Additional details of articles if applicable.

Please don't assume we'll understand exactly what you're talking about. If I can read an error, copy+paste something, or upload a file and reproduce an error in 30 seconds after opening your message, I can fix it without having to ask you for more information. If you do not provide an error case or details on how to recreate what you're experiencing, the first response to your issue will be "please provide an example".

# Requirements
Larachan runs on Laravel 5 and has the same requirements.

* `PHP 7.2` or greater.
  * `php-bcmath` for dealing with IP addresses.
  * `php-mcrypt` for bcrypt functions.
  * `php-gd` for captcha codes and other image manipulation.

You may also need the following because not all PHP packages include them:
  * `mbstring` for Lavarel.
  * `fileinfo` for Composer requirement.

When installing from source,
  * `composer`

You can also pass `--prefer-source` to `composer` to forego the Github OAuth requirement.

# Installation
Infinity Next is currently below its first release version. When it is finished, a compiled archive will be available with an installation process. Right now, however, you must build it yourself and have access to a command line interface.

1. Pull the code and navigate to the directory where the `.env` file is.
2. Edit the `.env` to your liking. This is where you enter database details.
3. Issue the command `composer update` and wait as 3rd party libraries are added.
4. Issue the command `php artisan migrate` or `./artisan migrate`
5. Issue the command `php artisan db:seed` or `./artisan db:seed`
    * Take note of the Admin account password that will be created for you.
6. Add the Laravel crontab service provided in `crontab.txt`
7. Restart your PHP daemon, using a command such as `service apache2 restart` or `service php5-fpm restart`.

You should now have a `/test/` board. The admin account will be named `Admin`.

# License
Infinity Next is distributed under the [AGPL 3.0](http://choosealicense.com/licenses/agpl-3.0/).

In short:
* You may use Infinity Next for any reason you please.
* You may modify Infinity Next as you see fit.
* You may profit with Infinity Next.

However, you also agree that:
* Infinity Next & Contributors are not liable for this software and any damage it may do.
* Infinity Next & Contributors are not liable if this software is used to break the law.
* You will not sell copies or modifications of this source code (no sublicensing).
* You *must* distribute the source code for any publicly hosted modifications of Infinity Next under the [AGPL 3.0](http://choosealicense.com/licenses/agpl-3.0/) license in a conventional format.

The AGPL3 license is designed to protect the end user by keeping modified copies of the source open and free.

While not binding, I do ask that users link back to this git repository on their website. Sharing is caring. ♥

# Contributing
Everyone is welcome to contribute, but please follow repository etiquette.

* Fork the repository.
* Raise a separate issue for everything you intend to fix.
* Plan your fix and allow for discussion.
* Tie your commits to issues.
* Keep pull requests concise, list issues addressed, and make it easy to pull them.

It would benefit you to make sure your solution has the OK before writing any code.

## Coding Standards
In the name of preventing conflict and keeping the codebase clean, I am implementing the **[exact same standards of Laravel contributors](http://laravel.com/docs/4.2/contributions#coding-style)**.

> Laravel follows the [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) and [PSR-1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md) coding standards. In addition to these standards, the following coding standards should be followed:
>
> * The class namespace declaration must be on the same line as `<?php`.
> * A class's opening `{` must be on the same line as the class name.
> * Functions and control structures must use Allman style braces.
> Indent with tabs, align with spaces.

"Allman style braces" refer to having a curly-brace on a new line at the same level of indentation as the conditional itself.

```
if (condition)
{
	// Code here.
}
```
