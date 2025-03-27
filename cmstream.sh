#!/bin/bash

# this script is used to run command php in container docker with docker-compose

# color liste
START='\033[0;'
END='\033[0m'

BLUE='34m'
GREEN='32m'
RED='31m'
PURPLE='35m'
ORANGE='33m'

if [ -n "$1" ]; then
    docker-compose exec php php /var/www/bin/$1.php $2 $3 $4 $5 $6 $7 $8 $9 $10 $11 $12 $13 $14 $15 $16
else
    printf "${START}${BLUE}Commands:${END}\n"
    printf "${START}${GREEN}- makeMigration${END}\n"
    printf "${START}${GREEN}- doMigation${END}\n"
    printf "${START}${RED}- makeRoute${END}\n"
    printf "${START}${RED}- reset${END}\n"
    printf "${START}${BLUE}- dataFixture${END}\n"
    printf "${START}${PURPLE}- addContent${END}\n"
    printf "${START}${ORANGE}- makeSitemap${END}\n"
fi
