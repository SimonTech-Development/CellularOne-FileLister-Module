<?php
/*----------------------------------------------------------------------------------|  simontech.me  |----/

				CellularOne File Lister Module

/-------------------------------------------------------------------------------------------------------/

	@version		0.9.x
	@build			17th November, 2018
	@created		6th September, 2018
	@package		CellularOne File Lister Module
	@subpackage		levels.php
	@author			SimonTech <http://simontech.me>
	@copyright		Copyright (C) 2018 - CellularOne. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

/------------------------------------------------------------------------------------------------------*/


// No direct access to this file
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldGroups extends JFormFieldList
{
	public $type = 'Groups';


	protected function getOptions()
	{
		$levels = array(1, 9);
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select(['id as value', 'title as text'])
			->from($db->quoteName('#__usergroups'))
			->where($db->quoteName('id') . ' NOT IN (' . implode(',', $levels) . ')')
			->order($db->quoteName('id') . ' ASC');

		$db->setQuery($query);

		$options = parent::getOptions();
		if ( ! $this->multiple && empty($options))
		{
			$options[] = JHtml::_('select.option', '0', '- ' . JText::_('MOD_CELLONEFILELISTER_USERGROUP_SELECTION') . ' -');
		}

		return array_merge($options, $db->loadObjectList());
	}
}
