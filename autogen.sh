#!/bin/sh
# $Id: autogen.sh 671 2004-02-26 06:50:11Z siegmar $

echo "$@" | grep -i -e '--help' > /dev/null
if [ $? -eq 0 ]
then
    echo -e "Usage:\n"
    echo -e "$0 [OPTION]...\n"
    echo "Options:"
    echo "--help     Show this help"
    echo "--clean    Cleanup generated files"
    echo
    echo "Default action is to generate files from .in inputs"
    echo
    exit
fi

cd $(dirname $0)

if [ ! -r autogen.conf ]
then
    echo "Couldn't read autogen.conf - you should create it by copying autogen.conf.orig"
    exit
fi

awk 'BEGIN { FS="[ \t]*=[ \t]*" }{ print "s|@" $1 "@|" $2 "|g"}' autogen.conf > autogen.sed

echo "$@" | grep -i -e '--clean' > /dev/null
if [ $? -eq 0 ]
then
    for f in $(find . -type f -name "*.in")
    do
        f2=$(echo $f | sed 's/\.in$//')
        echo "Cleaning up (deleting) $f2..."
        test -e $f2 && rm $f2
    done
else
    for f in $(find . -type f -name "*.in")
    do
        f2=$(echo $f | sed 's/\.in$//')
        echo "Generating $f2 (from $f)..."
        sed -f autogen.sed $f > $f2
        test -x /usr/bin/stat && chmod $(stat -c %a $f) $f2
    done
fi

rm autogen.sed
