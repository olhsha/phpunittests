<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once dirname(__DIR__) . '/Utils/Authenticator.php';
require_once dirname(__DIR__) . '/Utils/RequestResponse.php'; 

class CreateConcept2Test extends PHPUnit_Framework_TestCase {
    
    
     private function test01CreateConceptWithoutURIWithDateAccepted2() {
        //CreateConceptTest::test01CreateConceptWithoutURIWithDateAccepted();
        // Create new concept with dateAccepted filled (implicit status APPROVED). This should not be possible. 

        $client = Authenticator::authenticate();
        $randomn = rand(0, 2048);
        $prefLabel = 'testPrefLable_' . $randomn;
        $dateSubmitted = date(DateTime::ISO8601); //'2015-10-01T15:06:58Z';//
        $dateAccepted = '2015-10-02T10:31:35Z'; // date(DateTime::ISO8601);'2015-10-01T15:06:58Z';
        $set = BASE_URI_ . CONCEPT_collection;

        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:ns0="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<ns0:terms-dateSubmitted>' . $dateSubmitted . '</ns0:terms-dateSubmitted>' .
                '<ns0:terms-dateAccepted>' . $dateAccepted . '</ns0:terms-dateAccepted>' .
                '<openskos:set rdf:resource="' . $set .'"/>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($client, $xml, "true");

        if ($response->isSuccessful()) {
            print "\n Concept created \n";
            var_dump($response->getBody());
        } else {
            print "\n Failed to create concept, error-message: ";
            var_dump($response->getHeader('X-Error-Msg'));
        }
        print "\n\n Response body (should be the concept, if success): \n";
        var_dump($response->getBody());
        print "\n";
        $this->AssertEquals(409, $response->getStatus());
    }

    private function test02CreateConceptWithoutUriWithoutDateAccepted() {
        // Create a concept without Uri and without dateAccepted , but with UniquePrefLabel. Check XML response.
        $client = Authenticator::authenticate();
        $randomn = rand(0, 2048);
        $prefLabel = 'testPrefLable_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;

        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<openskos:set rdf:resource="' . $set . '"/>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($client, $xml, "true");

        if ($response->isSuccessful()) {
            print "\n Concept created \n";
            var_dump($response->getBody());
        } else {
            print "\n Failed to create concept: " . $response->getHeader('X-Error-Msg');
        }
        $this->AssertEquals(201, $response->getStatus());
        print "\n HTTPResponseHeader-Location: " . $response->getHeader('Location');
        $namespaces = RequestResponse::setNamespaces();
        $this->CheckCreatedConcept($response, $namespaces);
    }

   private function test03CreateConceptWithURIAlreadyExists() {
        // test if creating a new concept with an URI that already exists, fails
        $client = Authenticator::authenticate();
        $randomn = rand(0, 2048);
        $prefLabel = 'testPrefLable_' . $randomn;
        $notation = 'notation_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        $conceptId = $set . "/" . $notation;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:openskos="http://openskos.org/xmlns/openskos.xsd" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcterms="http://purl.org/dc/terms/" > ' .
                '<rdf:Description rdf:about="' . $conceptId . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<skos:notation>' . $notation . '</skos:notation>' .
                '<openskos:set rdf:resource="' . $set . '"/>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        // create the first concept with whoch we will compare
        $response = RequestResponse::CreateConceptRequest($client, $xml, "false");

        if ($response->isSuccessful()) {
            print "\n First concept created \n";
            $xml2= str_replace ('testPrefLable_', '_another_testPrefLable_', $xml);
           $response2 = RequestResponse::CreateConceptRequest($client, $xml2, "false");
           $this->AssertEquals(409, $response2->getStatus());

        } else {
            print "\n Failed to create the first concept concept: " . $response->getHeader('X-Error-Msg') . "\n";
        }

       
    }

    private function test04CreateConceptWithoutURIUniquePrefLabelNoApiKey() {
        // create concept without URI. but with unique prefLabel. Api Key is missng.
        // todo: veoeken met verkeerde parameters moeten foutcode opleveren (collection, tenant)
        $client = Authenticator::authenticate();
        $randomn = rand(0, 2048);
        $prefLabel = 'testPrefLable_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<openskos:set rdf:resource="' . $set . '"/>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptNoApikeyRequest($client, $xml, "true");

        if ($response->isSuccessful()) {
            print "\n Concept created \n";
            var_dump($response->getBody());
        } else {
            print "\n Failed to create concept, message: " . $response->getHeader('X-Error-Msg') . "\n";
            print "\n Failed to create concept, message : " . $response->getStatus() . "\n";
        }
        $this->AssertEquals(412, $response->getStatus());
    }

    private function test05CreateConceptWithURIUniquePrefLabel() {
        // Create concept with URI and with unique prefLabel, including skos:notation
        print "\n\n test05 ... \n";
        $client = Authenticator::authenticate();
        $randomn = rand(0, 4096);
        $prefLabel = 'testPrefLable_' . $randomn;
        $notation = 'testNotation_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        $about = $set . '/' . $notation;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description rdf:about="' . $about . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<skos:notation>' . $notation . '</skos:notation>' .
                '<openskos:set rdf:resource="' . $set . '"/>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($client, $xml, "false");

        if ($response->isSuccessful()) {
            print "\n Concept created \n";
            var_dump($response->getBody());
        } else {
            print "\n Failed to create concept: " . $response->getHeader('X-Error-Msg');
        }

        $this->AssertEquals(201, $response->getStatus());
        print "\n HTTPResponseHeader-Location: " . $response->getHeader('Location');
        $namespaces = RequestResponse::setNamespaces();
        $this->CheckCreatedConcept($response, $namespaces);
    }

    private function test05BCreateConceptWithURIUniquePrefLabel() {
        // Create concept with URI and with unique prefLabel, without skos:notation
        print "\n\n test05B ... \n";
        $client = Authenticator::authenticate();
        $randomn = rand(0, 4096);
        $prefLabel = 'testPrefLable_' . $randomn;
        $notation = 'testNotation_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        $about = $set . '/' . $notation;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description rdf:about="' . $about . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<openskos:set rdf:resource="'. $set . '"/>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($client, $xml, "false");

        if ($response->isSuccessful()) {
            print "\n Concept created \n";
            var_dump($response->getBody());
        } else {
            print "\n Failed to create concept, message: " . $response->getHeader('X-Error-Msg') . "\n";
            print "\n Failed to create concept, message : " . $response->getStatus() . "\n";
        }

        print "\n HTTPResponseHeader-Location: " . $response->getHeader('Location');
        $this->AssertEquals(400, $response->getStatus());
    }

   public function test05CCreateConceptWithURIUniquePrefLabel() {
        // Create concept with URI and with unique prefLabel, with duplicate skos:notation
        print "\n\n test05C ... \n";
        $client = Authenticator::authenticate();
        $randomn = rand(0, 4096);
        $prefLabel = 'testPrefLable_' . $randomn;
        $notation = 'testNotation_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        $about = $set . '/' . $notation;
        $xml0 = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description rdf:about="' . $about . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<skos:notation>' . $notation . '</skos:notation>' .
                '<openskos:set rdf:resource="' . $set . '"/>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response0 = RequestResponse::CreateConceptRequest($client, $xml0, "false");
        if ($response0->isSuccessful()) {
            print "\n First concept created \n";
            $xml1 = str_replace('testPrefLable_', '_another_testPrefLable_', $xml0);
            $response1 = RequestResponse::CreateConceptRequest($client, $xml1, "false");
            print "\n HTTPResponseHeader-Location: " . $response1->getHeader('Location');
            $this->AssertEquals(400, $response1->getStatus());
        } else {
            print "\n Failed to create first concept, message: " . $response0->getHeader('X-Error-Msg') . "\n";
            print "\n Failed to create first concept, message : " . $response0->getStatus() . "\n";
        }
    }

    private function test06CreateConceptWithURIUniquePrefLabel() {
        // Create concept without URI about, the xml is wrong
        print "\n\n test06 ... \n";
        $client = Authenticator::authenticate();
        $wrongXml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description>' .
                //'<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>'. 
                '<skos:prefLabel xml:lang="nl">blablabla</skos:prefLabel>' .
                '<openskos:set rdf:resource="http://192.168.99.100/api/collections/mi:collection"/>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($client, $wrongXml, "true");
        if ($response->isSuccessful()) {
            print "\n Concept created \n";
            var_dump($response->getBody());
        } else {
            print "\n Failed to create concept, message: " . $response->getHeader('X-Error-Msg') . "\n";
            print "\n Failed to create concept, message : " . $response->getStatus() . "\n";
        }

        print "\n HTTPResponseHeader-Location: " . $response->getHeader('Location');
        $this->AssertEquals(400, $response->getStatus());
    }

    private function test07CreateConceptWithoutUri() {
        // Create a concept without Uri and with unique PrefLabel. 
        print "\n\n test07 ... \n";

        $client = Authenticator::authenticate();
        $randomn = rand(0, 4092);
        $prefLabel = 'testPrefLable_' . $randomn;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<dcterms:creator>Test</dcterms:creator>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<openskos:set rdf:resource="http://192.168.99.100/api/collections/mi:collection"/>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($client, $xml, "true");

        if ($response->isSuccessful()) {
            print "\n Concept created \n";
            var_dump($response->getBody());
        } else {
            print "\n Failed to create concept: " . $response->getHeader('X-Error-Msg');
        }

        $this->AssertEquals(201, $response->getStatus());
        print "\n HTTPResponseHeader-Location: " . $response->getHeader('Location');
        $namespaces = RequestResponse::setNamespaces();
        $this->CheckCreatedConcept($response, $namespaces);
    }

    private function test08CreateConceptWithoutUriAutogenerateFalse() {
        // Create a concept without Uri and with unique PrefLabel.  Autogenerate parameter is false
        print "\n\n test08 ... \n";

        $client = Authenticator::authenticate();
        $randomn = rand(0, 4092);
        $prefLabel = 'testPrefLable_' . $randomn;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($client, $xml, "false");
        if ($response->isSuccessful()) {
            print "\n Concept created \n";
            var_dump($response->getBody());
        } else {
            print "\n Failed to create concept, message: " . $response->getHeader('X-Error-Msg') . "\n";
            print "\n Failed to create concept, message : " . $response->getStatus() . "\n";
        }

        print "\n HTTPResponseHeader-Location: " . $response->getHeader('Location');
        $this->AssertEquals(400, $response->getStatus());
    }

    private function test09CreateConceptWithoutUriPrefLabelExists() {
        // Create a concept without Uri and prefLabel is not unique. 

        print "\n\n test09 ... \n";
        $client = Authenticator::authenticate();

        $randomn = rand(0, 4092);
        $prefLabel = 'testPrefLable_' . $randomn;

        // create the first instance of the concept
        $xml0 = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<openskos:set rdf:resource="http://192.168.99.100/api/collections/mi:collection"/>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response0 = RequestResponse::CreateConceptRequest($client, $xml0, "true");
        print "\n Test setting:  the first concept is prepared ?? \n";
        var_dump($response0->getBody());

        if ($response0->getStatus() == 201) {
            // we can proceed with the test
            $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                    '<rdf:Description>' .
                    '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                    '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                    '<openskos:set rdf:resource="http://192.168.99.100/api/collections/mi:collection"/>' .
                    '</rdf:Description>' .
                    '</rdf:RDF>';

            $response = RequestResponse::CreateConceptRequest($client, $xml, "true");
            if ($response->isSuccessful()) {
                print "\n Concept created \n";
                var_dump($response->getBody());
            } else {
                print "\n Failed to create concept, message: " . $response->getHeader('X-Error-Msg') . "\n";
                print "\n Failed to create concept, message : " . $response->getStatus() . "\n";
            }

            print "\n HTTPResponseHeader-Location: " . $response->getHeader('Location');
            $this->AssertEquals(409, $response->getStatus());
        } else {
            print "ERROR while creating the first test concept, cannpt proceed woth the test! ";
        }
    }

   private function test10CreateConceptWithoutUriButWithNotationUniquePrefLabel() {
        // Create a concept without Uri (no rdf:about), but with notation. prefLabel is unique. 

        print "\n\n test10 ... \n";
        $client = Authenticator::authenticate();
        $randomn = rand(0, 4092);
        $prefLabel = 'testPrefLable_' . $randomn;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<openskos:set rdf:resource="http://192.168.99.100/api/collections/mi:collection"/>' .
                '<skos:notation>notation-xxx</skos:notation>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($client, $xml, "true");
        if ($response->isSuccessful()) {
            print "\n Concept created \n";
            var_dump($response->getBody());
        } else {
            print "\n Failed to create concept, message: " . $response->getHeader('X-Error-Msg') . "\n";
            print "\n Failed to create concept, message : " . $response->getStatus() . "\n";
        }

        print "\n HTTPResponseHeader-Location: " . $response->getHeader('Location');
        $this->AssertEquals(400, $response->getStatus());
    }

    private function test10BCreateConceptWithoutUriButWithoutNotationUniquePrefLabel() {
        // Create a concept without Uri (no rdf:about), and no notation. prefLabel is unique. 

        print "\n\n test10 ... \n";
        $client = Authenticator::authenticate();
        $randomn = rand(0, 4092);
        $prefLabel = 'testPrefLable_' . $randomn;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<openskos:set rdf:resource="http://192.168.99.100/api/collections/mi:collection"/>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($client, $xml, "false");
        if ($response->isSuccessful()) {
            print "\n Concept created \n";
            var_dump($response->getBody());
        } else {
            print "\n Failed to create concept, message: " . $response->getHeader('X-Error-Msg') . "\n";
            print "\n Failed to create concept, message : " . $response->getStatus() . "\n";
        }

        print "\n HTTPResponseHeader-Location: " . $response->getHeader('Location');
        $this->AssertEquals(400, $response->getStatus());
    }

    private function CheckCreatedConcept($response, $namespaces) {
        $dom = new Zend_Dom_Query();
        $dom->setDocumentXML($response->getBody());
        $dom->registerXpathNamespaces($namespaces);

        $elem = $dom->queryXpath('/rdf:RDF');
        $this->assertEquals($elem->current()->nodeType, XML_ELEMENT_NODE, 'The root node of the response is not an element');

        // $description = $dom->queryXpath('/rdf:RDF/rdf:Description'); ???
        $conceptElement = $dom->queryXpath('/rdf:RDF/skos:Concept');
        $this->assertEquals(1, $conceptElement->count(), "Concept element is not declared");
        $resURI = $conceptElement->current()->getAttribute("rdf:about");
        $this->assertNotEquals("", $resURI, "No valid uri for SKOS concept");
        $status = $dom->queryXpath('/rdf:RDF/skos:Concept/openskos:status');
        $this->assertEquals(1, $status->count(), "No valid uri for openkos:status");
        $this->assertEquals("Candidate", $status->current()->nodeValue, "Satus is not Candidate, as it must be by just created concept.");
        print "\n New concept is created with URI $resURI  and status" . $status->current()->nodeValue;
    }

}

