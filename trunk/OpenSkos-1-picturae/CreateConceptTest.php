<?php

require_once dirname(__DIR__) . '/Utils/Authenticator.php';
require_once dirname(__DIR__) . '/Utils/Logging.php'; 

class CreateConceptTest extends PHPUnit_Framework_TestCase {

    public function test01CreateConceptWithDateAccepted() {
        // Create new concept with dateAccepted filled (implicit status APPROVED). This should not be possible. 
        $client = Authenticator::authenticate();
        $randomn = rand(0, 2048);
        $prefLabel= 'testPrefLable_' . $randomn;
        $dateSubmitted = date(DateTime::ISO8601);
        $dateAccepted = date(DateTime::ISO8601);
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcterms="http://purl.org/dc/terms/" > ' .
               '<rdf:Description>' . 
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>'. 
    '<dcterms:creator>Test</dcterms:creator>' .
    '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' . 
    '<dcterms:dateSubmitted>' . $dateSubmitted . '</dcterms:dateSubmitted>' .
    '<dcterms:dateAccepted>' . $dateAccepted . '</dcterms:dateAccepted>' .
    '<skos:inScheme rdf:resource="http://data.beeldengeluid.nl/gtaa/GeografischeNamen"/>'.
  '</rdf:Description>' .
'</rdf:RDF>';
        
        $client -> setUri(BASE_URI_ .  "/public/api/concept?");
        $client ->setConfig(array(
        'maxredirects' => 0,
        'timeout'      => 30));
        
       $response = $client
        ->setEncType('text/xml')
        ->setRawData($xml)
        ->setParameterGet('tenant', TENANT)
        ->setParameterGet('collection', COLLECTION_1_code)
        ->setParameterGet('key', API_KEY)
        ->request('POST');
     if ($response->isSuccessful()) {
        print '\n Concept created \n';
        var_dump($response -> getBody());
    
     } else {
    	print  '\n Failed to create concept: ' . $response->getHeader('X-Error-Msg');
     }
     $this -> AssertEquals(409, $response -> getStatus());
    
    }
    
}

?>