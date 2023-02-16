#!/usr/bin/env bash

# This file is configured in .lando.yml. Run: lando db-pull.
# It will pull the production database from a remote Pantheon environment
# and replace your local database.

NORMAL="\033[0m"
RED="\033[31m"
YELLOW="\033[32m"
ORANGE="\033[33m"
PINK="\033[35m"
BLUE="\033[34m"

echo
echo -e "${RED}WARNING: This will wipe out your local database."
echo
echo -e "${ORANGE}Do you want to pull the database from a remote Pantheon environment and replace your local database? (y/n)${BLUE}"
echo
read REPLY
if [[ "$REPLY" =~ ^[NnNono]$ ]]
then
  exit 1
fi

echo
echo -e "${ORANGE}Enter your Pantheon account email. You will need a machine token configured in your Pantheon account for this to work:${BLUE}"
echo
read REPLY_EMAIL
echo

terminus auth:login --email "$REPLY_EMAIL"

echo
echo -e "${ORANGE}Which env should we pull the database or files from? (dev/test/live):${BLUE}"
echo
read REPLY_ENV
if [ -n "$REPLY_ENV" ]
then
  if [ "$REPLY_ENV" = "dev" ] || [ "$REPLY_ENV" = "test" ] || [ "$REPLY_ENV" = "live" ]
  then
    echo
    echo -e "${YELLOW}Pulling $REPLY_ENV database... You will be told we're pulling more than the database but that's a lie."
    echo
    echo -e "This may appear to be frozen but it's not. Promise...${NORMAL}"
    echo

    terminus backup:create solar4schools."$REPLY_ENV" --element=db
    BACKUP_URL=$(terminus backup:get solar4schools."$REPLY_ENV" --element=db)

    echo
    echo -e "${YELLOW}Pulling database from $BACKUP_URL...${NORMAL}"
    echo

    DATE=$(date +'%Y-%m-%d')
    cd /app/db
    curl --compressed -o "$REPLY_ENV"_"$DATE".sql.gz "$BACKUP_URL"
    yes n | gunzip "$REPLY_ENV"_"$DATE".sql.gz -f

#        mysql -h database -P 3306 -u cebf -pcebf -D cebf -e "drop SCHEMA cebf;"
#        mysql -h database -P 3306 -u cebf -pcebf -D cebf -e "create SCHEMA cebf;"

    echo
    echo -e "${YELLOW}Importing database...${NORMAL}"
    echo

    pv /app/db/"$REPLY_ENV"_"$DATE".sql | mysql -h database -P 3306 -u cebf -pcebf -D cebf

    rm /app/db/"$REPLY_ENV"_"$DATE".sql
  else
    echo
    echo -e "${RED}You must enter either dev, test or live.${NORMAL}"
    exit 1
  fi
else
  echo
    echo -e "${RED}You must enter either dev, test or live.${NORMAL}"
  exit 1
fi
