<?php
/**
* @version		3.9j
* @copyright	Copyright (C) 2010-2011 Anders Wasén
* @license		GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();



class JFormFieldDonate extends JFormField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	//public $type = 'Donate';
	//var	$_name = 'Donate';
	protected $type = 'Donate'; //the form field type


	//function fetchElement($name, $value, &$node, $control_name)
	protected function getInput()
	{

		$html = '';

		$html = '<div class="clr"></div><div class="input-wrap" style="background: #d2d2d2;clear: both; display: table;"><input id="kofi" type="image" src="https://ko-fi.com/img/cuplogo.svg"  style="height:60px;float:left;padding: 10px;"border="0" alt="Buy me a Coffee - The safer, easier way to pay online!" onclick="javascript: window.open (\'https://ko-fi.com/A843IG1\', \'donate\',\'\');" /><label for="kofi" style="
    margin: 0;
    font-size: 20px;
    line-height: 60px;
    float: left;
		    padding: 10px;
">Buy me a KoFi</label></div>';
		$html .= '<br />Well, I think it\'s worth AT LEAST a cup of KoFi! What do you think? (Buy me a coffee. Thanks!)';

		return $html;


	}
}
