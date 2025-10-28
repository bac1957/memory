#/bin/bash
current_date=$(date +"%d-%m-%Y")
tar -czf mem-$current_date.tgz *.php .htaccess SQL scripts views controllers models commands web tests config widgets migrations vendor/yiisoft/yii2/base/Application.php 
