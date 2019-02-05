<?php
/**
 * CellularOne File Lister Module Entry Point
 *
 * @package    Joomla
 * @subpackage Modules
 * @author SimonTech
 * @link http://simontech.me/
 * @license		GNU/GPL, see LICENSE.php
 * mod_cellonefilelister is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$session =& JFactory::getSession();
$baseurl = "";

$cofl_version = "1.0";
$cofl_basepath = "modules/mod_cellonefilelister/";

$cofl_dirlocation = $params->get( 'cofl_dirlocation', '.'.DIRECTORY_SEPARATOR.'images' );

$cofl_maxfiles = $params->get( 'cofl_maxfiles', '20' );
$cofl_bgcolor = $params->get( 'cofl_bgcolor', '#e8edf1' );
if ( substr( $cofl_bgcolor, 0, 1 ) !== "#" ) {
	$cofl_bgcolor = "#" . $cofl_bgcolor;
}
$cofl_maxheight = $params->get( 'cofl_maxheight', '0' );
$cofl_useusernameddir = $params->get( 'cofl_useusernameddir', '0' );
$cofl_usernameddirdefault = $params->get( 'cofl_usernameddirdefault', '0' );
$cofl_userlocation = $params->get( 'cofl_userlocation', '' );
if ( substr( $cofl_userlocation , strlen($cofl_userlocation) - 1) !== DIRECTORY_SEPARATOR ) {
  $cofl_userlocation .= DIRECTORY_SEPARATOR;
}
$cofl_boxleft = $params->get( 'cofl_boxleft', '-16' );
$cofl_allowdelete = $params->get( 'cofl_allowdelete', '0' );
$cofl_jquery = $params->get( 'cofl_jquery', '0' );
$cofl_jqueryinclude = $params->get( 'cofl_jqueryinclude', '0' );

// Get current logged in user
$user =& JFactory::getUser();
$usr_id = $user->get('id');
$usr_name = $user->get('username');
if(stripos($usr_name, "/") !== false) {
	$usr_name = "";
}
if(stripos($usr_name, "\\") !== false) {
	$usr_name = "";
}
if(stripos($usr_name, "..") !== false) {
	$usr_name = "";
}

if ($cofl_maxfiles > 0) {
	// Check if this is a new login
	if ($session->get( 'cofl_usrid', 0) !== $usr_id) {
		$session->set( 'cofl_nextindex', 0);
		$session->set( 'cofl_stopindex', $cofl_maxfiles);
	} else {

		if (isset($_GET["coflPrevious"])) {
			if (strlen($_GET["coflPrevious"]) > 0) {

				$idx_startat = $session->get( 'cofl_nextindex', 0);
				$idx_endat = $session->get( 'cofl_stopindex', $cofl_maxfiles);

				if ($idx_startat > 0 && $idx_endat > $cofl_maxfiles) {
					$idx_startat = $_GET["coflPrevious"] - $cofl_maxfiles;
					$idx_endat = $idx_startat + $cofl_maxfiles;

					$session->set( 'cofl_nextindex', $idx_startat);
					$session->set( 'cofl_stopindex', $idx_endat);
				}
			}
		}

	}

	$session->set( 'cofl_usrid', $usr_id);
}

if (!isset($_GET["coflPrevious"]) && !isset($_GET["coflNext"])) {

	// Neither next nor previous, must be reload from other link
	$session->set( 'cofl_nextindex', 0);
	$session->set( 'cofl_stopindex', $cofl_maxfiles);

}


if ($cofl_useusernameddir == 1) {

	// If only list users files clear default path
	if ($cofl_usernameddirdefault === '1' && strlen($cofl_userlocation) > 0) $cofl_dirlocation = '';

	if ($usr_id > 0 && strlen($cofl_userlocation) > 0) {
		// Set user path, it already has the DIRECTORY_SEPARATOR at the end, don't add after usr_name.
		$cofl_userlocation .= $usr_name;
	} else {
		$cofl_userlocation = '';
	}
} else {
	$cofl_userlocation = '';
}
// Make ready for Ajax calls and avoid any whitespace
if (isset($_GET["coflaction"])) {
if(!class_exists('coflAjaxServlet')) JLoader::register('coflAjaxServlet' , dirname(__FILE__).DIRECTORY_SEPARATOR.'helper.php');
//Security check
if (isset($_GET["coflDir"])) {
	if (strlen($cofl_userlocation) == 0) $cofl_userlocation = "-";
	// Check that either default dir or user dir is present in the given dir. If not set it to default
	if (strpos($_GET["coflDir"], $cofl_dirlocation) === false && strpos($_GET["coflDir"], $cofl_userlocation) === false) {
		// Add warning txt?
	} else {
		$cofl_dirlocation = $_GET["coflDir"];
	}
}
if (strpos($cofl_dirlocation, "../") !== false) $cofl_dirlocation = $params->get( 'cofl_dirlocation', '.'.DIRECTORY_SEPARATOR.'images' );
if (strlen($cofl_dirlocation) == 0) $cofl_dirlocation = $cofl_userlocation;
$cofl_file = "";
$session->set( 'cofl_currentdir', $cofl_dirlocation);
if ($_GET["coflaction"] === "delete") $cofl_file = $_GET["coflDelete"];
if ($_GET["coflaction"] === "sort" && isset($_GET["coflSort"])) $session->set( 'cofl_sort', $_GET["coflSort"]);
echo coflAjaxServlet::getContent($_GET["coflaction"], $params, $cofl_dirlocation, $cofl_basepath, $cofl_maxfiles, $cofl_userlocation, $cofl_file);
} else {

	// include the helper file
	require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'helper.php');
	$results = '';
	$results .= ModCelloneFileListerHelper::getFileList($params, $cofl_dirlocation, $cofl_basepath, $cofl_maxfiles, $cofl_userlocation);

	// include the template for display
	require(JModuleHelper::getLayoutPath('mod_cellonefilelister'));

}
?>
