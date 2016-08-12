#!/bin/sh
psql -U postgres -f ./create_db.sql
psql -U postgres provisioning < ./provisioning.bak
