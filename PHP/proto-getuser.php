<?php
/*
        P R O T O T Y P E
*/

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

    public function getMember()
    {
	$query_url = "http://www.allocine.fr/membre-Z20140809065515090882683/";

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
}


$allocine = new Allocine('100043982026', '29d185d98c984a359e6e6f26a0474269');
$response = $allocine->getMember();

libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($response);
foreach($dom->getElementsByTagName("div") as $div) {
	if( $div->hasAttribute("class") and $div->getAttribute("class") == "user-bio" ) {
		foreach($div->getElementsByTagName('span') as $span) {
			if( $span->hasAttribute("data-entities") ) {
				//print "DATA-ENTITIES: " . $span->getAttribute("data-entities") . "\n";
				$buffer = json_decode($span->getAttribute("data-entities"), true);
				printf("EntityID: %s\n", $buffer["entityId"]);
				printf("Name: %s\n", $buffer["label"]);
				printf("Gender: %s\n", $buffer["gender"]);
			}
		}
		foreach($div->getElementsByTagName("p") as $p) {
			if( $p->hasAttribute("class") and $p->getAttribute("class") == "fs11 lighten_hl" ) {
				preg_match("/depuis(.*)jours/i", $p->nodeValue, $matches);
				$buffer = trim($matches[1]);
				printf("SINCE:%s\n", $buffer);

				preg_match("/activit.*:(.*)/i", $p->nodeValue, $matches);
				$buffer = trim($matches[1]);
				printf("LASTACT:%s\n", $buffer);
			} 
		}
		foreach($div->getElementsByTagName("div") as $sdiv) {
			if( $sdiv->hasAttribute("class") and $sdiv->getAttribute("class") == "pos_rel" ) {
				$array = Array("notes", "critiques", "suivis", "abo");
				foreach($array as $value) {
					if(eregi($value, $sdiv->nodeValue)) {
						preg_match("/([0-9]+)/", $sdiv->nodeValue, $matches);
						print($value . ":" . trim($matches[1]) . "\n");
					}
				}
			}
		}
	}
}

?>
