<?php

class Allocine
{
    private $_api_url = 'http://api.allocine.fr/rest/v3';
    private $_partner_key;
    private $_secret_key;
    private $_user_agent = 'Dalvik/1.6.0 (Linux; U; Android 4.2.2; Nexus 4 Build/JDQ39E)';
    public $debug = False;

    public function __construct($partner_key, $secret_key)
    {
        $this->_partner_key = $partner_key;
        $this->_secret_key = $secret_key;
    }

    private function _do_request($method, $params)
    {
        // build the URL
        $query_url = $this->_api_url.'/'.$method;

        // new algo to build the query
	date_default_timezone_set('Europe/Paris');
        $sed = date('Ymd');
        $sig = urlencode(base64_encode(sha1($this->_secret_key.http_build_query($params).'&sed='.$sed, true)));
        $query_url .= '?'.http_build_query($params).'&sed='.$sed.'&sig='.$sig;

	if($this->debug === True)
		printf("DEBUG > query_url:'%s'\n", $query_url);

        // do the request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $query_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_user_agent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function search($query)
    {
        // build the params
        $params = array(
            'partner' => $this->_partner_key,
            'q' => $query,
            'format' => 'json',
            'filter' => 'movie'
        );

        // do the request
        $response = $this->_do_request('search', $params);

        return $response;
    }

    public function movie($id)
    {
        // build the params
        $params = array(
            'partner' => $this->_partner_key,
            'code' => $id,
            'profile' => 'large',
            'filter' => 'movie',
            'striptags' => 'synopsis,synopsisshort',
            'format' => 'json',
        );

        // do the request
        $response = $this->_do_request('movie', $params);

        return $response;
    }

    public function reviewlist($id)
    {
        // build the params
        $params = array(
            'partner' => $this->_partner_key,
            'code' => $id,
            'filter' => 'public',
            'format' => 'json',
            'count' => 1000,
            'page' => 1,
        );

        // do the request
        $response = $this->_do_request('reviewlist', $params);

        return $response;
    }

    public function showtimelist($zip, $radius) {
        $params = array(
            'partner' => $this->_partner_key,
            'zip' => $zip,
            'radius' => $radius,
            'format' => 'json',
        );

        // do the request
        $response = $this->_do_request('showtimelist', $params);

        return $response;
    }

    public function person($id) {
	$params = array(
		'partner' => $this->_partner_key,
		'code'    => $id,
		'profile' => "large"
	);
	$response = $this->_do_request('person', $params);
	return($response);
    }

}
