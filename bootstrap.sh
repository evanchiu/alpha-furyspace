#!/usr/bin/env bash

apt-get update

MYSQL_ROOT_PASSWORD="root"
DATABASE="furyspace"
INIT_CODE="furious"

# Configure mysql password for non-interactive installation
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password password $MYSQL_ROOT_PASSWORD"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $MYSQL_ROOT_PASSWORD"

# Install lamp and required libraries
apt-get install -y lamp-server^ php5-gd

# Replace webroot with vagrant link
rm -rf /var/www/html
ln -fs /vagrant/web /var/www/html

# Configure database: create, load schema, add initial code
echo "create database $DATABASE" | mysql --password="$MYSQL_ROOT_PASSWORD"
mysql --password="$MYSQL_ROOT_PASSWORD" $DATABASE < /vagrant/db/furyspace.sql
echo "insert into codes values (\"$INIT_CODE\")" | mysql --password="$MYSQL_ROOT_PASSWORD" $DATABASE

# Allow short open tags in php.ini and restart apache
sed -i "/short_open_tag = Off/c\short_open_tag = On" /etc/php5/apache2/php.ini 
/etc/init.d/apache2 reload
