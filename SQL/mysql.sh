#!/bin/bash
mysql --password=123456 --user=root --execute="source $1;" mysql
