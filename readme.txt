How to use the project:
navigate to the root directory.
In case composer isnt installed in your system, please install it(Composer can be found on the Composer website (https://getcomposer.org).
run composer install command
After all dependencies are installed, run the server in the root directory with the following command:
php -S localhost:8000
this will run the server and listen to incoming requests.
After server is alive and running, open a new seperate terminal and run the following command in the root diretory:
php scheduler.php
this will trigger the scheduler script which runs and executes the worker once a minute.


#DB creds are located in config.php file and you also have dump.sql file
#Assignment.postman_collection.json file contains the postman collection of the server requests, you can import it via postman.
Enjoy :)

with regards
Yuval Zolotovitzky
email - yuvalik94@gmail.com
phone - 0545807837


