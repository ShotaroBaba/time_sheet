use strict;
use warnings;
use utf8;
use open ":utf8";
use Time::Piece;
use Time::Seconds; 
use POSIX qw(strftime);
my $lim=2001;

# Open file 
my $filename = "test_sql.sql";
open(FH, '>', $filename) or die $!;

my @charset = ('0' ..'9', 'A' .. 'Z', 'a' .. 'z');
my $password = join ('' => map $charset[rand @charset], 1 .. 30);

# Generate random password for a user.
# A plain password (without hash, salt and pepper) is provided for testing purpose.

# Insert employee if it does not exist.
my $sql_insert_employee="INSERT INTO `occupation` (`occupation_type`, `wage`,`issue_time`) VALUES
          ('test',3000,'".((localtime))->strftime("%Y-%m-%d %H:%M:%S")."');\n\n";

my $sql_insert_employee_type="";

# This password is used only for testing purpose!
my $test_pass="_____time_admin_pass_____";
my $employee_type_id_sql='SELECT \`employee_type_id\` FROM \`occupation\` WHERE \`occupation_type\` = \'test\'';

# SELECT employee ID & user ID for testing.
my $employee_id = `docker exec -i mysql_server bash -c 'cat - | mysql -utime_sheet_admin -p$test_pass -Dtime_sheet -N 2> /dev/null' << EOF 
$employee_type_id_sql
EOF`;

chomp($employee_id);
print $employee_id;

# Create a test employee type for testing
print FH "INSERT INTO `user` (`first_name`, `middle_name`,`last_name`,`address`,
    `phone_number`,`employee_type_id`,`email`,`state`
    ) VALUES (\'test\',NULL,\'test\',\'test\',\'test\',$employee_id,`test\@test.example.com`,`state`);
    ";

print FH "\n\n";

# Now insert all user's information.
print FH "INSERT INTO `time_sheet` (`user_id`, `employee_type_id`, `time`, `state`) VALUES";

my $i = 0;

while ($i < $lim) {
    my $status = $i % 2 == 0 ? "'working'" :  "'left_work'";
    my $work_time = ((localtime)+ONE_HOUR*$i)->strftime("%Y-%m-%d %H:%M:%S");
    print FH "(2,$employee_id,$work_time,$status)\n";
    $i+=1;
}

close(FH);
