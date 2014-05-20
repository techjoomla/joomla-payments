<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.filesystem.file' );
jimport( 'joomla.plugin.plugin' );
$lang =  JFactory::getLanguage();
$lang->load('plg_cpgdetails', JPATH_ADMINISTRATOR);
class plgpaymentofflinecard extends JPlugin 
{
	var $_payment_gateway = 'payment_offlinecard';
	var $_log = null;
	
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$config = JFactory::getConfig();
		$this->responseStatus= array(
			'closed' =>'C',
			'Pending' =>'D',
			'failed' =>'E',
			'open'=>'UR'
		);
		$this->encryption_key = $this->params->get('public_key');
	}
	
	/* Internal use functions */
	function buildLayoutPath($layout="default") {
		if(empty($layout))
		$layout="default";
		$app = JFactory::getApplication();
		$core_file 	= dirname(__FILE__).DS.$this->_name.DS.'tmpl'.DS.$layout.'.php';
		$override	= JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'plugins'.DS.$this->_type.DS.$this->_name.DS.$layout.'.php';
		if(JFile::exists($override))
		{
			return $override;
		}
		else
		{
	  	return  $core_file;
	}
	}
	
	//Builds the layout to be shown, along with hidden fields.
	function buildLayout($vars, $layout = 'default' )
	{
		// Load the layout & push variables
		ob_start();
		$layout = $this->buildLayoutPath($layout);
		include($layout);
		$html = ob_get_contents(); 
		ob_end_clean();
		return $html;
	}
	//gets param values
    function getParamResult($name, $default = '') 
    {
		
    	$sandbox_param = "sandbox_$name";
    	$sb_value = $this->params->get($sandbox_param);
    	
        if ($this->params->get('sandbox') && !empty($sb_value)) {
            $param = $this->params->get($sandbox_param, $default);
        }
        else {
        	$param = $this->params->get($name, $default);
        }
        
        return $param;
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
		
		$session = JFactory::getSession();
		$session->set('amount', $vars->amount);
		$session->set('currency_code', $vars->currency_code);
		if(!empty($vars->payment_type) and $vars->payment_type!='')
			$payment_type=$vars->payment_type;
		else
			$payment_type='';
		$html = $this->buildLayout($vars,$payment_type);
		return $html;
	}
	
	function onTP_Processpayment($data) 
	{
		$db = JFactory::getDBO();
		$component = JRequest::getVar('option'); 
		$post=JRequest::get('post');
		$cardnum = substr($post['cardnum'], 0, 8);
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
		$cardno = base64_encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_256,hash('sha256', $this->encryption_key, true), $cardnum, MCRYPT_MODE_CBC, $iv)); 
		$cardexp = base64_encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_256,hash('sha256', $this->encryption_key, true), $post['cardexp'], MCRYPT_MODE_CBC, $iv)); 	
		$cardcvv = base64_encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_256,hash('sha256', $this->encryption_key, true), $post['cardcvv'], MCRYPT_MODE_CBC, $iv)); 
		$cardtype = base64_encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_256,hash('sha256', $this->encryption_key, true), $post['activated'], MCRYPT_MODE_CBC, $iv)); 					
		$arr = array('Card No' => $cardno, 'Expiry Date' => $cardexp, 'CVV Number' => $cardcvv, 'Card Type' => $cardtype);		
		$params = json_encode($arr);
		$mainframe =& JFactory::getApplication('site');
		if($component == 'com_quick2cart') {
			$order_id = $data["order_id"];
			$sql = "UPDATE #__kart_orders SET prefix = '".$data["order_id"]."', extra = '".$params."' ORDER BY id DESC LIMIT 1"; 
			$db->setQuery($sql);
			$db->query();
			$mainframe->redirect('index.php?option=com_quick2cart&view=cartcheckout&layout=payment', JText::_('MSG_SAVE'));
		}
		if($component == 'com_jgive') {
			$sql = "UPDATE #__jg_orders SET processor = 'Offline Card', extra = '".$params."' ORDER BY id DESC LIMIT 1"; 
			$db->setQuery($sql);
			$db->query();
			$sql = "SELECT id FROM #__jg_orders WHERE order_id = '".$data["order_id"]."'";
			$db->setQuery($sql);	
			$id = $db->loadResult();

			$jconfig = JFactory::getConfig();
			$jconfig->getValue('config.fromname'); 
			$params=JComponentHelper::getParams('com_jgive');
			$email = $params->get('email');
			$subject= JText::_('CREDIT_CARD_DETAILS');	
			$lastcardno = substr($post['cardnum'], 8); 
			$count =  strlen($lastcardno); 
			$order_id = $data["order_id"];
			$body=JText::sprintf('SEND_MSG_USER', $order_id, $count, $lastcardno);
			
			JUtility::sendMail($jconfig->getValue('config.mailfrom'), $jconfig->getValue('config.fromname'), $email, $subject, $body, $mode=1, $cc=null, $bcc=null, $attachment=null, $replyto=null, $replytoname=null);			
			$user = JFactory::getUser();
			if($user->guest) {
				$link = $_REQUEST["return"]; 
				$base = JURI::BASE();
				$link = str_replace($base, "", $link);
				$mainframe->redirect($link); 
			}
			else { $mainframe->redirect('index.php?option=com_jgive&view=donations&layout=details&donationid='.$id.'&processor=offlinecard&email=&Itemid=0');	}

		 }
		if($component == 'com_jticketing') {
			$sql = "UPDATE #__jticketing_orders SET extra = '".$params."' ORDER BY id DESC LIMIT 1"; 
			$db->setQuery($sql);
			$db->query();
			$mainframe->redirect('index.php?option=com_jgive&view=donations&layout=confirm','Card detailsSaved');		 		
		 }
		if($component == 'com_socialads') {
			$sql = "UPDATE #__jticketing_order SET extra = '".$params."' ORDER BY id DESC LIMIT 1"; 
			$db->setQuery($sql);
			$db->query();
			$mainframe->redirect('index.php?option=com_jticketing&view=mytickets','Card detailsSaved');		 }						
			return true;
	}
	
    function onOrderDisplay($id)
    {
		$db = JFactory::getDBO();
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
						<td>'.JText::_('Card No(1st 8 digits)') .'</td>
						<td>'.$cardno .'</td>
					</tr>
					<tr>
						<td>'. JText::_('Card CVV') .'</td>
						<td>'.$cardcvv .'</td>
					</tr>
					<tr>
						<td>'.JText::_('Expiry Date') .'</td>
						<td>'.$cardexp .'</td>
					</tr>
					<tr>
						<td>'.JText::_('Card Type') .'</td>
						<td>'.$cardtype .'</td>
					</tr>';
					
		
		echo $html;
	}
	
}

