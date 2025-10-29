#!/bin/bash
MyUser=root
MyPassword=123456
DbName=memorial
current_date=$(date +"%d-%m-%Y")
mysqldump  --password=$MyPassword --user=$MyUser  $DbName > memDump-$current_date.sql
