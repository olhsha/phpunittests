<?php

require_once dirname(__DIR__) . '/Utils/Authenticator.php';

class UpdateConceptTest extends PHPUnit_Framework_TestCase {

    public function test11RetrieveConceptFiletrStatus() {
        // Use API to search for concept and filter on status
        // todo: test additionele zoek parameters
        print "\n" . "Test: get concept via filters";
        $client = Authenticator::authenticate();
        //prepare and send request 

        $uri = BASE_URI_ . '/public/api/find-concepts?q=prefLabel:' . CONCEPT_prefLabel . '&status:' . CONCEPT_status_forfilter . '&tenant:' . TENANT . '&inScheme:' . CONCEPT_schema_forfilter;
        print "\n filtered request's uri: " . $uri . "\n";

        $client->setUri($uri);
        $client->setConfig(array(
            'maxredirects' => 0,
            'timeout' => 30));
        $client->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Content-Type' => 'application/xml',
            'Accept-Language' => 'nl,en-US,en',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection' => 'keep-alive')
        );
        $response = $client->request(Zend_Http_Client::GET);

        // analyse respond
        print "\n get status: " . $response->getMessage(). "\n";
        $this->AssertEquals(200, $response->getStatus());

        $namespaces = array(
            "rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
            "skos" => "http://www.w3.org/2004/02/skos/core#",
            "openskos" => "http://openskos.org/xmlns/openskos.xsd"
        );

        $dom = new Zend_Dom_Query();
        $dom->setDocumentXML($response->getBody());
        $dom->registerXpathNamespaces($namespaces);

        $elem = $dom->queryXpath('/rdf:RDF');
        $this->assertEquals(XML_ELEMENT_NODE, $elem->current()->nodeType, 'The root node of the response is not an element');
        $this->assertEquals(1, $elem->current()->getAttribute("openskos:numFound"));

        $resDescr = $dom->queryXpath('/rdf:RDF/rdf:Description');
        $i = 0;
        $l = $resDescr->count();
        $resDescr->rewind();
        while ($i < $l) {
            $labels = $resDescr->current()->getElementsByTagName("altLabel");
            //print "\n val:" . $labels ->item(0) ->textContent;
            $randomn = rand(0, 4096);
            $labels ->item(0) ->nodeValue = "test-1-" . $randomn;
            $doc = $resDescr->current()->ownerDocument; 
            $xml = $doc ->saveXML();
            var_dump($xml);
            
            // try $newdom isntead of $dom, which can be corrupted
            //$dom = new DOMDocument('1.0', 'utf-8');
            //$rdf = $dom -> createElement("rdf:RDF");
            //$dom ->importNode($newDescr, TRUE);// appendChild($rdf);
            //$rdf ->appendChild($newDescr);
            //$xml = $dom->saveXML();
            //var_dump($xml);
            
            $client->setUri(BASE_URI_ . "/public/api/concept?");
            $client->setConfig(array(
                'maxredirects' => 0,
                'timeout' => 30));

            $response = $client
                    ->setEncType('text/xml')
                    ->setRawData($xml)
                    ->setParameterGet('tenant', TENANT)
                    ->setParameterGet('collection', COLLECTION_1_code)
                    ->setParameterGet('key', API_KEY)
                    ->request(Zend_Http_Client::PUT);

            print "\n Update response message: " . $response->getMessage();
            
            $this->AssertEquals(200, $response->getStatus(), 'Update request returned worng status code');
            $resDescr->next();
            $i++;
        }
    }

}

?>