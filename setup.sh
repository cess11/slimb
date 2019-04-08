#! /bin/bash

composer update

php vendor/bin/phinx init
php vendor/bin/phinx migrate

