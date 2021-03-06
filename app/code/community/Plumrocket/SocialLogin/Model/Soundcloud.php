<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_SocialLogin
 * @copyright   Copyright (c) 2014 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_SocialLogin_Model_Soundcloud extends Plumrocket_SocialLogin_Model_Account
{
	protected $_type = 'soundcloud';
	
	protected $_responseType = 'code';
    protected $_url = 'https://soundcloud.com/connect';

	protected $_fields = array(
					'user_id' => 'id',
		            'firstname' => 'first_name',
		            'lastname' => 'last_name',
		            'email' => 'email', // empty
		            'dob' => 'birthday', // empty
                    'gender' => 'gender', // empty
                    'photo' => 'avatar_url',
				);

	protected $_buttonLinkParams = array(
					'scope' => 'non-expiring',
                    'display' => 'popup',
				);

    protected $_popupSize = array(650, 450);

	public function _construct()
    {      
        parent::_construct();
        
        $this->_buttonLinkParams = array_merge($this->_buttonLinkParams, array(
            'client_id'     => $this->_applicationId,
            'redirect_uri'  => $this->_redirectUri,
            'response_type' => $this->_responseType
        ));
    }

    public function loadUserData($response)
    {
    	if(empty($response)) {
    		return false;
    	}

        $data = array();

        $params = array(
            'client_id' => $this->_applicationId,
            'client_secret' => $this->_secret,
            'redirect_uri' => $this->_redirectUri,
            'code' => $response,
            'grant_type' => 'authorization_code',
        );
    
        $token = null;
        if($response = $this->_callPost('https://api.soundcloud.com/oauth2/token', $params)) {
            $token = json_decode($response, true);
        }
        $this->_setLog($token, true);

        if (isset($token['access_token'])) {
            $params = array(
                'oauth_token' => $token['access_token'],
            );
    
            if($response = $this->_callGet('https://api.soundcloud.com/me.json', $params)) {
                $data = json_decode($response, true);
            }
            $this->_setLog($data, true);
            
            if(isset($data['response'])) {
                $data = $data['response'];
            }
        }
 
        if(!$this->_userData = $this->_prepareData($data)) {
        	return false;
        }

        $this->_setLog($this->_userData, true);

        return true;
    }

    protected function _prepareData($data)
    {
    	if(empty($data['id'])) {
    		return false;
    	}

        // Name.
        if(!empty($data['username'])) {
            $nameParts = explode(' ', $data['username'], 2);
            $data['username_fn'] = $nameParts[0];
            $data['username_ln'] = !empty($nameParts[1])? $nameParts[1] : '';
        }

        if(empty($data['first_name'])) {
            $data['first_name'] = $data['username_fn'];
        }
        if(empty($data['last_name'])) {
            $data['last_name'] = $data['username_ln'];
        }

        return parent::_prepareData($data);
    }

    protected function _callGet($url, $params = array())
    {
        if(is_array($params) && $params) {
            $url .= '?'. urldecode(http_build_query($params));
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        
        // curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'pslogin');

        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    protected function _callPost($url, $params = array())
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        // curl_setopt($curl, CURLOPT_USERPWD, $this->_applicationId .':'. $this->_secret);
        curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

}