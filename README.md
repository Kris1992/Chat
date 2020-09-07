# CHAT 


# Setup

Thanks to download the code. 

To get it working, follow these steps:

**Download Composer dependencies**

Make sure you have [Composer installed](https://getcomposer.org/download/)
and then run:

```
composer install
```

You may alternatively need to run `php composer.phar install`, depending
on how you installed Composer.

**Configure the .env (or .env.local) File**

Rename to `.env.local.dist` file to `.env.local` and make changes you need - specifically
in `DATABASE_URL`, `MAILER_URL`, `GOOGLE_RECAPTCHA_SITE_KEY` and `GOOGLE_RECAPTCHA_SECRET`.
To properly run chat You must fill Mercure and Messenger(take care about current participants of public chat rooms) enviroments too.

**Setup the Database**

If you end previous step, it's time to create tables in database by commands below:

```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

**Start the built-in web server**

You can use Nginx or Apache, but Symfony's local web server
works even better.

To install the Symfony local web server, follow
"Downloading the Symfony client" instructions found
here: https://symfony.com/download - you only need to do this
once on your system.

Then, to start the web server, open a terminal, move into the
project, and run:

```
symfony serve --no-tls
```

Now check out the site at `http://localhost:8000`

To proper work chats you must run mercure by following command (note you should generate your own JWT_KEY before):

```
./bin/mercure --jwt-key='JWT_KEY' --addr='localhost:3000' --debug --cors-allowed-origins='http://localhost:8000'
```

Important!!
If you run mercure with following command:

```
./bin/mercure --jwt-key='JWT_KEY' --addr='localhost:3000' --debug --cors-allowed-origins='http://localhost:8000'
```

The site must had the same domain e.g `http://localhost:8000` because otherwise it can be blocked by CORS policy

Have fun!

## Tests stack

Unit Tests - PHPSpec \
Functional Tests - Behat \
Integration Tests - PHPUnit \

## Run Tests

./vendor/bin/phpspec run \
./vendor/bin/behat OR ./vendor/bin/behat --tag={tagName} \
php bin/phpunit \

## Used Technologies

Mercure - I use server send event to resend messages to partcicipants of chat rooms (better solution than resend ajax calls by users to check new messages). \

Messenger - take controll about active partcicipants of public chat rooms. If user go out from chat room or close window in browser messenger remove him from participants of public chat room. It is useful to removed uploaded and not used attachments for chat messages or petition too.  \

## Have Ideas, Feedback or an Issue?

If you have suggestions or questions, please feel free to
open an issue on this repository.Thanks a lot for feedback 
from you;).
