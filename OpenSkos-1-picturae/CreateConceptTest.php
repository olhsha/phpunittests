<?php

require_once dirname(__DIR__) . '/Utils/Authenticator.php';
require_once dirname(__DIR__) . '/Utils/Logging.php'; 

class CreateConceptTest extends PHPUnit_Framework_TestCase {
    
    

    public function test01CreateConceptWithoutURIWithDateAccepted() {
        // Create new concept with dateAccepted filled (implicit status APPROVED). This should not be possible. 
        $client = Authenticator::authenticate();
        $randomn = rand(0, 2048);
        $prefLabel= 'testPrefLable_' . $randomn;
        $dateSubmitted = date(DateTime::ISO8601);//'2015-10-01T15:06:58Z';//
        $dateAccepted = '2015-10-02T10:31:35Z';// date(DateTime::ISO8601);'2015-10-01T15:06:58Z';
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
        ->setParameterGet('autoGenerateIdentifiers', 'true')
        ->request('POST');
     if ($response->isSuccessful()) {
        print "\n Concept created \n";
        var_dump($response -> getBody());
    
     } else {
    	print  "\n Failed to create concept: " . $response->getHeader('X-Error-Msg');
     }
     $this -> AssertEquals(409, $response -> getStatus());
    
    }
    
    public function test02CreateConceptWithoutUriWithoutDateAccepted() {
        // Create a concept without Uri and without dateAccepted , but with UniquePrefLabel. Check XML response.
        $client = Authenticator::authenticate();
        $randomn = rand(0, 2048);
        $prefLabel= 'testPrefLable_' . $randomn;
        $dateSubmitted = date(DateTime::ISO8601);
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcterms="http://purl.org/dc/terms/" > ' .
               '<rdf:Description>' . 
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>'. 
    '<dcterms:creator>Test</dcterms:creator>' .
    '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' . 
    '<dcterms:dateSubmitted>' . $dateSubmitted . '</dcterms:dateSubmitted>' .
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
        ->setParameterGet('autoGenerateIdentifiers', 'true')
        ->request('POST');
     if ($response->isSuccessful()) {
        print "\n Concept created \n";
        var_dump($response -> getBody());
    
     } else {
    	print  "\n Failed to create concept: " . $response->getHeader('X-Error-Msg');
     }
     $this -> AssertEquals(201, $response -> getStatus());
     
     print "\n HTTPResponseHeader-Location: " . $response ->getHeader('Location');
     
     $namespaces = array (
         "rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
          "skos" => "http://www.w3.org/2004/02/skos/core#",
          "openskos" => "http://openskos.org/xmlns/openskos.xsd"  
     );
     
     $dom = new Zend_Dom_Query();
     $dom->setDocumentXML($response->getBody());
     $dom->registerXpathNamespaces($namespaces);
     
     $elem = $dom->queryXpath('/rdf:RDF');
     $this-> assertEquals($elem ->current()->nodeType, XML_ELEMENT_NODE, 'The root node of the response is not an element');
     
     $resURI = $dom->queryXpath('/rdf:RDF/rdf:Description')->current()->getAttribute("rdf:about");
     $this-> assertNotEquals("", $resURI, "No valid uri for SKOS concept");
     $status = $dom->queryXpath('/rdf:RDF/rdf:Description/openskos:status');
     $this-> assertEquals(1, $status ->count(), "No valid uri for SKOS concept");
     
     print "\n New concept is created with URI $resURI  and status" . $status -> current()->nodeValue;
    }
    
    public function test03CreateConceptWithURIAlreadyExists() {
        // test if creating a new concept with an URI that already exists, fails
        $client = Authenticator::authenticate();
        $randomn = rand(0, 2048);
        $prefLabel= 'testPrefLable_' . $randomn;
        $dateSubmitted = date(DateTime::ISO8601);
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:openskos="http://openskos.org/xmlns/openskos.xsd" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcterms="http://purl.org/dc/terms/" > ' .
               '<rdf:Description rdf:about="http://data.beeldengeluid.nl/gtaa/1">' . 
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>'. 
    '<dcterms:creator>Test</dcterms:creator>' .
    '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' . 
    '<dcterms:dateSubmitted>' . $dateSubmitted . '</dcterms:dateSubmitted>' .
    '<skos:inScheme rdf:resource="http://data.beeldengeluid.nl/gtaa/GeografischeNamen"/>'.
    '<skos:notation>1</skos:notation>'.
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
        ->setParameterGet('autoGenerateIdentifiers', 'false')
        ->request('POST');
     if ($response->isSuccessful()) {
        print "\n Concept created \n";
        var_dump($response -> getBody());
    
     } else {
    	print  "\n Failed to create concept: " . $response->getHeader('X-Error-Msg');
     }
     $this -> AssertEquals(409, $response -> getStatus());
    }
    
    public function test04CreateConceptWithoutURIUniquePrefLabelNoApiKey() {
        // create concept without URI. but with unique prefLabel. Api Key is missng.
        // todo: veoeken met verkeerde parameters moeten foutcode opleveren (collection, tenant)
        $client = Authenticator::authenticate();
        $randomn = rand(0, 2048);
        $prefLabel= 'testPrefLable_' . $randomn;
        $dateSubmitted = date(DateTime::ISO8601);
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcterms="http://purl.org/dc/terms/" > ' .
               '<rdf:Description rdf:about="http://data.beeldengeluid.nl/gtaa/1">' . 
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>'. 
    '<dcterms:creator>Test</dcterms:creator>' .
    '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' . 
    '<dcterms:dateSubmitted>' . $dateSubmitted . '</dcterms:dateSubmitted>' .
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
        ->setParameterGet('autoGenerateIdentifiers', 'true')
        ->request('POST');
     if ($response->isSuccessful()) {
        print "\n Concept created \n";
        var_dump($response -> getBody());
    
     } else {
    	print  "\n Failed to create concept: " . $response->getHeader('X-Error-Msg');
     }
     $this -> AssertEquals(412, $response -> getStatus());
    }
    
    public function test05CreateConceptWithURIUniquePrefLabel() {
        // Create concept with URI and with unique prefLabel, including skos:notation
        print "\n\n test05 ... \n";
        $client = Authenticator::authenticate();
        $randomn = rand(0, 4096);
        $prefLabel= 'testPrefLable_' . $randomn;
        $notation = 'testNotation_' . $randomn;
        $about = '"http://data.beeldengeluid.nl/gtaa/'.$notation . '"';
        $dateSubmitted = date(DateTime::ISO8601);
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcterms="http://purl.org/dc/terms/" > ' .
               '<rdf:Description rdf:about=' . $about .'>' . 
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>'. 
    '<dcterms:creator>Test</dcterms:creator>' .
    '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' . 
    '<dcterms:dateSubmitted>' . $dateSubmitted . '</dcterms:dateSubmitted>' .
    '<skos:inScheme rdf:resource="http://data.beeldengeluid.nl/gtaa/GeografischeNamen"/>'.
    '<skos:notation>' . $notation . '</skos:notation>'.
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
        ->setParameterGet('autoGenerateIdentifiers', 'false')
        ->request('POST');
     if ($response->isSuccessful()) {
        print "\n Concept created \n";
        var_dump($response -> getBody());
    
     } else {
    	print  "\n Failed to create concept: " . $response->getHeader('X-Error-Msg');
     }
     
     $this -> AssertEquals(201, $response -> getStatus());
     
     print "\n HTTPResponseHeader-Location: " . $response ->getHeader('Location');
     
     $namespaces = array (
         "rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
          "skos" => "http://www.w3.org/2004/02/skos/core#",
          "openskos" => "http://openskos.org/xmlns/openskos.xsd"  
     );
     
     $dom = new Zend_Dom_Query();
     $dom->setDocumentXML($response->getBody());
     $dom->registerXpathNamespaces($namespaces);
     
     $elem = $dom->queryXpath('/rdf:RDF');
     $this-> assertEquals($elem ->current()->nodeType, XML_ELEMENT_NODE, 'The root node of the response is not an element');
     
     $resURI = $dom->queryXpath('/rdf:RDF/rdf:Description')->current()->getAttribute("rdf:about");
     $this-> assertNotEquals("", $resURI, "No valid uri for SKOS concept");
     $status = $dom->queryXpath('/rdf:RDF/rdf:Description/openskos:status');
     $this-> assertEquals(1, $status ->count(), "No valid uri for SKOS concept");
     
     print "\n New concept is created with URI $resURI  and status" . $status -> current()->nodeValue;
    }
    
    public function test05BCreateConceptWithURIUniquePrefLabel() {
        // Create concept with URI and with unique prefLabel, withou skos:nottion
        print "\n\n test05B ... \n";
        $client = Authenticator::authenticate();
        $randomn = rand(0, 4096);
        $prefLabel= 'testPrefLable_' . $randomn;
        $notation = 'testNotation_' . $randomn;
        $about = '"http://data.beeldengeluid.nl/gtaa/'.$notation . '"';
        $dateSubmitted = date(DateTime::ISO8601);
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcterms="http://purl.org/dc/terms/" > ' .
               '<rdf:Description rdf:about=' . $about . '>' . 
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>'. 
    '<dcterms:creator>Test</dcterms:creator>' .
    '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' . 
    '<dcterms:dateSubmitted>' . $dateSubmitted . '</dcterms:dateSubmitted>' .
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
        ->setParameterGet('autoGenerateIdentifiers', 'false')
        ->request('POST');
     if ($response->isSuccessful()) {
        print "\n Concept created \n";
        var_dump($response -> getBody());
    
     } else {
    	print  "\n Failed to create concept: " . $response->getHeader('X-Error-Msg');
     }
     
     print "\n HTTPResponseHeader-Location: " . $response ->getHeader('Location');
     
     $this -> AssertEquals(400, $response -> getStatus());
    }
    
    public function test05CCreateConceptWithURIUniquePrefLabel() {
        // Create concept with URI and with unique prefLabel, with duplicate skos:notation
        print "\n\n test05C ... \n";
        $client = Authenticator::authenticate();
        $randomn = rand(0, 4096);
        $prefLabel= 'testPrefLable_' . $randomn;
        $tail = 'testNotation_' . $randomn;
        $about = '"http://data.beeldengeluid.nl/gtaa/' .$randomn. '/1"';
        $dateSubmitted = date(DateTime::ISO8601);
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcterms="http://purl.org/dc/terms/" > ' .
               '<rdf:Description rdf:about=' . $about . '>' . 
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>'. 
    '<dcterms:creator>Test</dcterms:creator>' .
    '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' . 
    '<dcterms:dateSubmitted>' . $dateSubmitted . '</dcterms:dateSubmitted>' .
    '<skos:inScheme rdf:resource="http://data.beeldengeluid.nl/gtaa/GeografischeNamen"/>'.
    '<skos:notation>1</skos:notation>'.
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
        ->setParameterGet('autoGenerateIdentifiers', 'false')
        ->request('POST');
     if ($response->isSuccessful()) {
        print "\n Concept created \n";
        var_dump($response -> getBody());
    
     } else {
    	print  "\n Failed to create concept: " . $response->getHeader('X-Error-Msg');
     }
     
     print "\n HTTPResponseHeader-Location: " . $response ->getHeader('Location');
     
     $this -> AssertEquals(400, $response -> getStatus());
    }
    
    public function test06CreateConceptWithURIUniquePrefLabel() {
        // Create concept without URI abut the xml is wrong
        print "\n\n test06 ... \n";
        $client = Authenticator::authenticate();
        $dateSubmitted = date(DateTime::ISO8601);
        $wrongXml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcterms="http://purl.org/dc/terms/" > ' .
               '<rdf:Description>' . 
                //'<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>'. 
    '<dcterms:creator>Test</dcterms:creator>' .
    '<skos:prefLabel xml:lang="nl">blablabla</skos:prefLabel>' . 
    '<dcterms:dateSubmitted>' . $dateSubmitted . '</dcterms:dateSubmitted>' .
    '<skos:inScheme rdf:resource="http://data.beeldengeluid.nl/gtaa/GeografischeNamen"/>'.
  '</rdf:Description>' .
'</rdf:RDF>';
        
        $client -> setUri(BASE_URI_ .  "/public/api/concept?");
        $client ->setConfig(array(
        'maxredirects' => 0,
        'timeout'      => 30));
        
       $response = $client
        ->setEncType('text/xml')
        ->setRawData($wrongXml)
        ->setParameterGet('tenant', TENANT)
        ->setParameterGet('collection', COLLECTION_1_code)               
        ->setParameterGet('key', API_KEY)
        ->setParameterGet('autoGenerateIdentifiers', 'true')
        ->request('POST');
     if ($response->isSuccessful()) {
        print "\n Concept created \n";
        var_dump($response -> getBody());
    
     } else {
    	print  "\n Failed to create concept: " . $response->getHeader('X-Error-Msg');
     }
     
     print "\n HTTPResponseHeader-Location: " . $response ->getHeader('Location');
     
     $this -> AssertEquals(400, $response -> getStatus());
    }
    
    public function test07CreateConceptWithoutUri() {
        // Create a concept without Uri and with unique PrefLabel. 
         print "\n\n test07 ... \n";
         
        $client = Authenticator::authenticate();
        $randomn = rand(0, 4092);
        $prefLabel= 'testPrefLable_' . $randomn;
        $dateSubmitted = date(DateTime::ISO8601);
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcterms="http://purl.org/dc/terms/" > ' .
               '<rdf:Description>' . 
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>'. 
    '<dcterms:creator>Test</dcterms:creator>' .
    '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' . 
    '<dcterms:dateSubmitted>' . $dateSubmitted . '</dcterms:dateSubmitted>' .
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
        ->setParameterGet('autoGenerateIdentifiers', 'true')
        ->request('POST');
     if ($response->isSuccessful()) {
        print "\n Concept created \n";
        var_dump($response -> getBody());
    
     } else {
    	print  "\n Failed to create concept: " . $response->getHeader('X-Error-Msg');
     }
     $this -> AssertEquals(201, $response -> getStatus());
     
     print "\n HTTPResponseHeader-Location: " . $response ->getHeader('Location');
     
     $namespaces = array (
         "rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
          "skos" => "http://www.w3.org/2004/02/skos/core#",
          "openskos" => "http://openskos.org/xmlns/openskos.xsd"  
     );
     
     $dom = new Zend_Dom_Query();
     $dom->setDocumentXML($response->getBody());
     $dom->registerXpathNamespaces($namespaces);
     
     $elem = $dom->queryXpath('/rdf:RDF');
     $this-> assertEquals($elem ->current()->nodeType, XML_ELEMENT_NODE, 'The root node of the response is not an element');
     
     $resURI = $dom->queryXpath('/rdf:RDF/rdf:Description')->current()->getAttribute("rdf:about");
     $this-> assertNotEquals("", $resURI, "No valid uri for SKOS concept");
     $status = $dom->queryXpath('/rdf:RDF/rdf:Description/openskos:status');
     $this-> assertEquals(1, $status ->count(), "No valid uri for SKOS concept");
     
     print "\n New concept is created with URI $resURI  and status" . $status -> current()->nodeValue;
    }
    
    public function test08CreateConceptWithoutUriAutogenerateFalse() {
        // Create a concept without Uri and with unique PrefLabel.  Autogenerate parameter is false
         print "\n\n test08 ... \n";
        
        $client = Authenticator::authenticate();
        $randomn = rand(0, 4092);
        $prefLabel= 'testPrefLable_' . $randomn;
        $dateSubmitted = date(DateTime::ISO8601);
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcterms="http://purl.org/dc/terms/" > ' .
               '<rdf:Description>' . 
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>'. 
    '<dcterms:creator>Test</dcterms:creator>' .
    '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' . 
    '<dcterms:dateSubmitted>' . $dateSubmitted . '</dcterms:dateSubmitted>' .
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
        ->setParameterGet('autoGenerateIdentifiers', 'false')
        ->request('POST');
     if ($response->isSuccessful()) {
        print "\n Concept created \n";
        var_dump($response -> getBody());
    
     } else {
    	print  "\n Failed to create concept (header) : " . $response->getHeader('X-Error-Msg');
        print  "\n Message: " . $response->getMessage();
     }
     $this -> AssertEquals(400, $response -> getStatus());
     
    }
    
    public function test09CreateConceptWithoutUriPrefLabelExists() {
       // Create a concept without Uri and prefLabel is not unique. 
          
        print "\n\n test09 ... \n";
        $client = Authenticator::authenticate();
        $dateSubmitted = date(DateTime::ISO8601);
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcterms="http://purl.org/dc/terms/" > ' .
               '<rdf:Description>' . 
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>'. 
    '<dcterms:creator>Test</dcterms:creator>' .
    '<skos:prefLabel xml:lang="nl">testconcept2</skos:prefLabel>' . 
    '<dcterms:dateSubmitted>' . $dateSubmitted . '</dcterms:dateSubmitted>' .
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
        ->setParameterGet('autoGenerateIdentifiers', 'true')
        ->request('POST');
     if ($response->isSuccessful()) {
        print "\n Concept created \n";
        var_dump($response -> getBody());
    
     } else {
    	print  "\n Failed to create concept (header) : " . $response->getHeader('X-Error-Msg');
        print  "\n Message: " . $response->getMessage();
     }
     $this -> AssertEquals(409, $response -> getStatus());
     
    }
    
    public function test10CreateConceptWithoutUriButWithNotationUniquePrefLabel() {
        // Create a concept without Uri (no rdf:about), but with notation. prefLabel is unique. 
        
        print "\n\n test10 ... \n";
        $client = Authenticator::authenticate();
        $randomn = rand(0, 4092);
        $prefLabel= 'testPrefLable_' . $randomn;
        $dateSubmitted = date(DateTime::ISO8601);
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcterms="http://purl.org/dc/terms/" > ' .
               '<rdf:Description>' . 
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>'. 
    '<dcterms:creator>Test</dcterms:creator>' .
    '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' . 
    '<dcterms:dateSubmitted>' . $dateSubmitted . '</dcterms:dateSubmitted>' .
    '<skos:inScheme rdf:resource="http://data.beeldengeluid.nl/gtaa/GeografischeNamen"/>'.
    '<skos:notation>1</skos:notation>'.
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
        ->setParameterGet('autoGenerateIdentifiers', 'true')
        ->request('POST');
     if ($response->isSuccessful()) {
        print "\n Concept created \n";
        var_dump($response -> getBody());
    
     } else {
    	print  "\n Failed to create concept (header) : " . $response->getHeader('X-Error-Msg');
        print  "\n Message: " . $response->getMessage();
     }
     $this -> AssertEquals(400, $response -> getStatus());
     
    }
    
    
}

?>