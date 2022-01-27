<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
 defined( '_JEXEC' ) or die( 'Restricted access' );

 use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Utility\Utility;

//$lang = & JFactory::getLanguage();
//$lang->load('plg_offlinecard', JPATH_ADMINISTRATOR);
JPlugin::loadLanguage('plg_offlinecard');
class plgpaymentofflinecard extends CMSPlugin 
{
	var $_payment_gateway = 'payment_offlinecard';
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$config = Factory::getConfig();
		$this->responseStatus = array(
			'closed' =>'C',
			'Pending' =>'D',
			'failed' =>'E',
			'open'=>'UR'
		);
		$this->encryption_key = $this->params->get('public_key');
	}
	
	/* Internal use functions */
	function buildLayoutPath($layout = "default") 
	{
		if(empty($layout))
		$layout = "default";
		$app = Factory::getApplication();
		$core_file 	= dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . $layout.'.php';
		$override	= JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/html/plugins/' . $this->_type . '/' . $this->_name . '/' . $layout.'.php';
		if(File::exists($override)) {
			return $override;	}
		else {	return  $core_file;	}
	}
	
	//Builds the layout to be shown, along with hidden fields.
	function buildLayout($vars, $layout = 'default' )
	{
		ob_start();
		$layout = $this->buildLayoutPath($layout);
		include($layout);
		$html = ob_get_contents(); 
		ob_end_clean();
		return $html;
	}
	
	// Used to Build List of Payment Gateway in the respective Components
	function onTP_GetInfo($config)
	{
		if(!in_array($this->_name,$config))
		return;
		$obj 		= new stdClass;
		$obj->name 	= $this->params->get( 'plugin_name' );
		$obj->id	= $this->_name;
		return $obj;
	}
	
	function onTP_GetHTML($vars)
	{
		$session = Factory::getSession();
		$session->set('amount', $vars->amount);
		$session->set('currency_code', $vars->currency_code);
		if(!empty($vars->payment_type) and $vars->payment_type != '')
			$payment_type = $vars->payment_type;
		else
			$payment_type = '';
		$html = $this->buildLayout($vars,$payment_type);
		return $html;
	}
	
	function onTP_Processpayment($data) 
	{
		$db = Factory::getDBO();
		$post = JFactory::getApplication()->input->get('post');
		$cardnum = substr($post['cardnum'], 0, 8);
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
		$cardno = base64_encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_256,hash('sha256', $this->encryption_key, true), $cardnum, MCRYPT_MODE_CBC, $iv)); 
		$cardexp = base64_encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_256,hash('sha256', $this->encryption_key, true), $post['cardexp'], MCRYPT_MODE_CBC, $iv)); 	
		$cardcvv = base64_encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_256,hash('sha256', $this->encryption_key, true), $post['cardcvv'], MCRYPT_MODE_CBC, $iv)); 
		$cardtype = base64_encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_256,hash('sha256', $this->encryption_key, true), $post['activated'], MCRYPT_MODE_CBC, $iv)); 					
		$arr = array('Card No' => $cardno, 'Expiry Date' => $cardexp, 'CVV Number' => $cardcvv, 'Card Type' => $cardtype);		
		$params = json_encode($arr);
		$mainframe =& Factory::getApplication('site');
		$sql = "UPDATE #__jg_orders SET processor = 'Offline Card', extra = '".$params."' ORDER BY id DESC LIMIT 1"; 
		$db->setQuery($sql);
		$db->query();
		$sql = "SELECT id FROM #__jg_orders WHERE order_id = '".$data["order_id"]."'";
		$db->setQuery($sql);	
		$id = $db->loadResult();
		$jconfig = Factory::getConfig();
		$jconfig->getValue('config.fromname'); 
		$params = ComponentHelper::getParams('com_jgive');
		$email = $params->get('email');
		$subject = Text::_('CREDIT_CARD_DETAILS');	
		$lastcardno = substr($post['cardnum'], 8); 
		$count =  strlen($lastcardno); 
		$order_id = $data["order_id"];
		$body = Text::sprintf('SEND_MSG_USER', $order_id, $count, $lastcardno);
			
		Utility::sendMail($jconfig->getValue('config.mailfrom'), $jconfig->getValue('config.fromname'), $email, $subject, $body, $mode=1, $cc=null, $bcc=null, $attachment=null, $replyto=null, $replytoname=null);			
		$user = Factory::getUser();
		if($user->guest) {
			$link = $_REQUEST["return"]; 
			$base = JURI::BASE();
			$link = str_replace($base, "", $link);
			$mainframe->redirect($link); 
		}
		else { $mainframe->redirect('index.php?option=com_jgive&view=donations&layout=details&donationid='.$id.'&processor=offlinecard&email=&Itemid=0');	}
		return true;
	}
	
    function onOrderDisplay($id)
    {
		$db = Factory::getDBO();
		$sql = "SELECT extra from #__jg_orders WHERE id = '".$id."'"; 
		$db->setQuery($sql);
		$params = $db->loadResult();
		$obj = json_decode($params);
		$data = base64_decode($obj->{'Card No'});
		$iv = substr(base64_decode($obj->{'Card No'}), 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));
		$cardno = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, hash('sha256', $this->encryption_key, true), substr(base64_decode($obj->{'Card No'}), mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)), MCRYPT_MODE_CBC, $iv), "\0");		
		
		$iv = substr(base64_decode($obj->{'CVV Number'}), 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));
		$cardcvv = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, hash('sha256', $this->encryption_key, true), substr(base64_decode($obj->{'CVV Number'}), mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)), MCRYPT_MODE_CBC, $iv), "\0");	
		
		$iv = substr(base64_decode($obj->{'Expiry Date'}), 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));
		$cardexp = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, hash('sha256', $this->encryption_key, true), substr(base64_decode($obj->{'Expiry Date'}), mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)), MCRYPT_MODE_CBC, $iv), "\0");		
		
		$iv = substr(base64_decode($obj->{'Card Type'}), 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));
		$cardtype = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, hash('sha256', $this->encryption_key, true), substr(base64_decode($obj->{'Card Type'}), mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)), MCRYPT_MODE_CBC, $iv), "\0");							
		$html ='<tr>
						<td>'.Text::_('Card No(1st 8 digits)') .'</td>
						<td>'.$cardno .'</td>
					</tr>
					<tr>
						<td>'. Text::_('Card CVV') .'</td>
						<td>'.$cardcvv .'</td>
					</tr>
					<tr>
						<td>'.Text::_('Expiry Date') .'</td>
						<td>'.$cardexp .'</td>
					</tr>
					<tr>
						<td>'.Text::_('Card Type') .'</td>
						<td>'.$cardtype .'</td>
					</tr>';
		echo $html;
	}
}

