<?php
namespace Internet4Associations; 
use Internet4Associations\Traits\SingletonTrait; 
use Internet4Associations\Exceptions\RuntimeException; 
class Request extends \SoapClient { 
	use SingletonTrait; 
	protected $config; 
	protected $token; 
	protected $ssoToken; 
	protected $cstToken; 
	
	public function __construct($i4aWsdl, array $i4aRequestParams) { 
		$this->config = (object) $i4aRequestParams;
		$this->config->debug = false;		// added this to fix error with AutoLogin.php
		$this->wsdl = $i4aWsdl;
		$this->wsdl_params = $this->constructParams($i4aRequestParams); 
		parent::__construct($i4aWsdl, $this->wsdl_params); 
		return $this; 
	} 
	
	public function getTimeout() { 
		return (int) $this->config->timeout; 
	} 
	
	public function setTimeout(int $i4aTimeoutLength) { 
		$this->config->timeout = $i4aTimeoutLength; 
	} 
	
	public function getSoapVersion() { 
		return SOAP_1_1; 	
	} 
	
	protected function constructParams(array $i4aConstructParams) { 
		if ($this->config->debug) { 
			$i4aConstructParams += array('trace' => true); 
		} 
		$thisUserAgent = $_SERVER['REMOTE_ADDR'] . ' - I4A Api';
		// add stream_context options to fix ssl cert failure issues 6/1/2020
		$i4aConstructParams += array( 
				'exceptions' => true, 
				'soap_version' => $this->getSoapVersion(), 
				'connection_timeout' => $this->getTimeout(), 
				'default_socket_timeout' => $this->getTimeout(), 
				'cache_wsdl' => WSDL_CACHE_NONE, 
				'features' => SOAP_SINGLE_ELEMENT_ARRAYS, 
				'encoding' => 'UTF-8', 
				'user_agent' => $thisUserAgent, 
				'stream_context' => stream_context_create(array(
						'ssl' => array(
								'verify_peer' => false,
								'verify_peer_name' => false,
								'allow_self_signed' => true
						)
				))
		); 
		return array_filter($i4aConstructParams); 
	} 
	
	public function __doRequest($i4aXmlSoapRequest, $i4aRequestURL, $i4aSoapAction, $i4aSoapVersion, $i4aOneWayRequest = 0) { 
		ini_set('default_socket_timeout', $this->getTimeout()); 
		if ($this->config->debug) { 
			dd(colorize(' >>> Sending Request _______', 'blue')); 
			dd(colorize('Params: Request: ' . prettyXML($i4aXmlSoapRequest) . "\nLocation: {$i4aRequestURL}\nAction: {$i4aSoapAction}\nVersion: {$i4aSoapVersion}", 'blue')); 
			dd(colorize('________________', 'blue') . ''); 
		} 
		return parent::__doRequest($i4aXmlSoapRequest, $i4aRequestURL, $i4aSoapAction, $i4aSoapVersion); 
	} 
	
	public function auth() { 
		$i4aAuthenticateResultObj = $this->request('Authenticate', array('parameters' => array('username' => $this->config->username, 'password' => $this->config->password)));
		if (is_object($i4aAuthenticateResultObj)) { 
			$this->token = $i4aAuthenticateResultObj->AuthenticateResult; 
		} 
		return $this;
	} 
	
	
	public function authSso($i4aSsoLogin = null, $i4aSsoPass = null) { 
		if (is_null($i4aSsoLogin) && is_null($i4aSsoPass) && !$this->config->credentials) { 
			throw new \Exception('Client credentials are required.'); 
		} 
		if (is_null($i4aSsoLogin) && is_null($i4aSsoPass) && $this->config->credentials) { 
			$i4aSsoLogin = $this->config->credentials['username']; 
			$i4aSsoPass = $this->config->credentials['password']; 
		} 
		
		$i4aWpOption = get_option('internet4associations');
		$enable_nonmembers = '0'; // default to false (must be string format)
		if ( isset($i4aWpOption['single_sign_on']['enable_nonmembers']) ) {
			$enable_nonmembers = $i4aWpOption['single_sign_on']['enable_nonmembers'];
		}
		
		$i4aAuthenticateResultObj = $this->auth()->request(
			'GetSignOnToken',
			array(
					'parameters' => array(
							'email' => $i4aSsoLogin, 
							'password' => $i4aSsoPass, 
							'minutes' => $this->config->ttl, 
							'enable_nonmembers' => $enable_nonmembers 
					)
				)
			);
		
		$i4aAuthenticateResultObj_i4aFormat  = simplexml_load_string($i4aAuthenticateResultObj);	
		
		if (is_object($i4aAuthenticateResultObj_i4aFormat) && isset($i4aAuthenticateResultObj_i4aFormat->GetSignOnTokenResult)) { 
			$thisSsoToken = $i4aAuthenticateResultObj_i4aFormat->GetSignOnTokenResult;
			$this->ssoToken = $thisSsoToken;
		} 
		return $this; 
	} 
	
	
	public function authCST() { 
		$i4aAuthenticateResultObj = $this->auth()->request('GetCstKeyFromSignOnToken', array('parameters' => array('szEncryptedSingOnToken' => $this->ssoToken)));
		$i4aAuthenticateResultObj_i4aFormat  = simplexml_load_string($i4aAuthenticateResultObj);
		if (is_object($i4aAuthenticateResultObj_i4aFormat)) {
			$this->cstToken = $i4aAuthenticateResultObj_i4aFormat->GetCstKeyFromSignOnTokenResult; 	
		}
		return $this; 
	} 
	
	public function getToken() { 
		return $this->token; 
	} 
	
	public function getSsoToken() { 
		if (is_null($this->ssoToken)) { 
			$this->authSso(); 
		} 
		return $this->ssoToken; 
	} 
	
	public function getCstToken() { 
		if (is_null($this->cstToken)) { 
			$this->authSso(); 
			$this->authCST(); 
		} 
		return $this->cstToken; 
	} 
	
	public function getCustomerByKey($i4aCustomerKey = null) { 	
		$getCustomerByKeyParams = array(
				'parameters' => array(
						'szCstKey' => is_null($i4aCustomerKey) ? $this->getCstToken() : $i4aCustomerKey 
				)
		);
		
		$myCustomerByKey = $this->OD()->request('GetCustomerByKey', $getCustomerByKeyParams);
		$myCustomerByKey_i4aFormat  = simplexml_load_string($myCustomerByKey);
		
		return $myCustomerByKey_i4aFormat;
	} 
	
	
	public function getMemberAndContactTypes() {
		$i4aMemberAndContactTypes = $this->auth()->request('GetMemberAndContactTypes', array('parameters' => array()));
		$i4aMemberAndContactTypes_i4aFormat  = simplexml_load_string($i4aMemberAndContactTypes);
		return $i4aMemberAndContactTypes_i4aFormat;
	}

	
	public function request($i4aRequestType, array $i4aRequestParams = array(), $i4aSoapHeader = null) { 
		try { 
			if ($this->config->debug) { 
				dd(colorize('Command is ' . $i4aRequestType, 'yellow')); 
			} 
			if (!isset($i4aRequestParams['parameters']['AuthToken']) && isset($this->token)) { 
				$i4aRequestParams['parameters']['AuthToken'] = $this->token; 
				$i4aSoapHeader = new \SoapHeader('http://www.i4a.com/Api/2018/', 'AuthorizationToken', array('Token' => '123456789'));
				if ($this->config->debug) { 
					dd('SENDING HEADERS: '); 
					dd($i4aSoapHeader); 
				} 
			}
			
			$i4aParams = $i4aRequestParams['parameters'];
		
			$i4aSoapCallResponseObj = $this->__soapCall($i4aRequestType, $i4aParams, null, $i4aSoapHeader);
			
			if ($this->config->debug) { 
				dd(colorize(" <<< {$i4aRequestType} Response Received _______", 'green') . ''); 
				dd($this->getAuthenticateResultSize($i4aRequestType, $i4aSoapCallResponseObj)); dd(colorize('________________', 'green') . ''); 
			} 
			
			return $this->getAuthenticateResultSize($i4aRequestType, $i4aSoapCallResponseObj);
			
			
		} catch (\SoapFault $i4aTextString) { 
			$i4aSoapFaultMsg = $i4aTextString->getMessage(); 
			if (preg_match('/failed to load external entity/i', $i4aSoapFaultMsg)) { 
				$i4aSoapFaultMsg = 'request failed, Internet4Associations did not respond to our request, try again.'; 
			} 
			throw new RuntimeException($i4aSoapFaultMsg, $i4aTextString->getCode(), $i4aTextString); 
		} 
	} 
	
	private function getAuthenticateResultSize($i4aRequestType, $i4aAuthenticateResultObj) { 
		$i4aRequestTypeResult = $i4aRequestType . 'Result'; 
		if (!isset($i4aAuthenticateResultObj->{$i4aRequestTypeResult}->any)) { 
			return $i4aAuthenticateResultObj; 
		} 
		libxml_use_internal_errors(true); 
		$i4aAuthenticateResultObj = simplexml_load_string($i4aAuthenticateResultObj->{$i4aRequestTypeResult}->any); 
		$i4aAuthenticateResultObj = is_object($i4aAuthenticateResultObj) && isset($i4aAuthenticateResultObj->Result) ? $i4aAuthenticateResultObj->Result : $i4aAuthenticateResultObj;
		return sizeof($i4aAuthenticateResultObj) ? $i4aAuthenticateResultObj : array(); 
	} 
	
	protected function OD() { 
		$this->auth(); 
		if (!isset($this->od)) { 
			if (preg_match('/signon/', $this->wsdl)) { 
				$i4aWsdl = $this->getWsdlPage('wordpress.cfc');
			} else { 
				$i4aWsdl = $this->getWsdlPage('wordpress.cfc');
			} 
			$this->od = new static($i4aWsdl, $this->wsdl_params); 
		} 
		$this->od->token = $this->token; 
		$this->od->ssoToken = $this->ssoToken; 
		$this->od->cstToken = $this->cstToken; 
		return $this->od; 
	} 
	
	protected function getWsdlPage($i4aWsdlFilename, $i4aStrURL = null) { 
		if (is_null($i4aStrURL)) { 
			$i4aStrURL = $this->wsdl; 
		} 
		$i4aWsdlObj = parse_url($i4aStrURL); 
		$i4aWsdlObj['path'] = dirname($i4aWsdlObj['path']) . '/' . $i4aWsdlFilename; 
	
		return http_build_url($i4aStrURL, $i4aWsdlObj); 
	} 

}