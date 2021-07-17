#!/bin/bash

cat test_main.pl | docker exec -i mysql_server bash -c "cat - > test_main.pl && \
perl test_main.pl && \
cat test_sql.sql"; 