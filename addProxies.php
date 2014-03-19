<?php
/**
* Adds proxies to the proxy table
*
* By Nathan Larson
* http://www.mediawiki.org/wiki/Manual:addProxies.php
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
* http://www.gnu.org/copyleft/gpl.html
*
* @file
* @ingroup Maintenance
*/

require_once __DIR__ . '/Maintenance.php';

/**
* Maintenance script to add fields to the proxy table
*
* @ingroup Maintenance
*/
class AddProxies extends Maintenance {

    public function __construct() {
        parent::__construct();
        $this->addOption( "delete-existing", "Clear the proxy table of all existing proxies" );
        $this->addOption( "file", "Filename of CSV file", true, true );
        $this->mDescription = "Add proxies to the proxy table";
    }

    public function execute() {
        if ( file_exists( $this->getOption( 'file' ) ) ) {
            $this->output( "Reading file " . $this->getOption( 'file' ) . "...\n" );
            $handle = fopen( $this->getOption( 'file' ), "r" );
            $proxies = fgetcsv( $handle );
        } else {
            $this->output( "File " . $this->getOption( 'file' ) . " does not exist!\n" );
            die();
        }
        $dbw = wfGetDB( DB_MASTER );
        $sqlCreateTable = 'CREATE TABLE /*_*/proxy(prx_id INT UNSIGNED '
            . 'NOT NULL PRIMARY KEY AUTO_INCREMENT,'
            . 'prx_ip varchar(255) binary NOT NULL)/*$wgDBTableOptions*/';
        $sqlCreateIndex = 'CREATE INDEX /*i*/prx_ip ON /*_*/proxy (prx_ip)';
        $dropped = false;
        if ( $dbw->tableExists( 'proxy') && $this->hasOption( 'delete-existing' ) ) {
            $this->output( "Dropping the proxy table...\n" );
            $dbw->dropTable( 'proxy' );
            $dropped = true;
        }
        $created = false;
        if ( !$dbw->tableExists( 'proxy' ) ) {
            $this->output( "Creating the proxy table...\n" );
            $dbw->query( $sqlCreateTable );
            $dbw->query( $sqlCreateIndex );
            $created = true;
        }
        $existing = array();
        if ( !$created && !$dropped ) {
            $count = 0;
            $this->output( "Reading the proxy table...\n" );
            $res = $dbw->select( 'proxy', array( 'prx_ip' ) );
            $existing = array();
            foreach ( $res as $row ) {
                $existing[] = $row->prx_ip;
                if ( !($count%100000) && $count ) {
                    $this->output( "$count rows read so far...\n" );
                }
                $count++;
            }
            echo "$count rows read total.\n";
        }
        $this->output( "Preparing the database query...\n" );
        $rows = array();
        $proxies = array_diff( $proxies, $existing );
        $count = 0;
        if ( $proxies ) {
            foreach ( $proxies as $proxy ) {
                if ( $proxy ) {
                    $rows[] = array( 'prx_ip' => $proxy );
                    if ( !($count%100000) && $count ) {
                        $this->output( "$count rows prepared so far...\n" );
                    }
                    $count++;
                }
            }
            $this->output ( "$count rows prepared total.\n" );
            $this->output( "Inserting the rows...\n" );
            $dbw->insert( 'proxy', $rows );
        } else {
            $this->output ( "All the rows were already in the database; therefore, "
                . "there was nothing to\nprepare. Query aborted.\n" );
        }
        $this->output( "Done!\n" );
    }
}

$maintClass = "AddProxies";
require_once RUN_MAINTENANCE_IF_MAIN;