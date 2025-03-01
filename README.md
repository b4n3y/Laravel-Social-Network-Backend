<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Laravel Social Network Backend

This is a fully featured social network backend built with Laravel, designed to power a modern Instagram-like application. It includes authentication, user management, real-time interactions, media uploads, notifications, and advanced search functionality.

## Repository

[GitHub Repository](https://github.com/b4n3y/laravelbackend.git)

## Features

### Authentication & Security
- **Laravel Sanctum** for API authentication
- Secure user login, registration, and password reset
- OAuth integration for social logins (Google, Facebook, etc.)

### User Management
- Profile management (username, bio, avatar, etc.)
- Follow/unfollow system
- Account privacy settings (public/private accounts)

### Posts & Media
- Create, edit, and delete posts
- Image & video uploads with Laravel Media Library
- Post captions, hashtags, and mentions

### Engagement Features
- Like & comment system
- Replies to comments
- Save/bookmark posts
- Share posts

### Notifications
- Real-time notifications for likes, comments, and follows
- Push notifications using Firebase
- Email notifications for important events

### Real-time Features
- WebSockets & Laravel Broadcasting for live updates
- Direct messaging (DMs) with real-time chat
- Live video streaming integration

### Search & Discovery
- Full-text search with Laravel Scout & Algolia
- Trending posts & hashtag system
- User & post recommendations

### Stories & Highlights
- Ephemeral stories that disappear after 24 hours
- Story highlights feature for saving important stories
- Story views tracking

### Explore & Feed Algorithm
- Personalized home feed based on follows & engagement
- Explore page with trending content & suggested users

### API & Mobile App Support
- Fully RESTful API with proper authentication & rate limiting
- WebSockets for real-time interactions
- API documentation with Swagger/Postman

## Installation

1. Clone the repository:
   ```sh
   git clone https://github.com/b4n3y/laravelbackend.git
   cd laravelbackend
   ```

2. Install dependencies:
   ```sh
   composer install
   ```

3. Copy and set up environment variables:
   ```sh
   cp .env.example .env
   ```
   Update database and other configurations in `.env`.

4. Generate application key:
   ```sh
   php artisan key:generate
   ```

5. Run migrations and seed data:
   ```sh
   php artisan migrate --seed
   ```

6. Serve the application:
   ```sh
   php artisan serve
   ```

## API Documentation

The API is designed for front-end applications, both web and mobile. Detailed API documentation is available [here](#) (replace with actual link if using Swagger or Postman).

## Contributing

We welcome contributions! Feel free to submit issues or create pull requests.

## Security

If you find any security vulnerabilities, please report them via email instead of publicly disclosing them.

## License

This project is open-source and licensed under the [MIT license](https://opensource.org/licenses/MIT).

