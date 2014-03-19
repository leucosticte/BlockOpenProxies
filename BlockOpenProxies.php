<?php
/**
* BlockOpenProxies MediaWiki extension.
*
* Written by Leucosticte
* https://www.mediawiki.org/wiki/User:Leucosticte
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* http://www.gnu.org/copyleft/gpl.html
*
* @file
* @ingroup Extensions
*/

if( !defined( 'MEDIAWIKI' ) ) {
        echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
        die( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
        'path' => __FILE__,
        'name' => 'BlockOpenProxies',
        'author' => 'Nathan Larson',
        'url' => 'https://mediawiki.org/wiki/Extension:BlockOpenProxies',
        'description' => 'Block open proxies',
        'version' => '1.0.1'
);

$wgHooks['userCan'][] = 'efBlockOpenProxies';

function efBlockOpenProxies( &$title, &$user, $action, &$result ) {
	if ( $action == 'edit' ) {
		global $wgProxyList;
		$ip = $user->getRequest()->getIP();
		$dbw = wfGetDB( DB_MASTER );
                $res = $dbw->select(
                    'proxy',
                    array( 'prx_ip' ),
                    array( 'prx_ip' => $ip )
                );
                if ( $res ) {
                    $wgProxyList[] = $ip;
                }
        }
	return true;
}