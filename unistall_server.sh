read -p "WARNING!!: If you uninstall the server, the server data will lose and can never be retrieved. It is strongly advised that you make sure that you back up your own data before you proceed: " yn 

case $yn in
    [Yy])
        break;;
    [Nn])
        exit 0;;
    *) 
        echo "Invalid input. Please type y/n. Abort."
        exit 1;;
esac



rm -rf config data logs test_data .mysql_secrets mysql_secrets decrypt_script

echo "All docker containers, MySQL and server data has been deleted."