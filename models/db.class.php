<?php
/* vim: set expandtab tabstop=3 shiftwidth=3: */

/*
Fluent - a suite of tools for the management of VoIP networks
Copyright (C) 2010 trav dot colbert at gmail dot com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**************** DB Object Class *****************
 *
 * Provides singleton access to a DB
 *
 */

class db {
    // the DB handle
    protected $dbhandle;

    function __construct($dbstring) {
        $this->dbhandle = $this->get_dbhandle($dbstring);
    }

    protected function __clone() {
        // so other objects can't clone a message object
    }

    /* * * * * * * get_dbhandle * * * * * * * *
     * Creates a handle to the DB based on the string
     * passed to it
     */
    protected function get_dbhandle($dbstring) {
        return pg_connect($dbstring);
    }

    /* * * * * * * get * * * * * * * *
     * public method for querying
     * all objects should support one or more of the
     * 4 HTTP verbs GET, POST, PUT, DELETE
     */
    public function get($query) {
        $resultset = $this->query($query);
        return $resultset;
    }

    /* * * * * * * query * * * * * * * *
     * protected method for querying
     * internal to the object
     */
    protected function query($query) {
        $resultset = pg_query($query);
        if(pg_num_rows($resultset)<1) {
            return false;
        } else {
            return $resultset;
        }
    }
}
