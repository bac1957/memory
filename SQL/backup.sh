#!/bin/bash
MyUser=root
MyPassword=123456
DbName=nfl
current_date=$(date +"%d-%m-%Y")
mysqldump  --password=$MyPassword --user=$MyUser  $DbName > nflDump-$current_date.sql
