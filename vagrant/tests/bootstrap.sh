#!/usr/bin/env bash

echo "en_GB.UTF-8 UTF-8" >> /etc/locale.gen

locale-gen

echo "tmpfs  /var/lib/postgresql  tmpfs  size=1536m,auto  0  0" >> /etc/fstab
mkdir /var/lib/postgresql
mount /var/lib/postgresql

sudo apt-get update
sudo apt-get install -y php phpunit postgresql php-pgsql zip  php-mbstring

sudo su --login -c "psql -c \"CREATE USER test WITH PASSWORD 'testpassword';\"" postgres
sudo su --login -c "psql -c \"CREATE DATABASE test WITH OWNER test ENCODING 'UTF8'  LC_COLLATE='en_GB.UTF-8' LC_CTYPE='en_GB.UTF-8'  TEMPLATE=template0 ;\"" postgres

mkdir /home/vagrant/bin
cd /home/vagrant/bin
wget -q https://getcomposer.org/composer.phar

cp /vagrant/vagrant/tests/parameters_test.yml /vagrant/app/config/parameters_test.yml
cp /vagrant/vagrant/tests/parameters_test.yml /vagrant/app/config/parameters.yml

cd /vagrant
php /home/vagrant/bin/composer.phar  install

rm -r /vagrant/app/cache/prod/*
rm -r /vagrant/app/cache/dev/*

cp /vagrant/vagrant/tests/test /home/vagrant/test
chmod a+rx /home/vagrant/test