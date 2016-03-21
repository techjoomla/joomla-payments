<?php
/**
 * @version     1.0.0
 * @package     com_tjcpg
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      vidyasagar <vidyasagar_m@tekdi.net> - http://techjoomla.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Tjcpg helper.
 */
class TjcpgHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($vName = '')
	{
		
		
		if(version_compare(JVERSION, '3.0', 'ge'))
		{
			JHtmlSidebar::addEntry(
			JText::_('COM_TJCPG_TITLE_TJCPGPAYMENT'),
			'index.php?option=com_tjcpg&view=tjcpgpayment',
			$vName == 'tjcpgpayment'
		);
		}
		else
		{
			JSubMenuHelper::addEntry(JText::_('COM_TJCPG_TITLE_TJCPGPAYMENT'), 'index.php?option=com_tjcpg&view=tjcpgpayment',$vName == 'tjcpgpayment');
		}


	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_tjcpg';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
}
