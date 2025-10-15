#!/bin/sh

LOGDIR="/var/www/maf/var/log"
APP="/var/www/maf/bin/console"
DAY=`date +%a`

# Fix the permissions that never stick:
# sudo setfacl -dR -m u:www-data:rwX -m u:maf:rwX ~/symfony/app/cache ~/symfony/app/logs ~/symfony/app/spool
# sudo setfacl -R -m u:www-data:rwX -m u:maf:rwX ~/symfony/app/cache ~/symfony/app/logs ~/symfony/app/spool

php $APP maf:process:activities 2>&1 > $LOGDIR/hourly-$DAY.log
php $APP maf:process:familiarity -t 2>&1 >> $LOGDIR/hourly-$DAY.log
php $APP maf:process:travel -t 2>&1 >> $LOGDIR/hourly-$DAY.log
php $APP maf:process:spotting -t 2>&1 >> $LOGDIR/hourly-$DAY.log
php $APP maf:run -v -t -d hourly 2>&1 >> $LOGDIR/hourly-$DAY.log
php $APP dungeons:hourly -d 2>&1 >> $LOGDIR/hourly-$DAY.log
echo "----- hourly done -----" >> $LOGDIR/hourly-$DAY.log
