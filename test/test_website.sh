#!/bin/bash

cat test_sql.sql | docker exec -i mysql_server bash -c 'cat - > test_sql.sql' 