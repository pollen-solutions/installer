#!/usr/bin/env bash
set -e

if [ ! -z "$USER" ]; then
    usermod -u ${USER} pollen
fi

exec gosu ${USER} "$@"
