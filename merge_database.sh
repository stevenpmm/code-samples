#!/bin/bash
# FREQUENCY: AUTO
# DESC:
# MERGES A DATABASE OF THE SAME STRUCTURE WITH THE MASTER DATABASE
# Author: smatthews, Date: 18/09/2017
# 0 2 * * * set up cron here
# PARAMETERS - 3 <SLAVE NAME> <USERNAME> <PASSWORD>

# DETAILS: most of the sql queries are executed by php scripts (get_db_ref.php and prep_data.php)
# which are called within the bash script and return values to it; Error checking is rigorous.
# The script relies upon a "master" database that has been set up MANUALLY in advance (see documentation)



# check parameters
if [ "$#" -ne 3 ] 
then
echo 'USAGE: '$0 '<SLAVE NAME> <USERNAME> <PASSWORD>'
exit 1
fi

curtime=$(date '+%d-%m-%Y_%H:%M:%S')

db=" -u"$2" -p"$3" "

# check for empty databases and db connection credentials XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
a=$( /usr/bin/mysql $db -e "SELECT COUNT(DISTINCT table_name) AS num FROM information_schema.columns WHERE table_schema = 'master';")

if [ $? -ne 0  ] 
then
echo '['$curtime'] Error: failed database connection!'
exit 1
fi

b=$( /usr/bin/mysql $db -e "SELECT COUNT(DISTINCT table_name) AS num FROM information_schema.columns WHERE table_schema = '$1';")

a=${a:4} # capture numeric output
b=${b:4}

if [ "$a" == 0 ]
then
echo '['$curtime'] Error: master database is empty or does not exist!'
exit 1
fi

if [ "$b" == 0 ] 
then
echo '['$curtime'] Error: slave "'$1'" database is empty or does not exist!'
exit 1
fi

#check db structures - slave cannot have more tables than master ?
if [ "$b" -gt "$a" ]
then
echo '['$curtime'] Error: slave cannot have more tables than master!'
exit 1
fi

#exit 1

# back up BOTH databases  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
$(mysql $db -e "CREATE DATABASE IF NOT EXISTS master_bak;")
/usr/bin/mysqldump master > master.sql
/usr/bin/mysql master_bak < master.sql
rm master.sql

ctime=$(date '+%d_%m_%Y_%H_%M_%S')
temp="$1"_bak_$ctime
$(mysql $db -e "CREATE DATABASE IF NOT EXISTS "$temp )
/usr/bin/mysqldump "$1"  > slave.sql
/usr/bin/mysql  "$temp" < slave.sql
rm slave.sql

# XXXXXXXXXXXXXXXXXXXXXXXXXX MAIN PRCESSING STARTS HERE  XXXXXXXXXXX

# creates new record for DB "$1" in master.databases table and returns the db_ref value 
db_ref=$(/usr/bin/php  get_db_ref.php "$1" "$2" "$3") 

if [ $? -ne 0 ]
       then 
            echo '['$curtime'] Error with script get_db_ref.php'
           exit 1
    fi


if [ "$db_ref" -eq "-2" ] 
then
echo '['$curtime'] Error: slave database "'$1'" does not exist! (ref:script get_db_ref.php)'
exit 1
fi

if [ "$db_ref" -eq "-1" ] 
then
echo '['$curtime'] Error: slave database "'$1'" already imported into master (ref:script get_db_ref.php)'
exit 1
fi


if [ "$db_ref" -eq "0" ]
then
 echo '['$curtime'] Error: var db_ref is empty in script get_db_ref.php'
exit 1
fi


# prep the data for the current slave: "$1"
/usr/bin/php  prep_data.php "$1" "$db_ref" "$2" "$3"
 
  if [ $? -ne 0 ]
     then
          echo '['$curtime'] Error with script prep_data.php'
         exit 1
  fi

# export slave data
/usr/bin/mysqldump --no-create-info "$1" > slave_data.sql

if [ $? -ne 0 ] 
     then
          echo '['$curtime'] Error with export slave data'
         exit 1
  fi 

# Import to master - The Merge !
/usr/bin/mysql master < slave_data.sql

if [ $? -ne 0 ]
     then
          echo '['$curtime'] Error with import to master (the merge)'
         exit 1
  fi

 echo '['$curtime'] Merge Successfull for' "$1"

# clean up
# $(mysql $db -e "DROP DATABASE master_bak;")
rm slave_data.sql;
exit 0


