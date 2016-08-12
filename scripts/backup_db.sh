#!/bin/sh
pg_dump -U postgres provisioning > ./provisioning.bak.sql
