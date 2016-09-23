#PHP 7 Cookbook

##Database Setup
* Read through Chapter 1, "Defining a test MySQL database"
* See "db.config.php" for example MySQL params; adjust to match your environment
* Use this SQL statement to match the examples in the book:
```
CREATE DATABASE IF NOT EXISTS dbname DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci;
CREATE USER 'cook'@'%' IDENTIFIED WITH mysql_native_password;
SET PASSWORD FOR 'cook'@'%' = PASSWORD('book');
GRANT ALL PRIVILEGES ON dbname.* to 'cook'@'%';
GRANT ALL PRIVILEGES ON dbname.* to 'cook'@'localhost';
FLUSH PRIVILEGES;
```
* "php7cookbook.sql" contains all the sample data used in the book
* You will need to unzip "world_city_codes.zip" and then import "world_city_codes.sql" into the "php7cookbook" database separately

