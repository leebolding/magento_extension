#!/bin/bash

TARGET=$1;
DIRECTION=$2;

DIRS="app/code/local/TVPage/ app/design/adminhtml/default/default/template/tvpconnect/";
FILES="app/etc/modules/TVPage_Connect.xml app/design/adminhtml/default/default/layout/tvpconnect.xml";

if [ ! -d "$TARGET" ]; then
  echo "Directory doesn't exist";
  exit 0;

fi

# Start the copy
for i in $DIRS
  do
    if [ "$DIRECTION" ]; then
      echo "Copying $i $TARGET/$i";
      cp -a "$i" "$TARGET/$i";
    else 
      echo "Copying $TARGET/$i $i";
      cp -a "$TARGET/$i" "$i";
    fi;
done;

# Start the copy
for f in $FILES
  do
    if [ "$DIRECTION" ]; then
      echo "Copying $TARGET/$f $f";
      cp "$f" "$TARGET/$f";
      
    else 
      echo "Copying $TARGET/$f $f";
      cp "$TARGET/$f" "$f";
    fi;

done;