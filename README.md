# ProjectShare

A web application for sharing project plans and status updates with stakeholders. Built with PHP 8 and MySQL.

## Features

- **Multi-Provider Authentication**: Login via Google, Apple, Microsoft, or email with OTP code
- **Project Management**: Create and manage projects with schedules, tasks, and milestones
- **Stakeholder Sharing**: Generate unique secret links for building owners, tenants, subcontractors
- **Impact Notifications**: Inform stakeholders about service interruptions (electricity, water, heating)
- **Document Management**: Upload files with view tracking and acknowledgement requirements
- **Plan Versioning**: Track changes and notify stakeholders of updates
- **Feedback Collection**: Gather structured feedback at project completion
- **Responsive Design**: Mobile-first design works on all devices

## Requirements

- PHP 8.1+
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Apache/Nginx with URL rewriting

## Installation

### 1. Clone and Install Dependencies

```bash
cd /var/www
git clone <repository-url> projectshare
cd projectshare
composer install
```

### 2. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` with your settings:

```env
# Application
APP_NAME="ProjectShare"
APP_URL="https://your-domain.com"
APP_ENV="production"
APP_DEBUG=false
APP_SECRET="your-secure-random-string"

# Database
DB_HOST="localhost"
DB_PORT="3306"
DB_DATABASE="projectshare"
DB_USERNAME="your_db_user"
DB_PASSWORD="your_db_password"

# Email (SMTP)
MAIL_HOST="smtp.example.com"
MAIL_PORT=587
MAIL_USERNAME="your_email"
MAIL_PASSWORD="your_password"
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="ProjectShare"

# OAuth (Optional - configure as needed)
GOOGLE_CLIENT_ID=""
GOOGLE_CLIENT_SECRET=""
MICROSOFT_CLIENT_ID=""
MICROSOFT_CLIENT_SECRET=""
```

### 3. Set Up Database

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE projectshare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# Run migrations
php database/migrate.php
```

### 4. Configure Web Server

#### Apache

Ensure `mod_rewrite` is enabled. The `.htaccess` file in `/public` handles routing.

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/projectshare/public

    <Directory /var/www/projectshare/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/projectshare/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

### 5. Set Permissions

```bash
chmod -R 755 /var/www/projectshare
chmod -R 775 /var/www/projectshare/uploads
chown -R www-data:www-data /var/www/projectshare
```

### 6. Development Server

For local development:

```bash
cd projectshare
php -S localhost:8000 -t public
```

## OAuth Setup

### Google

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create a new project
3. Enable Google+ API
4. Create OAuth 2.0 credentials
5. Add authorized redirect URI: `https://your-domain.com/auth/google/callback`

### Microsoft

1. Go to [Azure Portal](https://portal.azure.com)
2. Register a new application
3. Add redirect URI: `https://your-domain.com/auth/microsoft/callback`
4. Create a client secret

### Apple

1. Go to [Apple Developer](https://developer.apple.com)
2. Create an App ID with Sign In with Apple
3. Create a Services ID
4. Configure redirect URI: `https://your-domain.com/auth/apple/callback`

## Project Structure

```
projectshare/
├── assets/
│   ├── css/
│   │   └── app.css          # Main stylesheet
│   └── js/
│       └── app.js           # Main JavaScript
├── config/
│   ├── app.php              # Application config
│   └── database.php         # Database connection
├── database/
│   ├── migrations/          # SQL migration files
│   └── migrate.php          # Migration runner
├── public/
│   ├── index.php            # Entry point
│   └── .htaccess            # Apache rewrite rules
├── src/
│   ├── Controllers/         # Route handlers
│   ├── Models/              # Data models
│   ├── Services/            # Business logic
│   └── Views/               # Templates
├── uploads/                 # User uploads
├── .env.example             # Environment template
├── composer.json            # Dependencies
└── README.md
```

## Usage

### For Project Managers

1. **Create Account**: Sign up and set up your company
2. **Create Project**: Add project details, location, and timeline
3. **Add Tasks**: Define project plan with tasks and milestones
4. **Define Impacts**: Specify service interruptions for each task
5. **Add Stakeholders**: Add building owners, tenants, subcontractors
6. **Share Links**: Send unique links to stakeholders
7. **Upload Documents**: Add plans, contracts, permits
8. **Track Views**: Monitor who has viewed and acknowledged documents
9. **Publish Updates**: Notify stakeholders of plan changes
10. **Collect Feedback**: Request feedback at project completion

### For Stakeholders

1. **Access via Link**: Open the unique link sent by the project manager
2. **View Schedule**: See upcoming tasks and timeline
3. **Check Impacts**: Review service interruptions that affect you
4. **View Documents**: Access project documents
5. **Acknowledge**: Confirm receipt of important documents
6. **Provide Feedback**: Share your experience at project end

## Security

- All passwords hashed with Argon2id
- CSRF protection on all forms
- Secure session handling
- XSS protection via output escaping
- SQL injection prevention via prepared statements
- Secure cookie settings (HttpOnly, SameSite)

## License

MIT License
