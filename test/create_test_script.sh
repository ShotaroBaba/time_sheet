#!/bin/bash

cat test_main_template.pl | \
sed "s|_____time_admin_pass_____|"$(cd .. && sudo decrypt_script/timesheet_admin_decrypt.sh)"|g" > test_main.pl
