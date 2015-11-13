<?php

class ApiConceptTest extends PHPUnit_Framework_TestCase {
	public $client;

    protected function setUp() {
    	$this->client = new Zend_Http_Client();
    }
        
    public function testApiAutocomplete() {
    	/*  HTTP GET: /api/autocomplete/[term]
    	*/
    	print "\nTest /api/autocomplete/[term] ";
    	$this->client->setUri(BASE_URI_ . '/api/autocomplete/' .QUERY_term);
    	 
    	$response = $this->client -> request(Zend_Http_Client::GET);    
    	$this->assertJson($response->getBody());
    }
    
    public function testApiAutocompleteJSONP() {
    	/*  3. Sprekerherkenning:
    	*  HTTP GET: /api/autocomplete/[term] in jsonP format
    	*  	Parameters: callback, format??
    	*/
    	print "\nTest /api/autocomplete/[term] in jsonP format";
    	$this->client->setUri(BASE_URI_ . '/api/autocomplete/' .QUERY_term);
    	
    	// OpenSKOS parameters
    	$this->client->setParameterGet(array(
    			'format'=> 'jsonp',
    			'callback'=>'myCallback',
    	));
    	$response = $this->client -> request(Zend_Http_Client::GET);
    	 
    	$this->assertionsForJSONPResult($response);
    }
    

	/* JSON assertions
	 *
	*/
	private function assertionsForJSONPResult($response) {
		//	response is JSONP response
		$jsonp = $response->getBody();
		print $jsonp;
		$this->assertInternalType('string', $jsonp);

		$pattern3 = '/^\w+\((?<json>.+)\);$/';
		preg_match($pattern3, $jsonp, $morematches);
		$json = $morematches['json'];
		$this->assertJson($json);
	}

}
?>

