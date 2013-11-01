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

class TjcpgController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			$cachable	If true, the view output will be cached
	 * @param	array			$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{

		$view		= JFactory::getApplication()->input->getCmd('view', 'tjcpgpayment');
    JFactory::getApplication()->input->set('view', $view);
    
    
    ////
    $jinput = JFactory::getApplication()->input;
		$vName = $jinput->get('view', 'tjcpgpayment');
		//$vName=JRequest::getCmd('view', 'cp');
		//  for line bar
				require_once JPATH_COMPONENT.'/helpers/tjcpg.php';

		TjcpgHelper::addSubmenu($vName);
    ///
    
    
    

		parent::display($cachable, $urlparams);

		return $this;
	}
}
