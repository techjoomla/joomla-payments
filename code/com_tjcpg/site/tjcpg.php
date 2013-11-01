<?php
/**
 * @version     1.0.0
 * @package     com_tjcpg
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      vidyasagar <vidyasagar_m@tekdi.net> - http://techjoomla.com
 */

defined('_JEXEC') or die;
$user=JFactory::getUser();
$path = JPATH_COMPONENT.DS.'helpers'.DS."tjcpg.php";
if(!class_exists('tjcpgHelper'))
{
  //require_once $path;
   JLoader::register('tjcpgHelper', $path );
   JLoader::load('tjcpgHelper');
}

// Include dependancies
jimport('joomla.application.component.controller');
$controller="";
require_once (JPATH_COMPONENT.DS.'controller.php');
$input=JFactory::getApplication()->input;
if($controller = $input->get('controller')) {
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	if (file_exists($path)) {
		require_once $path;
	} else {
		$controller = '';
	}
}
$classname	= 'TjcpgController'.$controller;
$controller = new $classname();

// Perform the Request task
$controller->execute($input->get('task'));

// Redirect if set by the controller
$controller->redirect();

