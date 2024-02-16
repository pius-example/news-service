#!/bin/bash

ESC_SEQ="\x1b["
COL_RESET=$ESC_SEQ"39;49;00m"
COL_RED=$ESC_SEQ"0;31m"
COL_GREEN=$ESC_SEQ"0;32m"
COL_YELLOW=$ESC_SEQ"0;33m"

CHANGED_FILES_BEFORE=$(git status --porcelain | sed s/^...//)
php artisan openapi:generate-server
CHANGED_FILES=$(git status --porcelain | sed s/^...//)

if [ "$CHANGED_FILES_BEFORE" == "$CHANGED_FILES" ]; then
    echo "Okay"
    exit 0
else
    printf "$COL_RED%s$COL_RESET\r\n" "Generate server check failed."
    exit 1
fi
