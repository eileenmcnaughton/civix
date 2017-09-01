language: php

php:
- 5.5
- 5.6
- hhvm

before_script:
- composer install -n --dev --prefer-source

script: vendor/bin/phpcs --standard=PSR2 src && vendor/bin/phpunit --coverage-text