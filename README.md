
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

* These instructions are based on a clean Debian 10 installation.
  * If you don't know how to deal with `sudo: command not found`, you probably shouldn't be doing this.

1. Add `oldstable main` and `buster-backports main` to `/etc/apt/sources.list`
   1. `apt update`
1. `apt install php7.3 php7.3-common php-bcmath php-mcrypt php-gd php-mbstring php-xml php-curl php-redis php-pgsql php-zip php-gmp`
1. `apt install postgresql`
   1. `sudo -u postgres psql` or `runuser -u postgres psql`
   1. `create user chan;`
   1. `create database chan owner chan;`
   1. `\c chan`
   1. `CREATE EXTENSION fuzzystrmatch;`
   1. `\password chan`
   1. `\q`
1. `apt install redis git`
1. At your discretion: 
   1. `apt autoremove apache2`
   1. `apt install nginx-full`
   1. `apt install php-fpm`
1. `adduser --system infinitynext --home /var/www/infinity-next`
1. `git clone https://github.com/infinity-next/infinity-next.git /var/www/infinity-next`
1. `chown -hR infinitynext /var/www/infinity-next`
1. `sudo -u infinitynext /bin/bash` or `runuser -u infinitynext /bin/bash`
1. `cp .env.example .env`
1. Edit `.env`, set:
   1. A unqiue 32 character value for `APP_KEY`.
   1. `DB_DATABASE`,`DB_USERNAME`,`DB_PASSWORD` as configured.
1. `php composer.phar update` and wait as 3rd party libraries are installed.
1. `php artisan migrate`
1. `php artisan db:seed`
    * Take note of the Admin account password that will be created for you.
1. Add the Laravel crontab service provided in `crontab.txt`
   1. `cat ./docs/crontab.txt`
   1. `crontab -e`
   1. Do the needful.
1. `exit`
1. Add the virtual host configuration for nginx.
   1. `cp /var/www/infinity-next/docs/nginx.txt /etc/nginx/sites-available/infinity-next`
   1. Adjust `/etc/nginx/sites-available/infinity-next` as required.
   1. `ln -s /etc/nginx/sites-available/infinity-next /etc/nginx/sites-enabled/infinity-next`
   1. `nginx -t`
      1. `systemctl restart nginx`
1. Give `www-data` read/write access to application storage:
   1. `chown -hR infinitynext:www-data /var/www/infinity-next/storage/`
   1. `chmod -R g+rw /var/www/infinity-next/storage/`
1. Install Node.js (https://github.com/nodesource/distributions/blob/master/README.md#debinstall)
   1. `curl -sL https://deb.nodesource.com/setup_14.x | bash -`
   1. `apt-get install -y nodejs`
1. `npm install`
1. `npm run prod`
   1. Check `package.json` for npm script definitions.
1. CONGRATULATIONS!!! U HAVE SUCCESSED 🎉🎉🎉
   1. You should now have a `/test/` board.
   1. The admin account will be named `Admin`.

To accommodate upstream reverse proxies:

1. `sudo -u infinitynext /bin/bash` or `runuser -u infinitynext /bin/bash`
1. `php artisan vendor:publish`
1. Edit `config/trustedproxy.php` as required.

Additional reminders:

1. Set your SMTP server in `.env` - you need this for password resets.
1. Adjust your maximum file upload size in nginx and php!
1. Install `ffmpeg` for multimedia assets to work!

## Adding WebSockets
WebSockets will play an increasingly important part of the software as they increase the responsiveness of the entire application. It is suggested to set it up, though it is somewhat complicated.

1. Install [Supervisor](https://laravel.com/docs/7.x/queues#supervisor-configuration) to run `artisan queue:work redis`. Example in `docs/supervisor.txt`.
2. Install [Laravel Echo Server](https://github.com/tlaverdure/laravel-echo-server) by running `npm install -g laravel-echo-server` or `yarn global add laravel-echo-server`.
3. Run `laravel-echo-server config` and generate a `laravel-echo-server.json`.
    * You must be using Redis and you must make sure the Redis configuration matches exactly.
    * Ensure the hostname matches exactly or events will not be sent to connected users.
    * I suggest using the `docs/nginx.txt` configuration with a `/socket.io` proxy pass to the server instead of exposing it directly.
4. Run `laravel-echo-server start`. This does not launch as a daemon, so consider using a program like `screen` to keep it running without a terminal attached.

Further documentation about [Laravel Queues](https://laravel.com/docs/7.x/queues) and [Laravel Broadcasting](https://laravel.com/docs/7.x/broadcasting) can be found in the official Laravel documentation.

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
