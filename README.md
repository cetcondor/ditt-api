# DITT API

DBJR Internal Time Tracking API developed by VisionApps

## Build

To install the project:

1. Install composer packages: `composer install`
2. Run the installation process `vendor/bin/robo install`
3. Create certificates for JWT:
  ```bash
  $ openssl genrsa -out config/jwt/private.pem -aes256 4096
  $ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
  ```
4. Set `JWT_PASSPHRASE` in `.env` to whatever you entered as a password in previous step

To update the project:

1. Install composer packages: `composer install`
2. Run the installation process `vendor/bin/robo update`

## User Accounts

Following accounts are available for testing purposes:

| Email                      | Password | Roles           |
|----------------------------|----------|-----------------|
| employee@example.com       | password | EMPLOYEE        |
| employee-admin@example.com | password | EMPLOYEE, ADMIN |
| admin@example.com          | password | ADMIN           |
| superadmin@example.com     | password | SUPER ADMIN     |

## Config

To change supported years, supported holidays and worked hours limits, go to the `/config/config.php` file and
make changes there. Config is exposed as API endpoint, so you do not need to change it in client's config.