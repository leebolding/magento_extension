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
    if [ ! -d "$TARGET/$i" ]; then
      echo "Directory: $TARGET/$i doesn't exist";
      exit 0;
    fi

    if [ ! -d "$i" ]; then
      echo "Creating dir $i";
      mkdir -p "$i";
    fi
done;

for f in $FILES
  do
    if [ ! -f "$TARGET/$f" ]; then
      echo "File: $TARGET/$i doesn't exist";
      exit 0;
    fi 
    
    DIR=`dirname $f`;
    if [ ! -d "$DIR" ]; then
      echo "Creating dir $DIR";
      mkdir -p "$DIR";
    fi
done

# Start the copy
for i in $DIRS
  do
    echo "Copying $TARGET/$i $i";
    cp -a "$TARGET/$i" "$i";
done;

# Start the copy
for f in $FILES
  do
    echo "Copying $TARGET/$f $f";
    cp "$TARGET/$f" "$f";
done;