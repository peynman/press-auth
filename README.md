# W.I.P.

# Larapress Authentication
A package to provide SMS, Email based registration with Larapress CRUD & based on Models provided in Larapress Profiles.

## Dependencies
* Larapress CRUD
* Larapress Reports
* Larapress Notifications
* Larapress Profiles

## Install
1. ```composer require ```

## Config
1. Run ```php artisan vendor:publish --tag=larapress-auth```
1. Set ```larapress``` as your ```auth.providers.user.driver``` config
1. Set default ```Role``` ID as larapress ```larapress.auth.signup.default_role``` config, or set it null to disable signup
1. Set default ```SMSGatewayData``` ID as larapress ```larapress.auth.signup.sms.default_gateway``` config
1. Set default admin user id for ```larapress.auth.signup.sms.default_author``` config

## Usage
* After configuration is completed you can use API endpoints to register or authenticate users
