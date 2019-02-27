<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class ModCelloneFileListerHelper
{
	function getFileList(
							$params,
							$cofl_dirlocation,
							$cofl_basepath,
							$cofl_maxfiles,
							$cofl_userlocation
						)
    {

	    $results = "";

		$session = JFactory::getSession();

		// Reset session var. we had a reload if we enter this way!
		$session->set( 'cofl_startdir', '');
		$session->set( 'cofl_userdir', '');

		$results = "<div style=\"text-align: left\">";

		if (strlen($cofl_dirlocation) == 0 && strlen($cofl_userlocation) == 0) {
			$results .= JText::_('NO_DIR_GIVEN');
		} else {
			$results .= ModCelloneFileListerHelper::getDirContents($params, $cofl_dirlocation, $cofl_basepath, $cofl_maxfiles, $cofl_userlocation);
		}

		$results .= "</div>";

		return $results;
	}


	function getFileSizePP($filesize) {
		if(is_numeric($filesize)){
			$decr = 1024; $step = 0;
			$prefix = array('Bytes','KB','MB','GB','TB','PB');

			while(($filesize / $decr) > 0.9){
				$filesize = $filesize / $decr;
				$step++;
			}
				return round($filesize,2).' '.$prefix[$step];
		} else {
			return 'NaN';
		}

	}

	function getDirContents($params, $cofl_dirlocation, $cofl_basepath, $cofl_maxfiles, $cofl_userlocation) {

		$session = JFactory::getSession();
		$results = "";
		$cofl_goupdir = "";
		$cofl_currentdir = "";
		$browsedir = "";
		$filelist = "";
		$cofl_dirlocationdefault = $params->get( 'cofl_dirlocation', '.'.DIRECTORY_SEPARATOR.'images' );
		$cofl_next = $params->get( 'cofl_next', '0' );
		$cofl_showfilesize = $params->get( 'cofl_showfilesize', '0' );
		$cofl_onlyimg = $params->get( 'cofl_onlyimg', '0' );
		$cofl_imgthumbs = $params->get( 'cofl_imgthumbs', '0' );
		$cofl_thumbheight = $params->get( 'cofl_thumbheight', '30' );
		$cofl_thumbwidth = $params->get( 'cofl_thumbwidth', '30' );
		$cofl_thumbkeepaspect = $params->get( 'cofl_thumbkeepaspect', '0' );
		$cofl_listdir = $params->get( 'cofl_listdir', '0' );
		$cofl_browsedir = $params->get( 'cofl_browsedir', '0' );
		$cofl_showdir = $params->get( 'cofl_showdir', '1');
		$cofl_showicon = $params->get( 'cofl_showicon', '1');
		$cofl_sortorder = $params->get( 'cofl_sortorder', 'asc');
		$cofl_showsort = $params->get( 'cofl_showsort', '0' );
		$cofl_setbasepath = $params->get( 'cofl_basepath', '');
		$cofl_basepathusr = $params->get( 'cofl_basepathusr', '');
		$cofl_listleft = $params->get( 'cofl_listleft', '-10' );

		$cofl_allowdelete = $params->get( 'cofl_allowdelete', '0' );
		// $cofl_allowdeleteall = $params->get( 'cofl_allowdeleteall', '0' );
		//$cofl_allowdeletereg = $params->get( 'cofl_allowdeletereg', '0' );
		//$cofl_allowdeleteedt = $params->get( 'cofl_allowdeleteedt', '0' );
		$level_delete = $params->get('level_delete', '2');

		$cofl_movedeleted = $params->get( 'cofl_movedeleted', '0' );
		$cofl_movedeletedpath = $params->get( 'cofl_movedeletedpath', '' );
		$cofl_disablegdthreshold = $params->get( 'cofl_disablegdthreshold', '0' );
		$cofl_allowupdir = $params->get( 'cofl_allowupdir', '0' );

		$subdirlocation = "";

		$tmpSort = $session->get( 'cofl_sort', '');
		if (strlen($tmpSort) > 0)
			$cofl_sortorder = $tmpSort;

		// Get current logged in user
		$user = JFactory::getUser();
		$groups = $user->get('groups');

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

		//$show_delete = "0";
			$show_delete = ($cofl_allowdelete == "1" && (max($groups) >= $level_delete) ? "1" : "0");
/*
		if ($cofl_allowdelete === "1") {
			if ($cofl_allowdeleteall === "1")
				$show_delete = "1";
			if ($cofl_allowdeletereg === "1" && !$user->guest)
				$show_delete = "1";
			if ($cofl_allowdeleteedt === "1" && $user->authorise('core.edit', 'com_content'))
				$show_delete = "1";
		}*/
//echo "cofl_dirlocation=$cofl_dirlocation<br/>";
//echo "cofl_dirlocationdefault=$cofl_dirlocationdefault<br/>";

		// Don't allow moving upwards in dirs through AJAX
		if ($cofl_allowupdir == 0 && strlen(strstr($cofl_dirlocation, "../")) > 0) $cofl_dirlocation = $cofl_dirlocationdefault;

		if (strlen(strstr($cofl_dirlocation, $cofl_dirlocationdefault)) <= 0) $cofl_dirlocation = $cofl_dirlocationdefault;

//echo "cofl_dirlocation=$cofl_dirlocation<br/>";

		// If only cofl_userlocation is set!
		if (strlen($cofl_dirlocation) == 0 || (strlen($cofl_dirlocation) > 0 && strlen($cofl_userlocation) > 0)) {
			$cofl_dirlocation = $cofl_userlocation;
			if ( substr( $cofl_dirlocation , strlen($cofl_dirlocation) - 1) !== DIRECTORY_SEPARATOR )
				$cofl_dirlocation .= DIRECTORY_SEPARATOR;

			if (strlen($cofl_basepathusr) > 0 && $usr_name !== "") {
				if ( substr( $cofl_basepathusr , strlen($cofl_basepathusr) - 1) !== DIRECTORY_SEPARATOR )
					$cofl_basepathusr .= "/".$usr_name."/";
				else
					$cofl_basepathusr .= $usr_name."/";

				$cofl_setbasepath = $cofl_basepathusr;
				$session->set( 'cofl_userdir', $cofl_setbasepath);
			}
		} else {
			// check if we are in "user" mode
			$cofl_basepathusr = $session->get( 'cofl_userdir', '');
			if (strlen($cofl_basepathusr) > 0) $cofl_setbasepath = $cofl_basepathusr;
		}
//echo "cofl_dirlocation=$cofl_dirlocation<br/>";
//echo "cofl_setbasepath=$cofl_setbasepath<br/>";

		$baseurl = ModCelloneFileListerHelper::getBaseURL($cofl_dirlocation, $cofl_setbasepath);

		// Remove final slash to get dir.
		if ( substr( $cofl_dirlocation , strlen($cofl_dirlocation) - 1) === DIRECTORY_SEPARATOR )
			$cofl_dirlocation = substr( $cofl_dirlocation, 0, strlen($cofl_dirlocation) - 1);

		$startdir = $session->get( 'cofl_startdir', '');
		if ($startdir === '')
			$session->set( 'cofl_startdir', $cofl_dirlocation);

		if (strlen($startdir) > 0 && str_replace(DIRECTORY_SEPARATOR, "", $startdir) !== str_replace(DIRECTORY_SEPARATOR, "", $cofl_dirlocation)) {
			// We have browsed!

			$browsedir = substr($cofl_dirlocation, strlen($startdir));
			// Remove any leading slash
			$browsedir = str_replace(DIRECTORY_SEPARATOR, "/", substr($browsedir, 1));
			// Remove any trialing slash
			if ( substr( $browsedir , strlen($browsedir) - 1) === DIRECTORY_SEPARATOR )
				$browsedir = substr( $browsedir, 0, strlen($browsedir) - 1);
			// Make sure we are working with front-slash only
			$browsedir = str_replace(DIRECTORY_SEPARATOR, "/", $browsedir);

			$cofl_breadcrumb = "<a class=\"cofl_btnBrowseDir\" rel=\"". $startdir."\" href=\"javascript: void(0);\">".$startdir."</a>/";

			$dirvals = "/";
			$icntdir = 0;
			$pathcol = explode("/", $browsedir);
			foreach ($pathcol as $dirval) {
				$dirvals .= $dirval."/";
				$icntdir++;
				if ($icntdir < count($pathcol)) {
					$cofl_breadcrumb .= "<a class=\"cofl_btnBrowseDir\" rel=\"".$startdir.$dirvals."\" href=\"javascript: void(0);\">".$dirval."</a>/";
					// Get parent dir for "go up"
					$cofl_goupdir = "<a class=\"cofl_btnBrowseDir\" rel=\"".$startdir.$dirvals."\" href=\"javascript: void(0);\">".JText::_('UP_DIR')."</a>";
				} else {
					$cofl_breadcrumb .= $dirval;
					$cofl_currentdir = " ".$dirval;
				}
			}

// Fix AW 2001-05-20, if web server path is set subdir is omitted without below
$subdirlocation = $dirvals;
// Remove initial slash if exist
if (substr($subdirlocation, 0, 1) === "/") $subdirlocation = substr($subdirlocation, 1);
// Add trainling slash
if (substr($subdirlocation, strlen($subdirlocation) - 1) !== "/") $subdirlocation .= "/";
$baseurl .= $subdirlocation;
//$results .= "[$subdirlocation]";
			/*
$results .= "$baseurl .= $browsedir";
			// Set new browsedir and add one slash at the end if missing
			$baseurl .= $browsedir;
			if ( substr( $baseurl , strlen($baseurl) - 1) !== "/" )
				$baseurl .= "/";
*/
		} else {
			if ($startdir === "") $startdir = $cofl_dirlocation;
			$cofl_breadcrumb = $startdir;
		}

		// Open directory
		if($bib = @opendir($cofl_dirlocation)) {

			$idx = 0;
			$dir_list = null;
			$file_list = null;

			$idx_startat = $session->get( 'cofl_nextindex', 0);
			$idx_endat = $session->get( 'cofl_stopindex', $cofl_maxfiles);
//$results .= "idx_startat=$idx_startat | idx_endat=$idx_endat";

			if ($cofl_next === '1' && $idx_startat > 0)
				$fil_list[] = "<input type=\"hidden\" id=\"coflPrevVal\" value=\"".$idx_startat."\" /><a id=\"cofl_btnPrev\" href=\"javascript:void(0)\">".JText::_('PREV_BTN')."</a>";
			else
				$fil_list[] = "<input type=\"hidden\" id=\"coflPrevVal\" value=\"-1\" />";
				// Intially set previous to -1 to make sure it exists

				//$fil_list[] = "<form id=\"frm_coflprevious\" value=\"prev\" enctype=\"multipart/form-data\" action=\"\" method=\"POST\"><input type=\"hidden\" name=\"coflPrevious\" value=\"".$idx_startat."\" /><a href=\"javascript:void(0)\" onclick=\"javascript: cofl_MovePrevious(); coflSubmitForm('frm_coflprevious');\">".JText::_('PREV BTN')."</a></form>";


			while (false !== ($lfile = readdir($bib))) {

				if (is_dir($cofl_dirlocation.DIRECTORY_SEPARATOR.$lfile)) {
					// Safe it, dir or filenames can't contain a *
					if ($lfile !== "." && $lfile !== ".." && $cofl_listdir == 1)
						$dir_list[] =  array( "sort" => strtolower($lfile), "name" => "*dir*".$lfile );

				} else {

					$file_list[] = array( "sort" => strtolower($lfile), "name" => $lfile );

				}
			}

			//Sort Directories
			if (is_array($dir_list)) {
				//Asc
				if ($cofl_sortorder === "asc")
					asort($dir_list);

				//Desc
				if ($cofl_sortorder === "desc")
					rsort($dir_list);
			}

			//Sort Files
			if (is_array($file_list)) {
				//Asc
				if ($cofl_sortorder === "asc")
					asort($file_list);

				//Desc
				if ($cofl_sortorder === "desc")
					rsort($file_list);
			}
//print_r($file_list);

			if ($cofl_listdir == 1 && is_array($dir_list) && is_array($file_list))
				$full_list = array_merge($dir_list, $file_list);
			elseif (is_array($file_list))
				$full_list = $file_list;
			elseif ($cofl_listdir == 1 && is_array($dir_list))
				$full_list = $dir_list;
			else
				$full_list = null;
			if (is_array($full_list)) {
				foreach ($full_list as $lfile) {
				//while (false !== ($lfile = readdir($bib))) {
				//$cofl_listdir
				//$cofl_browsedir
					$fdir = (substr($lfile['name'], 0, 5) === "*dir*");

					if($lfile['name'] != "." && $lfile['name'] != ".." && !preg_match("/^\..+/", $lfile['name']) && $lfile['name'] != "index.html") {

						// Capture a list of files to be put in session var. This to protect delete
						$filelist .= $lfile['name'].'*';

						if ($idx >= $idx_endat) {
							$session->set( 'cofl_nextindex', $idx);
							$session->set( 'cofl_stopindex', $idx + $cofl_maxfiles);
							break;
						}

						$idx += 1;

						if ($idx > $idx_startat && $idx <= $idx_endat) {
							$tmpfile = "<nobr>";
							$tmpthumb = "";
							$is_img = false;

							if (($cofl_imgthumbs === '1' || $cofl_imgthumbs === '2') && !$fdir) {
								//Check image

								if ((filesize($cofl_dirlocation.DIRECTORY_SEPARATOR.$lfile['name']) <= $cofl_disablegdthreshold) || ($cofl_disablegdthreshold == 0)) {

									if ($img = @getimagesize($cofl_dirlocation.DIRECTORY_SEPARATOR.$lfile['name'])) {
										// Show thumbnail
									//if($img = @getimagesize($baseurl.str_replace(" ", "%20", $lfile['name']))) {
										//list($width, $height, $type, $attr) = getimagesize($baseurl.str_replace(" ", "%20", $lfile['name']));
										list($width, $height, $type, $attr) = getimagesize($cofl_dirlocation.DIRECTORY_SEPARATOR.$lfile['name']);
										if (($height > $cofl_thumbheight) && ($cofl_thumbkeepaspect === '1'))
											$tmpthumb = "<img border=\"0\" height=$cofl_thumbheight src=\"".$baseurl.str_replace(" ", "%20", $lfile['name'])."\"/>";
										else

											$tmpthumb = "<img border=\"0\" height=$cofl_thumbheight width=$cofl_thumbheight src=\"".$baseurl.str_replace(" ", "%20", $lfile['name'])."\"/>";

										$is_img = true;
									} else {
										// no thumbnail and show icon
										if ($cofl_showicon == 1)
											$tmpfile .= "<img height=\"24\" src=\"".JURI::root().$cofl_basepath."images/file.png\"/>";
									}
								} else {
									$tmpfile .= "<img height=\"24\" src=\"".JURI::root().$cofl_basepath."images/file.png\"/>";
								}

							} elseif ($fdir) {
								$lfile['name'] = substr($lfile['name'], 5);
								$tmpfile .= "<img height=\"24\" src=\"".JURI::root().$cofl_basepath."images/directory.png\"/>";
							} elseif ($cofl_showicon == 1) {
									$tmpfile .= "<img height=\"24\" src=\"".JURI::root().$cofl_basepath."images/file.png\"/>";
							}


							if ($fdir) {
								if ($cofl_browsedir == 1)
									$tmpfile .= "<a class=\"cofl_btnBrowseDir\" rel=\"" . $cofl_dirlocation.DIRECTORY_SEPARATOR.$lfile['name'] . "\" href=\"javascript: void(0);\">".$lfile['name']."</a>";
								else
									$tmpfile .= $lfile['name'];
							} else {
								// Add thumb-nail as clickable, empty string if no thumb option
								$linktext = $tmpthumb;
								if ($cofl_imgthumbs !== '2' || !$is_img) {
									// Add the filename if it is not an image and/or thumb is not created
									$linktext .= $lfile['name'];
								}
								$tmpfile .= "<a href=\"".$baseurl.str_replace(" ", "%20", $lfile['name'])."\" target=\"blank\">".$linktext."</a>";
							}

							// Show size but not for directories
							if ($cofl_showfilesize === '1' && !$fdir)
								$tmpfile .= " (".ModCelloneFileListerHelper::getFileSizePP(filesize($cofl_dirlocation.DIRECTORY_SEPARATOR.$lfile['name'])).")";

							// Allow delete?
							if ($show_delete === '1' && !$fdir)
								$tmpfile .= " <a class=\"cofl_btnDelete\" rel=\"".$cofl_dirlocation.DIRECTORY_SEPARATOR.$lfile['name']."**".$lfile['name']."\" href=\"javascript:void(0)\"><img class=\"cofldel\" src=\"".JURI::root().$cofl_basepath."images/delete.png\" /></a>";

							if (($cofl_onlyimg === '1' && $is_img) || ($cofl_onlyimg !== '1'))
								$fil_list[] = $tmpfile."</nobr>";
						}
					}
				}
				$session->set( 'cofl_filelist', $filelist);
			}

			closedir($bib);

			if ($cofl_next === '1' && $idx >= $idx_endat)
				$fil_list[] = "<input type=\"hidden\" id=\"coflNextVal\" value=\"".$idx_endat."\" /><a id=\"cofl_btnNext\" href=\"javascript:void(0)\">".JText::_('NEXT_BTN')."</a>";
				//$fil_list[] = "<form id=\"frm_coflnext\" value=\"next\" enctype=\"multipart/form-data\" action=\"\" method=\"POST\"><input type=\"hidden\" name=\"coflNext\" value=\"".$idx_endat."\" /><a id=\"cofl_btnNext\" href=\"javascript:void(0)\" onclick=\"javascript: cofl_MoveNext(); coflSubmitForm('frm_coflnext');\">".JText::_('NEXT BTN')."</a></form>";

			if(is_array($fil_list)) {
				$liste = "<div class=\"cofl_item\">" . join("</div><div class=\"cofl_item\">", $fil_list) . "</div>";
			} else {
				$liste = "<div class=\"cofl_item\">" . JText::_('NO_FILES_FOUND') . " " . $cofl_dirlocation . "</div>";
			}

			$sortascclass = "";
			$sortdescclass = "";
			if ($cofl_sortorder === "desc")
				$sortascclass = "class=\"cofl_shadow\" ";
			elseif ($cofl_sortorder === "asc")
				$sortdescclass = "class=\"cofl_shadow\" ";

			$sort_arrows = "";
			if ($cofl_showsort == 1)
				$sort_arrows = "<div style=\"width: 90%; height: 12px; text-align: right;\"><a id=\"cofl_ASortAsc\" class=\"cofl_ASortAsc\" href=\"javascript:void(0)\"><img id=\"coflSortAsc\" ".$sortascclass."alt=\"Sort ascending\" style=\"cursor: n-resize ;\" src=\"".JURI::root().$cofl_basepath."images/sort_up.png\" /></a>&nbsp;<a id=\"cofl_ASortDesc\" class=\"cofl_ASortDesc\" href=\"javascript:void(0)\"><img id=\"coflSortDesc\" ".$sortdescclass."alt=\"Sort descending\" style=\"cursor: n-resize ;\" src=\"".JURI::root().$cofl_basepath."images/sort_down.png\" /></a></div>";

			if ($cofl_showdir == 1) {
				$results .=  "<b>" . JText::_('FILES_IN_DIR') . " (" . $cofl_breadcrumb . "):</b>";
			} elseif ($cofl_showdir == 0) {
				$results .=  "<b>".JText::_('FILES_IN_DIR').$cofl_currentdir.":</b><br />";
				// If no breadcrumb we must have "home" and "up" buttons
				if (strlen($browsedir) > 0)
					$results .= "<a class=\"cofl_btnBrowseDir\" rel=\"".$startdir."\" href=\"javascript: void(0);\">".JText::_('GO_HOME')."</a>&nbsp;&nbsp;".$cofl_goupdir."<br />";
			}
			$results .= $sort_arrows."<div>" . $liste . "</div>";

		} else {
			$results .=  "<b>" . JText::_('ERROR_READ') ." (Dir: ". $cofl_dirlocation . ")</b>";
		}

		return $results;

	}


	function getBaseURL($cofl_dirlocation, $cofl_basepath) {
		$baseurl = "";
		$serverurl = "";
		$protocol = "";
		$protocol = "http://";

		$dirlocation = $cofl_dirlocation;

		if (strlen($cofl_basepath) == 0) {

			$tmp_dirlocation = str_replace("\\", "/", $dirlocation);

			if (substr(JURI::base(), 0, 5) === "https") $protocol = "https://";
			$folder = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], "/"));
			if ($folder === "//") $folder = "";
//print_r($dirlocation."<br/>");
			//Do we have .. in the path?
			if (strpos($dirlocation, "../") >= 0) {
				$dirlocation = realpath($dirlocation);
				$server_root = $_SERVER["DOCUMENT_ROOT"];
				//SCRIPT_FILENAME
				$dirlocation = str_replace($server_root, "/", $dirlocation);
				// Get rid of http:// or https://
				$server_basedir = str_replace("http://", "https://", $_SERVER["HTTP_HOST"]);
				$server_basedir = str_replace("https://", "", $server_basedir);
				$server_basedir = explode(".", $server_basedir);
//print_r($server_basedir[0]."<br/>");
//print_r($dirlocation."<br/>");
				if ($server_basedir[0] === substr($dirlocation, 2, strlen($server_basedir[0])))
					$dirlocation = ".".substr($dirlocation, strlen($server_basedir[0])+2);
				if (substr($dirlocation, 0, 2) === "//")
					$dirlocation = str_replace("//", "./", $dirlocation);
//print_r($dirlocation);
				//$dirlocation = "[".$server_basedir[0]."][".substr($dirlocation, 2, strlen($server_basedir[0]))."]";
			}
	//TEST 2011-03-24 TEST WITH WINDOWS FULL PATH TO RELATIVE PATH IF UNDER WEB ROOT
			if (strpos($tmp_dirlocation, ":/") >= 0 || substr($tmp_dirlocation, 0, 1) === "/") {
				//We have a root path, check and see if it is under Server root, e.g. make http:// url
				$cofl_realdirlocation = realpath($dirlocation);
				$cofl_realdirlocation = str_replace("\\", "/", $cofl_realdirlocation);
				$server_root = realpath($_SERVER["DOCUMENT_ROOT"]);
				$server_path = str_replace($server_root, "", str_replace("index.php", "", realpath($_SERVER["SCRIPT_FILENAME"])));
	//$dirlocation = "[".$server_root."][".$cofl_realdirlocation."][".$server_path."]";

				$server_root = str_replace("\\", "/", $server_root);
				$server_path = str_replace("\\", "/", $server_path);

				if (strlen(str_replace($server_root, "", $cofl_realdirlocation)) < strlen($cofl_realdirlocation)) {
					//Path is in server root
					$dirlocation = str_replace($server_root, ".", $cofl_realdirlocation);
	//print_r($dirlocation);
	//print_r($server_path);
					if (strpos($dirlocation, $server_path) >= 0) {
						$dirlocation = str_replace($server_path, "/", $dirlocation);
					}
				}

			}


			// Check if relative path
			if (substr($dirlocation, 0, 1) === ".") {
				// Don't replace all dots... Could be dots in directory name!!!
				//$serverurl .= str_replace(".", $protocol.$_SERVER["HTTP_HOST"].$folder, $dirlocation);
				$serverurl .= $protocol.$_SERVER["HTTP_HOST"].$folder . substr($dirlocation, 1);
				// Fix Windows path...
				$baseurl .= str_replace("\\", "", $serverurl);
			} else {
				if ((substr($dirlocation, 1, 2) === ":\\") || (substr($dirlocation, 0, 1) === "/")) {
					// Server root path
					$baseurl = "file://".str_replace("\\", "/", $dirlocation);
				} else {

					$serverurl = str_replace("\\", "/", $_SERVER["DOCUMENT_ROOT"]);

					$baseurl = str_replace("\\", "/", $dirlocation);

					$baseurl = str_replace($serverurl, "", $baseurl);
					//$baseurl = dirname($_SERVER["HTTP_REFERER"])."/".$baseurl;
					$baseurl = $protocol.$_SERVER["HTTP_HOST"].$folder."/".$baseurl;
				}
			}
		} else {
			$baseurl = $cofl_basepath;
		}

		//Replace space with %20 for URL
		$baseurl = str_replace(" ", "%20", $baseurl);

		// Make sure it ends with front slash
		if ( substr( $baseurl , strlen($baseurl) - 1) !== "/" ) {
			$baseurl .= "/";
		}
		return $baseurl;
	}

	function deleteFile($params, $fileName) {

		$cofl_allowdelete = $params->get( 'cofl_allowdelete', '0' );
		$cofl_movedeleted = $params->get( 'cofl_movedeleted', '0' );
		$cofl_movedeletedpath = $params->get( 'cofl_movedeletedpath', '' );
		$level_delete = $params->get('level_delete', '2');
		$session = JFactory::getSession();

		// Get current logged in user
		$user = JFactory::getUser();
		$groups = $user->get('groups');
		$usr_name = $user->get('username');
		if(stripos($usr_name, "/") !== false) {
			$usr_name = "fake";
		}
		if(stripos($usr_name, "\\") !== false) {
			$usr_name = "fake";
		}
		if(stripos($usr_name, "..") !== false) {
			$usr_name = "fake";
		}

		$fileName = explode("**", $fileName);
		$absoluteFilePath = $fileName[0];
		$fileName = $fileName[1];

		//$show_delete = "0";
		$msg_backup = "";

		$show_delete = ($cofl_allowdelete == "1" && (max($groups) >= $level_delete) ? "1" : "0");
		// if ($cofl_allowdelete == "1" && (max($groups) >= $level_delete)) $show_delete = "1";

		// Check that we are in the current listed directory
		/*if ($session->get( 'cofl_currentdir' ).$fileName != $absoluteFilePath || $session->get( 'cofl_currentdir' ).'/'.$fileName != $absoluteFilePath) {
			$show_delete = 0;
		}*/
		// Make sure that the file deleted is in the list shown
		if (strpos($session->get( 'cofl_filelist' ), $fileName.'*') === false) {
			$show_delete = 0;
		}

		if ($show_delete == "1") {

			// Check if you are to move the file
			if ($cofl_movedeleted === "1") {
				// Bail out if no move directory is set, must be atleast "./a"
				if (strlen($cofl_movedeletedpath) < 3)
					return JText::_('DELETE_MSG1');
					//return "Setup error!\nYou must set the path to the directory to move the files to before deleting files!\nCheck your Joomla backend settings.";

				if ( substr( $cofl_movedeletedpath , strlen($cofl_movedeletedpath) - 1) !== DIRECTORY_SEPARATOR )
					$cofl_movedeletedpath .= DIRECTORY_SEPARATOR;

				if (!file_exists($cofl_movedeletedpath)) {

					if (mkdir($cofl_movedeletedpath, 0777, true)) {
						//echo "Created dir: " . $cofl_movedeletedpath;
						// Add empty HTML page to newly created directory
						if (!file_exists($cofl_movedeletedpath . "index.html")) {
							$fhIndex = fopen($cofl_movedeletedpath . "index.html", "w");
							if (!$fhIndex) {
								$stringData = "<html><body bgcolor=\"#FFFFFF\"></body></html>\n";
								fwrite($fhIndex, $stringData);

								fclose($fhIndex);
							}
						}


					} else {
						return JText::_('DELETE_MSG2').$cofl_movedeletedpath;
						//return "Failed to create backup directory $cofl_movedeletedpath";
						//echo "Failed to create dir: " . $cofl_movedeletedpath;
					}

				}

				$new_filename = $cofl_movedeletedpath.$fileName."_".$usr_name."_".microtime();
				if (!copy($absoluteFilePath, $new_filename)) {
					return JText::_('DELETE_MSG3');
					//return "Failed to move file! Original file not deleted!";
				} else {
					if (!file_exists($new_filename))
						return JText::_('DELETE_MSG4');
						//return "Backup file does not exist. Delete failed!";
					$msg_backup = "\n(".JText::_('DELETE_MSG5').")";
					//$msg_backup = "\n(Backup successful)";
				}
			}

			if (!unlink($absoluteFilePath)) {
				return JText::_('DELETE_MSG6')." ($absoluteFilePath)!";
				//return "Failed to delete file ($absoluteFilePath)!";
			} else {
				return "$fileName ".JText::_('DELETE_MSG7')."!$msg_backup";
			}

		} else {
			return JText::_('DELETE_MSG8')." Your Highest Level: [".max($groups)."] Min Level Required: [".$level_delete."] Delete Allowed Settings(must be 1:1): ".$cofl_allowdelete.":".$show_delete;
			//return "You are not allowed to delete files!";
		}

	}

}

class coflAjaxServlet {

	function getContent($action, $params, $cofl_dirlocation, $cofl_basepath, $cofl_maxfiles, $cofl_userlocation, $cofl_file) {
		$retVal = "false";

		// We should alsways get directory through Ajax call, userlocation only at initial call
		$cofl_userlocation = "";

		switch ($action) {
			case "delete":
				//$retVal = "<div style=\"text-align: left\">";
				// Just send the information text back!
				$retVal = ModCelloneFileListerHelper::deleteFile($params, $cofl_file);
				//$retVal .= "</div>";
				break;

			case "next" || "prev" || "dir" || "sort":

				$retVal = "<div style=\"text-align: left\">";
				$retVal .= ModCelloneFileListerHelper::getDirContents($params, $cofl_dirlocation, $cofl_basepath, $cofl_maxfiles, $cofl_userlocation);
				$retVal .= "</div>";
				break;

			default:
				$retVal = "Action missing";
				break;
		}

		$app = JFactory::getApplication();
		echo $retVal;
		$app->close();
	}

}

?>
