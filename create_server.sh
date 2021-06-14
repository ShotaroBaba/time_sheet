#!/bin/bash

# TODO: Enable software backup
read -p "Would you like to create your server? Before you create your server, if you have your server data already, then make sure that you make a backup of your database. Otherwise, you will lose all of your data![y/n]: " yn 

case $yn in
    [Yy])
        break;;
    [Nn])
        exit 0;;
    *) 
        echo "Invalid input. Please type y/n. Abort."
        exit 1;;
esac

# The default docker value configuration file will be copied to
# .env
sudo cp .env_default .env

sudo docker-compose down && sudo docker image prune -a 2> /dev/null && \
sudo docker rm $(sudo docker ps -a -f status=exited -q) 2> /dev/null

sudo rm -rf config data logs test_data .mysql_secrets mysql_secrets decrypt_script && sudo docker-compose up -d

# Externally create password for mysql.
rsa_key_root_name=$(date +%Y_%m_%d_%H_%M_%S)
secret_dir=$(readlink --canonicalize .mysql_secrets)

key_dir=$(readlink --canonicalize .mysql_secrets/keys)
if [ ! -d $key_dir ]; then
    sudo mkdir $key_dir
    sudo echo $key_dir
fi

pass_dir=$(readlink --canonicalize .mysql_secrets/pass)
if [ ! -d $pass_dir ]; then
    sudo mkdir $pass_dir
    sudo echo $pass_dir
fi


pub_key=$key_dir/pub_$rsa_key_root_name.pem
priv_key=$key_dir/priv_$rsa_key_root_name.pem
encrypted_pass=$pass_dir/root_pass_$rsa_key_root_name.txt.enc
encrypted_admin_pass=$pass_dir/admin_pass_$rsa_key_root_name.txt.enc
encrypted_table_client_pass=$pass_dir/table_client_$rsa_key_root_name.txt.enc
encrypted_table_admin_pass=$pass_dir/table_admin_$rsa_key_root_name.txt.enc

# Generate rsa
sudo openssl genrsa -out $priv_key
sudo openssl rsa -in $priv_key -pubout -out $pub_key

sudo cp default_root.txt .mysql_secrets
sudo cp default_admin.txt .mysql_secrets

# TODO: Find out a way to check the status of mysql.
echo "Wait until your mysql server fully starts..."
sleep 45

# Generate root password & store it.
sudo openssl rand -base64 32 | sudo openssl rsautl -encrypt -pubin -inkey $pub_key -out $encrypted_pass

pepper=$(sudo openssl rand -base64 32);

#pass_str=$(sudo docker exec mysql_server bash -c "mysql --defaults-extra-file=/.mysql_secrets/default_root.txt")

# Alter default admin user priviledge
# sudo docker exec mysql_server \
# bash -c "mysql --defaults-extra-file=/.mysql_secrets/default_root.txt \
#  --connect-expired-password -e \"REVOKE ALL PRIVILEGES ON *.* FROM 'admin'@'%'; GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, RELOAD, PROCESS, FILE, REFERENCES, INDEX, ALTER, SHOW DATABASES, CREATE TEMPORARY TABLES, LOCK TABLES, CREATE VIEW, EVENT, TRIGGER, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, CREATE USER, EXECUTE ON *.* TO 'admin'@'%' REQUIRE NONE WITH GRANT OPTION MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;\""

# Change root password.
sudo docker exec mysql_server \
bash -c "mysql --defaults-extra-file=/.mysql_secrets/default_root.txt \
--connect-expired-password -e  \"ALTER USER 'root'@'%' IDENTIFIED WITH mysql_native_password \
BY '"$(sudo openssl rsautl -decrypt -inkey $priv_key -in $encrypted_pass)"';\""

# Generate admin password.
sudo openssl rand -base64 32 | sudo openssl rsautl -encrypt -pubin -inkey $pub_key -out $encrypted_admin_pass

# Now change admin password.
sudo docker exec mysql_server \
bash -c "mysql --defaults-extra-file=/.mysql_secrets/default_admin.txt \
 -e \"ALTER USER 'admin'@'%' IDENTIFIED WITH mysql_native_password \
BY '"$(sudo openssl rsautl -decrypt -inkey $priv_key -in $encrypted_admin_pass)"';\""

# Create password for a table manager.
# sudo openssl rand -hex 48 | sudo openssl rsautl -encrypt -pubin -inkey $pub_key -out $encrypted_table_client_pass

# This script is for testing purpose.
sudo openssl rand -base64 32 | sudo openssl rsautl -encrypt -pubin -inkey $pub_key -out $encrypted_table_client_pass

sudo openssl rand -base64 32 | sudo openssl rsautl -encrypt -pubin -inkey $pub_key -out $encrypted_table_admin_pass

# After reading all files, the program wiil change permission.
# Only root can read and all of the files below are read-only states.
sudo find .mysql_secrets -name '*.pem' -o -name '*.enc' | while read p; do sudo chmod 400 $p; done
sudo find .mysql_secrets -name '*.txt' | while read p; do sudo rm $p; done

if [ ! -d decrypt_script ]; then
    mkdir decrypt_script
    chmod 700 decrypt_script
fi

# To decrypt your password, just invoke this command with root privilege.
# You do need to perform this command carefully, otherwise your password will be revealed 
# in such a way that anyone can see it.
echo "openssl rsautl -decrypt -inkey '$priv_key' -in '$encrypted_pass'" > decrypt_script/root_decrypt.sh
echo "openssl rsautl -decrypt -inkey '$priv_key' -in '$encrypted_pass' | xclip -sel clip" > decrypt_script/root_decrypt_clip.sh

echo "openssl rsautl -decrypt -inkey '$priv_key' -in '$encrypted_admin_pass'" > decrypt_script/admin_decrypt.sh
echo "openssl rsautl -decrypt -inkey '$priv_key' -in '$encrypted_admin_pass' | xclip -sel clip" > decrypt_script/admin_decrypt_clip.sh

echo "openssl rsautl -decrypt -inkey '$priv_key' -in '$encrypted_table_client_pass'" > decrypt_script/timesheet_manage_decrypt.sh
echo "openssl rsautl -decrypt -inkey '$priv_key' -in '$encrypted_table_client_pass' | xclip -sel clip" > decrypt_script/timesheet_manage_decrypt_clip.sh

echo "openssl rsautl -decrypt -inkey '$priv_key' -in '$encrypted_table_admin_pass'" > decrypt_script/timesheet_admin_decrypt.sh
echo "openssl rsautl -decrypt -inkey '$priv_key' -in '$encrypted_table_admin_pass' | xclip -sel clip" > decrypt_script/timesheet_admin_decrypt_clip.sh

find decrypt_script -name '*.sh' | while read p; do chmod 500 $p; done
echo "root_decrypt.sh, root_decrypt_clip.sh, admin_decrypt.sh and admin_decrypt_clip.sh are created for your password decryption."
echo "All of the created scripts need to be run with root privileges."
echo "All setups done!"

# TODO: Put a script that creates a database for a user.
sudo docker exec mysql_server \
bash -c "mysql -uroot -hmysql_server -p"$(sudo decrypt_script/root_decrypt.sh)" \
 -e \"REVOKE ALL PRIVILEGES ON *.* FROM 'admin'@'%'; GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, RELOAD, PROCESS, FILE, REFERENCES, INDEX, ALTER, SHOW DATABASES, CREATE TEMPORARY TABLES, LOCK TABLES, CREATE VIEW, EVENT, TRIGGER, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, CREATE USER, EXECUTE ON *.* TO 'admin'@'%' REQUIRE NONE WITH GRANT OPTION MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;\""

# TODO: Not to generate a password directly.
# Create a client that can see a manager. 
sudo docker exec mysql_server \
bash -c "mysql -uroot -hmysql_server -p"$(sudo decrypt_script/root_decrypt.sh)" \
 -e \"CREATE USER IF NOT EXISTS 'time_sheet_client' IDENTIFIED WITH mysql_native_password BY '"$(sudo openssl rsautl -decrypt -inkey $priv_key -in $encrypted_table_client_pass)"'\""

# Create a admin for `time sheet` database.
sudo docker exec mysql_server \
bash -c "mysql -uroot -hmysql_server -p"$(sudo decrypt_script/root_decrypt.sh)" \
 -e \"CREATE USER IF NOT EXISTS 'time_sheet_admin' IDENTIFIED WITH mysql_native_password BY '"$(sudo openssl rsautl -decrypt -inkey $priv_key -in $encrypted_table_admin_pass)"'\""

# Replace sample with generated passwords. 
cat www/.secret/.config_sample.php | \
sed "s|_____time_sheet_pass_____|"$(sudo openssl rsautl -decrypt -inkey $priv_key -in $encrypted_table_client_pass)"|g" | \
sed "s|_____pepper_string_____|"$pepper"|" > www/.secret/.config.php

sudo docker exec mysql_server \
bash -c "mysql -uroot -hmysql_server -p"$(sudo decrypt_script/root_decrypt.sh)" < /create_database.sql" 