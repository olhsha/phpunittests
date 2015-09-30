<?php

require_once dirname(__DIR__) . '/Utils/Authenticator.php';
        
class GetConceptTest extends PHPUnit_Framework_TestCase {
    
    
    public function testViaHandle() {
        print "\n" . "Test: get concept via its handle. ";
        
        $client = Authenticator::authenticate();
        //prepare and send request 
        
        $client -> setUri(BASE_URI_ . '/public/api/concept?id=' . CONCEPT_handle);
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
       $this -> assertionsForXMLRDFConcept($response);
    }  
    
    public function testViaId() {
        print "\n" . "Test: get concept-rdf via its id. ";
        $client = Authenticator::authenticate();
        //prepare and send request 
        
        $client -> setUri(BASE_URI_ . '/public/api/concept/' . CONCEPT_id . '.rdf');
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
       $this -> assertionsForXMLRDFConcept($response);
    }
    
    public function testViaIdHTML() {
        print "\n" . "Test: get concept-html via its id. ";
        $client = Authenticator::authenticate();
        //prepare and send request 
        
        $client -> setUri(BASE_URI_ . '/public/api/concept/' . CONCEPT_id . '.html');
        $client->setConfig(array(
            'maxredirects' => 0,
            'timeout' => 30));
        $client->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
                'Content-Type' => 'text/html',
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
       $this -> assertionsForHTMLConcept($response);
    }
    
    public function testViaHandleJson() {
        print "\n" . "Test: get concept via its handle, return json. ";
        $client = Authenticator::authenticate();
        //prepare and send request 
        
        $client -> setUri(BASE_URI_ . '/public/api/find-concepts?format=json&fl=uuid,uri,prefLabel,class,dc_title&id=' . CONCEPT_handle);
        $client->setConfig(array(
            'maxredirects' => 0,
            'timeout' => 30));
        $client->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
                'Content-Type' => 'application/json',
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
       $this -> assertionsForJsonConcept($response);
    }  
    
    private function assertionsForXMLRDFConcept($response) {
        $dom = new Zend_Dom_Query();
        $dom->setDocumentXML($response->getBody());
        
        $results1 = $dom->query('rdf:Description');
        $this -> AssertEquals(CONCEPT_Description_about, $results1 -> current()-> getAttribute('rdf:about'));
        
        $results2 = $dom->query('rdf:type');
        $this -> AssertEquals(CONCEPT_type_resource, $results2 -> current()-> getAttribute('rdf:resource'));
        
        $results3 = $dom->query('skos:notation');
        $this -> AssertEquals(CONCEPT_notation, $results3 -> current()-> nodeValue);
        
        $results4 = $dom->query('skos:inScheme');
        $this -> AssertEquals(CONCEPT_NUMBER_inScheme, count($results4));
        
        $results5 = $dom->query('skos:topConceptOf');
        $this -> AssertEquals(CONCEPT_NUMBER_topConceptOf, count($results5));
        
        $results6 = $dom->query('skos:prefLabel');
        $this -> AssertEquals(CONCEPT_prefLabel_lang, $results6 -> current()-> getAttribute('xml:lang'));
        $this -> AssertEquals(CONCEPT_prefLabel, $results6 -> current()-> nodeValue);
        
        $results7 = $dom->query('skos:scopeNote');
        $this ->assertStringEndsWith(CONCEPT_ENDSWITH_scopeNote, $results7 -> current()-> nodeValue);
        
        $results8 = $dom->query('skos:definition');
        $this -> AssertStringEndsWith(CONCEPT_ENDSWITH_definition, $results8 -> current()-> nodeValue);
       
    }
    
    private function getByIndex($list, $index){
        if ($index < 0 || $index >= count($list)) {
            return null;
        }
        $list -> rewind();
        $i=0;
        while ($i<$index){
            $list -> next();
            $i++;
        }
        return $list -> current();
    }
    
    private function assertionsForHTMLConcept($response) {
        $dom = new Zend_Dom_Query();
        $dom->setDocumentHtml($response->getBody());
        
        //does not work because of . : $results1 = $dom->query('dl > dd  > a[href="http://hdl.handle.net/11148/CCR_C-4046_944cc750-1c29-ccf0-fb68-4d00385d7b42"]');
        $resultsUri1 = $dom->query('dl > dt');
        $resultsUri2 = $dom->query('dl > dd > a');
        $propertyName = $this -> getByIndex($resultsUri1, 2) -> nodeValue;
        $property = $this -> getByIndex($resultsUri2, 2);
        $this -> AssertEquals("URI:", $propertyName);
        $this -> AssertEquals(CONCEPT_handle, $property -> nodeValue);
        $this -> AssertEquals(CONCEPT_handle, $property -> getAttribute('href'));
                
        $h2s = $dom -> query('h2');
        $title =  $this -> getByIndex($h2s, 0) -> nodeValue;
        $this -> AssertEquals(CONCEPT_prefLabel, $title);
        
        $h3s = $dom -> query('h3');
        $inScheme =  $this -> getByIndex($h3s, 0) -> nodeValue;
        $this -> AssertEquals("inScheme", $inScheme);
        
        $listHeads = $dom -> query('ul');
        $inSchemeHead = $this ->getByIndex($listHeads, 0);
        $inSchemeVal = $inSchemeHead -> nodeValue;
        $inSchemeList = explode("\n", $inSchemeVal);
        $this -> AssertEquals(CONCEPT_NUMBER_inScheme+1, count($inSchemeList));
        
        $label = $dom -> query ('.prefLabel .label');
        $this -> AssertEquals(CONCEPT_prefLabel, $label -> current() -> nodeValue);
        
        $def = $dom -> query ('.definition .label');
        $this -> assertStringEndsWith(CONCEPT_ENDSWITH_definition, $def -> current() -> nodeValue);
        
        $note = $dom -> query ('.scopeNote .label');
        $this -> assertStringEndsWith(CONCEPT_ENDSWITH_scopeNote, $note -> current() -> nodeValue);
    }
    
    private function assertionsForJsonConcept($response) {
        $json = $response->getBody();
        $arrays = json_decode($json, true);
        $this -> assertEquals(CONCEPT_id, $arrays["uuid"]);
        $this -> assertEquals(CONCEPT_handle, $arrays["uri"]);
        $this -> assertEquals("Concept", $arrays["class"]);
        $this -> assertEquals(CONCEPT_prefLabel, $arrays["prefLabel"][0]);
    }
  
}
?>

