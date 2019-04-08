Small bugtracker based on Slim and https://github.com/gothinkster/slim-php-realworld-example-app. 

Install should be straightforward. 

Create a database named slimb or adjust DB name in .env/.env.example to a fitting one.

Then composer update, phinx init, phinx migrate, composer start. 

TODO:
- Change favourites to working-on
- Add DB columns to add bug severity ranking
- Add auth to more routes
- Add admin users
- Write some clients for interacting with it

