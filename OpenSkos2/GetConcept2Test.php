<?php

require_once dirname(__DIR__) . '/OpenSkos-1-picturae/GetConceptTest.php';
        
class GetConcept2Test extends PHPUnit_Framework_TestCase {
  
    /** obsolete resouce
    public function testViaHandle2() {
        GetConceptTest::testViaHandle2();
    }
     * **
     */
    
    public function testViaPrefLabel2() {
        print "\n" . "Test: get concept-rdf via its prefLabel. ";
        $client = Authenticator::authenticate();
        //prepare and send request 
        
        $client -> setUri(BASE_URI_ . '/public/api/find-concepts?q=prefLabel:' . CONCEPT_prefLabel);
        $client->setConfig(array(
            'maxredirects' => 0,
            'timeout' => 30));
        $client->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
                'Content-Type' => 'text/xml',
            'Accept-Language'=>'nl,en-US,en',
            'Accept-Encoding'=>'gzip, deflate',
            'Connection'=>'keep-alive')
        );
       $response = $client -> request(Zend_Http_Client::GET); 
        
       // analyse respond
       if ($response->getStatus() != 200) {
          print "\n " . $response->getMessage();
       }
      
       $this -> AssertEquals(200, $response->getStatus());
    }
}

