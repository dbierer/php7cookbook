# PHP 7 Cookbook

This repository is designed to supplement the examples discussed in the
[PHP 7 Programming Cookbook](https://www.packtpub.com/application-development/php-7-programming-cookbook)

## Database Setup
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
* `php7cookbook.sql` contains all the sample data used in the book
* In order to use the sample data in the `world_city_codes` database table, you'll need to download it separately from this URL:
    * opensource.unlikelysource.org/world_city_data.sql.zip
    * you can then import `world_city_codes.sql` into the `php7cookbook` database separately
