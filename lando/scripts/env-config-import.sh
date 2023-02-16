#!/usr/bin/env bash

# This file is configured in .lando.yml. Run: lando env-config-import.
# It will run database updates, import config and clear caches
# on the chosen environment.

NORMAL="\033[0m"
RED="\033[31m"
YELLOW="\033[32m"
ORANGE="\033[33m"
PINK="\033[35m"
BLUE="\033[34m"

echo
echo -e "${RED}WARNING: This will affect a remote Acquia environment."
echo
echo -e "${ORANGE}Are you sure you want to run database updates, import config and clear caches on a remote Acquia environment? (y/n):${BLUE}"
echo
read REPLY
if [[ ! "$REPLY" =~ ^[Yy]$ ]]
then
  exit 1
fi

echo
echo -e "${ORANGE}Enter the environment to import on (dev/test/prod):${BLUE}"
echo
read REPLY_ENV
if [ "$REPLY_ENV" = "prod" ] || [ "$REPLY_ENV" = "test" ] || [ "$REPLY_ENV" = "dev" ]
then
  if [ "$REPLY_ENV" = "prod" ]
  then
    echo
    echo -e "${ORANGE}Are you sure you want to run database updates, import config and clear caches on prod? This will wipe out any config changes that are currently on prod. (y/n):${BLUE}"
    echo
    read REPLY_CONFIRM
    if [[ ! "$REPLY_CONFIRM" =~ ^[Yy]$ ]]
    then
      exit 1
    fi
  fi

  echo
  echo -e "${YELLOW}Running database updates, importing config and clearing caches on $REPLY_ENV environment...${NORMAL}"
  echo

  drush @dovelewis."$REPLY_ENV" updb -y

  echo
  echo -e "${YELLOW}Importing config...${NORMAL}"
  echo

  drush @dovelewis."$REPLY_ENV" cim sync -y

  echo
  echo -e "${YELLOW}Clearing caches...${NORMAL}"
  echo

  drush @dovelewis."$REPLY_ENV" cr

  echo
  echo -e "${YELLOW}Running drush status so you know what's up...${NORMAL}"
  echo

  drush @dovelewis."$REPLY_ENV" status
else
  echo
  echo -e "${RED}Please provide a valid env. Choose between 'dev', 'test', or 'prod'.${NORMAL}"
  echo
  exit 1
fi
