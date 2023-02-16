#!/usr/bin/env bash

# This file is configured in .lando.yml. Run: lando drupal-config-import.
# It will run database updates, imports config and clear caches
# on your local Lando environment.

NORMAL="\033[0m"
RED="\033[31m"
YELLOW="\033[32m"
ORANGE="\033[33m"
PINK="\033[35m"
BLUE="\033[34m"

echo
echo -e "${ORANGE}Are you sure you want to run database updates, import config and clear caches on your local Lando environment? (y/n):${BLUE}"
echo
read REPLY
if [[ ! "$REPLY" =~ ^[Yy]$ ]]
then
  exit 1
fi

echo
echo -e "${YELLOW}Running database updates, config import and clearing caches on local Lando environment...${NORMAL}"
echo
drush updb -y
echo
echo -e "${YELLOW}Running config import...${NORMAL}"
echo
drush cim sync -y
echo
echo -e "${YELLOW}Clearing caches...${NORMAL}"
echo
drush cr
echo
echo -e "${YELLOW}Running drush status so you know what's up...${NORMAL}"
echo
drush status

#/app/lando/scripts/config-local.sh
