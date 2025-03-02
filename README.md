<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Laravel Social Network Backend

This is a fully featured social network backend built with Laravel, designed to power a modern Instagram-like application. It includes authentication, user management, real-time interactions, media uploads, notifications, and advanced search functionality.
It also has support for s3 object storage like aws s3 buckets and digital ocean spaces.

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
- Refer to the `API_DOCUMENTATION.txt` file for detailed API documentation.

## API Usage & Guidelines

### Authentication
- The API uses **Laravel Sanctum** for authentication.
- Authentication is handled via Bearer tokens.
- Include the `Authorization: Bearer <token>` header in all authenticated requests.

### Rate Limiting
- The API has rate limits in place to prevent abuse.
- Standard users are limited to **X** requests per minute.
- If the rate limit is exceeded, a `429 Too Many Requests` response is returned.

### API Response Format
- The API follows RESTful principles and returns JSON responses.
- Example success response:
  ```json
  {
    "status": "success",
    "data": { ... }
  }
  ```
- Example error response:
  ```json
  {
    "status": "error",
    "message": "Unauthorized"
  }
  ```

### Example API Request
```sh
curl -X GET "https://your-api-url.com/api/posts" \
     -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
     -H "Accept: application/json"
```

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

The API is designed for front-end applications, both web and mobile. For detailed API documentation, please refer to the `API_DOCUMENTATION.txt` file included in this repository.

## Contributing

We welcome contributions! Feel free to submit issues or create pull requests.

## Security

If you find any security vulnerabilities, please report them via email instead of publicly disclosing them.

## License

This project is open-source and licensed under the [MIT license](https://opensource.org/licenses/MIT).

