#!/usr/bin/env bash

# This file is configured in .lando.yml. Run: lando files-pull.
# It will pull any new files from a remote pantheon environment.

NORMAL="\033[0m"
RED="\033[31m"
YELLOW="\033[32m"
ORANGE="\033[33m"
PINK="\033[35m"
BLUE="\033[34m"

echo
echo -e "${ORANGE}Do you want to download the files directory from a remote Pantheon environment? (y/n)${BLUE}"
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
echo -e "${ORANGE}Which env should we pull files from? (dev/test/live):${BLUE}"
echo
read REPLY_ENV
if [ -n "$REPLY_ENV" ]
then
  if [ "$REPLY_ENV" = "dev" ] || [ "$REPLY_ENV" = "test" ] || [ "$REPLY_ENV" = "live" ]
  then
    echo
    echo -e "${YELLOW}Pulling $REPLY_ENV files... You will be told we're pulling more than the files but that's a lie."
    echo
    echo -e "This may appear to be frozen but it's not. Promise...${NORMAL}"
    echo

    terminus backup:create solar4schools."$REPLY_ENV" --element=files
    BACKUP_URL=$(terminus backup:get solar4schools."$REPLY_ENV" --element=files)

    echo
    echo -e "${YELLOW}Pulling files from $BACKUP_URL...${NORMAL}"
    echo

    DATE=$(date +'%Y-%m-%d')
    cd /app/sites/default
    curl --compressed -o "$REPLY_ENV"_"$DATE".tar.gz "$BACKUP_URL"
    yes n | tar xvzf "$REPLY_ENV"_"$DATE".tar.gz
    rm -rf files
    mv files old_files
    mv files_"$REPLY_ENV" files
    rm -rf old_files
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
