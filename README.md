# BC24 : Symfony Webapp - Food traceability


## Presentation

This project is the user interface for the food traceability web-application, specialising in meat. 
This UI is designed to be run locally on a raspberryPi via a touch screen or any other screen format, it is responsive.
This project was carried out with several groups of students at different levels of the MIAGE at Paris 1 Panthéon Sorbonne.

The webapp consists of two parts: 
1. A user section, for consulting the information and traceability of any resourse and product in the production chain
2. A business section, enabling the information required for traceability to be entered throughout the production chain, while facilitating managementof resources for the various actors (breeder, transporter, slaughterer, manufacturer, distributor, admin) in the chain.


## Features

### In a nutshell :
1. signin/login/access
2. role (breeder, slaughterer, transporter, manufacturer, distributor) request 
3. read/write NFC tags to physically identify resources
4. create/modify resources (NFT on our blockchain) with strict and secure access
5. find resource metadata and traceability
6. manage your resources
7. transfer and acquire resources (with transaction history)
8. create recipes and create products (combined resources) from them
9. sell products
10. report resources and products (bad temperature, disease of animal, etc.)

### In full : 

#### For everybody :
- Customer section (Login, Logout, Change personal details, Delete account)
- Search for and Find resources and/or products with the full traceability
- Search history
- Display of products recently considered dangerous to health
- Report dangerous products and send alert
  
#### Specific for Admin :
- Resources management : resource creation, modification
- Management of accounts, roles, production sites, etc.

#### For all actors (breeder, transporter, slaughterer, manufacturer, distributor), excluding classic user :
- Request ownership of a resource and history of requests with their status
- Transaction management (incoming acquisition requests): acceptance, refusal
- List(s) of resources for which they are owner and information on these resources

#### Specific For breeder :
- Birth registration of animal

#### Specific for slaughterer :
- Registration of the slaughter of an animal (creation of a Carcass resource)
- Registration of the transformation of a carcass into half carcass

#### Specific for manufacturer :
- Cutting half a carcass into pieces (creation of Piece resources)
- Creation of proprietary recipes
- Recipe application (creation of PRODUCT with combined resources, following a predefined recipe)

#### Specific for distributor :
- Product sales (exit from the production chain)


## Local Installation
prerequisity : your OS distribution must at least allowed the php-8.3 use

1. Clone the directory and get to the root of the project (gui/symfony-docker).
2. Instal dependencies with the `composer update` command
3. The DB is located in the 'var' folder; it is a SQLite file named "BC24DB.db".
   To display the different tables, run the command `php bin/console doctrine:migrations:migrate`.
4. To populate the DB, run the command `php bin/console doctrine:fixtures:load --group=app`.
5. The 404 and 403 error pages have been customised, switch to production mode (otherwise Symfony will override the error pages) in the .env file.
6. To run the webapp use the `symfony serve -d` command
7. To stop the webapp use the `symfony serve:stop` command
8. To clean the cache (necessary to see new code updates) use the `php bin/console cache:clear` command

### Common error :
1. If it tell you : “An exception occurred in the driver: could not find driver”,  then use the `sudo apt-get update` command


## OR run with docker anywhere
1. change the last line in bundles.php
   Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => false],
2. go into the root directory `cd gui/symfony-docker`
3. run the docker compose command `docker-compose -f compose.yaml up --build -d`
- dont use `-d` if you want to have docker output
- first time will take a minute or two
- will finally launch the application (make sure you check out all the host addresses)
  there are https://localhost:443 (https) and http://localhost:80 (http)
  Both should work but lets just stay with the later (80) one for now.
You will already be able to see the web app but no login is possible, since the DB migration was not yet done
4. enter the docker container comand line interface `docker exec -ti symfony-docker-php-1 sh`
5. in the docker container execute the DB shema creation and migration commands (answer all the questions with y) `php bin/console doctrine:migrations:migrate`then `php bin/console doctrine:fixtures:load --group=app`
If you go back to the web app you will be able to log in now.


## Use

### Good to know :
- To view the DB, we recommend using SQLiteStudio and opening the .db file with this software.
- The fixture has created several users: Here's how to connect to their accounts:
The different roles are as follows: admin; breeder; transporter; slaughterer; manufacturer; distributor
For each of these roles, the email is [french role name]@gmail.com and the password is [french role name].
Exemple : Email="eleveur@gmail.com" // PWD="eleveur"

There are also 100 customer accounts, user0@gmail.com → user99@gmail.com with the password "user".



## Project management

The project was managed using Notion, bringing together all the different working groups and their contributions.
It is free to consult it [here](https://www.notion.so/invite/871c052a59e13d1fd9d87985533f88fe1d821b95).
