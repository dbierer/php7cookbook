# PHP Installation Notes

* Basic procedure:
```
./configure
make
make test
sudo make install
```
* `configure` options:
```
./configure --prefix=/usr/local/php7 --with-config-file-path=/usr/local/php7/etc --with-config-file-scan-dir=/usr/local/php7/etc/conf.d --disable-debug --enable-inline-optimization --disable-all --enable-libxml --enable-session --enable-spl --enable-xml --enable-hash --enable-reflection --with-pear --with-layout=GNU --enable-filter --with-pcre-regex --with-zlib --enable-simplexml --enable-dom --with-libxml --with-openssl --enable-pdo --with-pdo-sqlite --with-readline --with-iconv --with-sqlite3 --disable-phar --enable-xmlwriter --enable-xmlreader --enable-mysqlnd --enable-json
```
* some errors you might see:
```
configure: error: xml2-config not found. Please check your libxml2 installation.
configure: error: Please reinstall readline - I cannot find readline.h
configure: WARNING: unrecognized options: --enable-spl, --enable-reflection, --with-libxml
```
* See: http://superuser.com/questions/740399/how-to-fix-php-installation-when-xml2-config-is-missing
* Install dependencies
```
sudo apt-get install libxml2-dev libreadline-dev
```
* Good reading: http://jcutrer.com/howto/linux/how-to-compile-php7-on-ubuntu-14-04
* PHP 7 / Windows
    * XAMPP: https://www.apachefriends.org/download.html

