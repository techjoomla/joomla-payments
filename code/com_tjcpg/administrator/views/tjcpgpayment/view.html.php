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

jimport('joomla.application.component.view');

/**
 * View class for a list of Tjcpg.
 */
class TjcpgViewTjcpgpayment extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
	
			$this->addToolbar(); 
			if(version_compare(JVERSION, '3.0', 'ge'))
			{
				$this->sidebar = JHtmlSidebar::render();
			}
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/tjcpg.php';
		JToolBarHelper::title(JText::_('COM_TJCPG_TITLE_TJCPGPAYMENT'), 'tjcpgpayment.png');
		
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::preferences('com_tjcpg');
		    //Set sidebar action - New in 3.0
		   if(version_compare(JVERSION, '3.0', 'ge'))
			{
			JHtmlSidebar::setAction('index.php?option=com_tjcpg&view=tjcpgpayment');
			}
    $this->extra_sidebar = '';
        
        
	}
    
	protected function getSortFields()
	{
		return array(
		'a.id' => JText::_('JGRID_HEADING_ID'),
		'a.created_by' => JText::_('COM_TJCPG_TJCPGPAYMENT_CREATED_BY'),
		'a.checked_out_time' => JText::_('COM_TJCPG_TJCPGPAYMENT_CHECKED_OUT_TIME'),
		'a.checked_out' => JText::_('COM_TJCPG_TJCPGPAYMENT_CHECKED_OUT'),
		);
	}

    
}
