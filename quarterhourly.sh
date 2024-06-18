#!/bin/sh

LOGDIR="/var/www/maf/var/log"
APP="/var/www/maf/bin/console"
DAY=`date +%a%H`

# Fix the permissions that never stick:
# sudo setfacl -dR -m u:www-data:rwX -m u:maf:rwX ~/symfony/app/cache ~/symfony/app/logs ~/symfony/app/spool
# sudo setfacl -R -m u:www-data:rwX -m u:maf:rwX ~/symfony/app/cache ~/symfony/app/logs ~/symfony/app/spool

php $APP --env=prod maf:process:battles -t 5 2>&1 > $LOGDIR/quarterhourly-$DAY.log
php $APP --env=prod maf:mail 2>&1 >> $LOGDIR/quarterhourly-$DAY.log
