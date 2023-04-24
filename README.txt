This program aims to automate the daily problem of composing diversified and complete meals.

You can manually insert the foods you prefere (POST /food), but you already dispose of a selected food dataset 
picked from Foodb (https://foodb.ca/downloads).

You can chose from these foods your favorite ones (you can easily search using GET /food/categories OR GET /food/name/{name}),
associate these foods to a user_id (POST /food/user) and than the application will generate your meals (lunch and dinner)
based on the diet you follow (vegan, vegetarian or onnivore) and the period (daily, weekly or monthly) 
for which you want to have your meals generated.


You can read the swagger.yml to understand the main APIs.


*installation*
Run these commands to have the development environment ready:
docker-compose up -d
docker-compose exec php_service composer install
docker-compose exec php_service composer setup                 [***]

*test*
Run this command to execute phpunit:
docker-compose exec php_service bin/phpunit

*server*
Run this command to have the symfony application ready:
docker-compose exec php_service symfony server:start


[***] the custom setup will:        
    - create the dev db
    - execute the migrations on the dev db
    - run the custom command that imports and manipulates the foods dataset
    - create the test db
    - execute the migrations on the test db