## BlueSky Post Laravel

A simple Laravel application that allows users to create posts on BlueSky (AT Protocol), including text and image uploads, using BlueSky App Password authentication.
- BlueSky AT Protocol authentication
- Media upload handling
- Clean backend architecture
- Secure, stateless API integration


## Features

- Create BlueSky posts with text

- Upload images (PNG / JPG / WEBP)

- Uses BlueSky App Password (no normal password)

- Stateless authentication (token created per request)

- Fully compliant with official BlueSky documentation

## Tech Stack
- Laravel 10+

- PHP 8.1+

- Laravel HTTP Client

- Blade (basic UI)

- BlueSky AT Protocol API


### How It Works (Flow)

1. User clicks Create Post

2. App creates a BlueSky session (createSession)

3. Access token is received (short-lived)

4. Image is uploaded as raw bytes (if provided)

5. Post is created via createRecord

6. Token is discarded (no storage)

## BlueSky Authentication
BlueSky API requires:

- Account handle (not email)

- App Password (not normal password)

- Example handle: yourname.bsky.social

## Install Dependencies

- composer install
- cp .env.example .env
- php artisan key:generate

## Update .env
BLUESKY_IDENTIFIER=your-handle.bsky.social
BLUESKY_PASSWORD=your-app-password

## Clear Config Cache

- php artisan config:clear
- php artisan config:cache

## Run the App
- php artisan serve
- http://127.0.0.1:8000/post
