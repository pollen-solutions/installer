#!/usr/bin/env bash
set -e

if [ ! -z "$USER" ]; then
    usermod -u ${USER} pollen
fi

#if [ ! -d /.composer ]; then
#    mkdir /.composer
#fi
#
#chmod -R ugo+rw /.composer

exec gosu ${USER} "$@"
