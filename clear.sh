#!/bin/bash

TARGET=$1;

DIRS="app/code/local/TVPage/ app/design/adminhtml/default/default/template/tvpconnect/";
FILES="app/etc/modules/TVPage_Connect.xml app/design/adminhtml/default/default/layout/tvpconnect.xml";

if [ ! -d "$TARGET" ]; then
  echo "Directory doesn't exist";
  exit 0;
fi

for i in $DIRS
  do
    if [  -d "$TARGET/$i" ]; then
      echo "Removing directory: $TARGET/$i";
      rm -rf "$TARGET/$i";
    fi
done;

for f in $FILES
  do
    if [  -f "$TARGET/$f" ]; then
      echo "Removing File: $TARGET/$f";
      rm -f "$TARGET/$f";
    fi 
done