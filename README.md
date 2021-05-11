# Larapress Authentication

A package to provide SMS, Email, 3rdParty user registration with Larapress CRUD.
Based on Models provided in Larapress Profiles.

## Dependencies

- [Larapress CRUD](../../../press-crud)
- [Larapress Pages](../../../press-pages)
- [Larapress Reports](../../../press-reports)
- [Larapress Notifications](../../../press-notifications)
- [Larapress Profiles](../../../press-profiles)

## Install

1. `composer require peynman/larapress-auth`

## Config

1. Run `php artisan vendor:publish --tag=larapress-auth`
1. Set `larapress` as your `auth.providers.user.driver` config
1. Set default `Role` ID as larapress `larapress.auth.signup.default_role` config, or set it null to disable signup
1. Set default `SMSGatewayData` ID as larapress `larapress.auth.signup.sms.default_gateway` config
1. Set default admin user id for `larapress.auth.signup.sms.default_author` config

## Usage

- After configuration is completed you can use API endpoints to register or authenticate users

## Development/Contribution Guid

- create a new laravel project
- add this project as a submodule at path packages/larapress-crud
- use phpunit, phpcs
  - `vendor/bin/phpunit -c packages/larapress-crud/phpunit.xml packages/larapress-auth/`
  - `vendor/bin/phpcs --standard=packages/larapress-crud/phpcs.xml packages/larapress-auth/`
