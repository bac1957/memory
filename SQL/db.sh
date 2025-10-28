#!/bin/bash
if [ ! -z $1 ]; then
  mysql --password=123456 --user=root --host=127.0.0.1 --execute="source $1;" memorial
else
  mysql --password=123456 --user=root --host=127.0.0.1 mysql
fi