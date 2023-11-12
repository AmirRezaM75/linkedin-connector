# Linkedin Connector

## Installation

```
cp .env.example .env
```

Update database credentials to your needs:

```
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Build the image and run the container:

```
export UID=$UID
docker compose up -d
```

Migrate database:

```
docker compose exec fpm php artisan migrate
```

Visit `localhost:8080` and upload a txt file in which each profile link is separated by new line.

```
https://www.linkedin.com/in/amirreza/
https://www.linkedin.com/in/vahid/
https://www.linkedin.com/in/ali/
```

## Issues
LinkedIn will ask you to solve a challenge in the form of captcha
when detected unusual traffic from your account.
In order to don't stuck in this step. We recommend you to login into your account via browser
open the console and execute `document.cookie` and past the value into `storage/cookies.txt` file.
