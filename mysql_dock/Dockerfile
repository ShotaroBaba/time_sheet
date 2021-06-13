FROM mysql:5.7

RUN apt-get update && apt-get install -y curl bash 
#TODO: Enable an automatical-creation of a database on mysql-dock during the system setup.
COPY create_database.sql / 

EXPOSE 3306