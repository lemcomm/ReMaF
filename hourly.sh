#!/bin/sh

LOGDIR="/var/www/maf/var/log"
APP="/var/www/maf/bin/console"
DAY=$(date +%a)

# Fix the permissions that never stick:
# sudo setfacl -dR -m u:www-data:rwX -m u:maf:rwX ~/symfony/app/cache ~/symfony/app/logs ~/symfony/app/spool
# sudo setfacl -R -m u:www-data:rwX -m u:maf:rwX ~/symfony/app/cache ~/symfony/app/logs ~/symfony/app/spool

php $APP maf:process:activities > $LOGDIR/hourly-$DAY.log 2>&1
php $APP maf:process:familiarity -t >> $LOGDIR/hourly-$DAY.log 2>&1
php $APP maf:process:travel -t >> $LOGDIR/hourly-$DAY.log 2>&1
php $APP maf:process:spotting -t >> $LOGDIR/hourly-$DAY.log 2>&1
php $APP maf:run -v -t -d hourly >> $LOGDIR/hourly-$DAY.log 2>&1
php $APP dungeons:hourly -d >> $LOGDIR/hourly-$DAY.log 2>&1
echo "----- hourly done -----" >> $LOGDIR/hourly-$DAY.log
