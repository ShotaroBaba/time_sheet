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

sudo cp default_root.txt .mysql_secrets
sudo cp default_admin.txt .mysql_secrets

pub_key=$key_dir/pub_$rsa_key_root_name.pem
priv_key=$key_dir/priv_$rsa_key_root_name.pem
encrypted_pass=$pass_dir/root_pass_$rsa_key_root_name.txt.enc
encrypted_admin_pass=$pass_dir/admin_pass_$rsa_key_root_name.txt.enc

# Generate rsa
sudo openssl genrsa -out $priv_key
sudo openssl rsa -in $priv_key -pubout -out $pub_key

# TODO: Find out a way to check the status of mysql.
echo "Wait until your mysql server fully starts..."
sleep 45

# Generate root password & store it.
sudo openssl rand -base64 32 | sudo openssl rsautl -encrypt -pubin -inkey $pub_key -out $encrypted_pass
# sudo openssl rsautl -decrypt -inkey $priv_key -in $encrypted_pass

#pass_str=$(sudo docker exec mysql_server_shotaro bash -c "mysql --defaults-extra-file=/.mysql_secrets/default_root.txt")


# Change root password.
sudo docker exec mysql_server_shotaro \
bash -c "mysql --defaults-extra-file=/.mysql_secrets/default_root.txt \
--connect-expired-password -e  \"ALTER USER 'root'@'%' IDENTIFIED WITH mysql_native_password \
BY '"$(sudo openssl rsautl -decrypt -inkey $priv_key -in $encrypted_pass)"';\""

# Generate admin password.
sudo openssl rand -base64 32 | sudo openssl rsautl -encrypt -pubin -inkey $pub_key -out $encrypted_admin_pass

# Now change admin password.
sudo docker exec mysql_server_shotaro \
bash -c "mysql --defaults-extra-file=/.mysql_secrets/default_admin.txt \
 -e \"ALTER USER 'admin'@'%' IDENTIFIED WITH mysql_native_password \
BY '"$(sudo openssl rsautl -decrypt -inkey $priv_key -in $encrypted_admin_pass)"';\""

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
find decrypt_script -name '*.sh' | while read p; do chmod 500 $p; done
echo "root_decrypt.sh, root_decrypt_clip.sh, admin_decrypt.sh and admin_decrypt_clip.sh are created for your password decryption."
echo "All of the created scripts need to be run with root privileges."
echo "All setups done!"