# DITT API

DBJR Internal Time Tracking API developed by VisionApps

## Build
 
To install the project:
 
1. Install composer packages: `composer install`
2. Run the installation process `vendor/bin/robo install`
3. Create certificates for JWT
```bash
$ openssl genrsa -out config/jwt/private.pem -aes256 4096
$ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```
 
To update the project:

1. Install composer packages: `composer install`
2. Run the installation process `vendor/bin/robo update`

## Users

Following accounts are creating for testing purposes:

| Email                      | Password | Roles           |
|----------------------------|----------|-----------------|
| employee@example.com       | password | EMPLOYEE        |
| employee-admin@example.com | password | EMPLOYEE, ADMIN |
| admin@example.com          | password | ADMIN           |
| admin@example.com          | password | SUPER ADMIN     |