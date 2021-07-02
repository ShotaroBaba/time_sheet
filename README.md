# Time Sheet
A website for recording employee's timestamp based on Docker, MySQL and PHP. User can also manage MySQL server using phpMyAdmin.

# Prerequisite
Your operating system needs to be a Ubuntu (>=18.04 LTS), where the developed servers have been tested, CentOS (>= 7.4), and other Unix-like OS. Currently, the developed server has not been tested under CentOS or the other Unix-like OS.

Before proceeding to the installation, make sure that you have installed the following packages on your system:
```
Docker (>=19.03.7)
Docker-compose (>=1.24.1)
```
# Installation
First download repository by running this command

```bash
git clone https://github.com/ShotaroBaba/time_sheet.git
```

To install servers, invoke the following command in the root of project directory:
```bash
cd time_sheet
./create_server.sh
```

# Root & Other Passwords Retrieval

During the installation, the passwords of root, admin and time_sheet_admin MySQL accouts are automatically generated. To retrieve one of these password, just run one of the comamnds below in the root of the project directory:

To retrive MySQL 'root' password:
```bash
decrypt_script/root_decrypt.sh
```
Alternatively, you can run this command and copy the password to your clipboard (xclip needs to be installed):
```bash
decrypt_script/root_decrypt.sh | xclip -sel clip
```
#
To retrieve MySQL 'admin' password:
```bash
decrypt_script/root_decrypt.sh
```
Alterantive option
```bash
decrypt_script/root_decrypt.sh | xclip -sel clip
```
#
To retrieve MySQL 'timesheet_admin' password:
```bash
decrypt_script/timesheet_admin_decrypt.sh
```
Alterantive option
```bash
decrypt_script/timesheet_admin_decrypt.sh | xclip -sel cli
```
# Managing Server
To shutdown server after the installation, invoke the below command:
```bash
./shutdown_server.sh
```

To re-run your server:
```bash
./run_server.sh
```

To stop your server:
```bash
docker-compose down
```

To uninstall your server:
```bash
./uninstall_server.sh
```
Note that you cannot retrieve your server data after the uninstallation.

# Accessing to the Server
You can access to the webpage by inputting the following address:
```
localhost:59111
```
#
To go to phpMyAdmin, put the following url
```bash
localhost:55553
```

If you want to change the port numbers that are used to access to the website, just edit the variable PHPMYADMIN_PORT (DEFAULT: 55553) for phpMyAdmin server and the variable HTTP_PORT (DEFAULT: 59111) for PHP server that provides access to time sheet website. 

# To Do
- Create backup script
- Check and remove all bugs
- Create an install script that enables https access
- Adjust the apperance of the website for usability
- Throughly check all systems and servers, including README.md
- Create Japanese Version README.md as well as web server
# Source
  Timesheet favicon.ico [image](https://publicdomainvectors.org/en/free-clipart/Paper-sheet/59351.html) (https://publicdomainvectors.org/en/free-clipart/Paper-sheet/59351.html)
