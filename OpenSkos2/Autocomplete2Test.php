<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__DIR__) . '/Utils/RequestResponse.php'; 

class Autocomplete2Test extends PHPUnit_Framework_TestCase {
  
    private static $client;
    private static $success;
    private static $prefs;
    
   public static function setUpBeforeClass() {
       
        self::$client = new Zend_Http_Client();
        self::$client->setConfig(array(
            'maxredirects' => 0,
            'timeout' => 30));
        self::$client->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
                'Content-Type' => 'application/json',
            'Accept-Language'=>'nl,en-US,en',
            'Accept-Encoding'=>'gzip, deflate',
            'Connection'=>'keep-alive')
        );
   
        // create test concepts
        
        $letters = range('a', 'z'); 
        self::$prefs[0] = "t" . uniqid();
        $i = 1;
        
        foreach ($letters as $letter) {
            self::$prefs[$i] = self::$prefs[$i-1] . $letter;
            $randomn = rand(0, 10000);
            $prefLabel = self::$prefs[$i] . $randomn;
            $altLabel = 'alt-Lable_' . self::$prefs[$i] . $randomn;
            $hiddenLabel = 'hidden-Lable_' . self::$prefs[$i] . $randomn;
            $notation = 'test-notation-' . self::$prefs[$i] . $randomn;
            $uuid = uniqid();
            $about = BASE_URI_ . CONCEPT_collection . "/" . $notation;
            $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                    '<rdf:Description rdf:about="' . $about . '">' .
                    '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                    '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                    '<skos:altLabel xml:lang="nl">' . $altLabel . '</skos:altLabel>' .
                    '<skos:hiddenLabel xml:lang="nl">' . $hiddenLabel . '</skos:hiddenLabel>' .
                    '<openskos:set rdf:resource="' . BASE_URI_ . CONCEPT_collection . '"/>' .
                    '<openskos:uuid>' . $uuid . '</openskos:uuid>' .
                    '<openskos:status>approved</openskos:status>' .
                    '<skos:notation xml:lang="nl">' . $notation . '</skos:notation>' .
                    '<skos:inScheme  rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                    '<skos:topConceptOf rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                    '<skos:definition xml:lang="nl">testje (voor def ingevoegd)</skos:definition>' .
                    '</rdf:Description>' .
                    '</rdf:RDF>';

            $response0 = RequestResponse::CreateConceptRequest(self::$client, $xml, "false");
            if ($response0 ->getStatus() !=201) {
                $this -> failureMessaging($response0);
                self::$success = false;
                return;
            } 
            $i++;
        }
          
        self::$success = true;
         
    }
    
 
    public function testAutocompletechekPrefLabel() {
        print "\n testAutocompletePrefLabel";
        $this ->autocompleteAlphabeticWithPrefix("", null);
    }
     
    public function testAutocompleteSearchPrefLabelExplicit() {
        print "\n testAutocompletePrefLabel";
        $this ->autocompleteAlphabeticWithPrefix("", "?searchLabel=prefLabel");
    }
    
    public function testAutocompleteCheckPrefLabelReturnAltExplicit() {
        print "\n testAutocompletePrefLabel";
        $this ->autocompleteAlphabeticWithPrefix("", "?returnLabel=altLabel");
        // todo: check if the retunr label are as expected, starting with altLa
    }
   
    public function testAutocompleteCheckAltLabel() {
        print "\n testAutocompleteAltLabel";
        $this ->autocompleteAlphabeticWithPrefix('alt-Lable_', null);
    }
     
    public function testAutocompleteCheckHiddenLabel() {
        print "\n testAutocompleteHiddenLabel";
        $this ->autocompleteAlphabeticWithPrefix('hidden-Lable_', null);
    }
    
    // todo: add tests for language
    
    // todo: add test for format-html that should fail
    
    private function autocompleteAlphabeticWithPrefix($prefix, $parameterString) {
        if (self::$success) {
            $lim = count(self::$prefs) - 1; // must be 26
            for ($i=1; $i<=$lim; $i++) {
                $response = RequestResponse::AutocomleteRequest(self::$client, $prefix . self::$prefs[$i], $parameterString);
                if ($response->getStatus() != 200) {
                    $this->failureMessaging($response);
                }
                $this->AssertEquals(200, $response->getStatus());
                $json = $response->getBody();
                $array = json_decode($json, true);
                //var_dump($array);
                $this->AssertEquals(27 - $i, count($array));
            }
        } else {
            print "\n Cannot perform the test because something is wrong with creating test concepts, see above. \n ";
        } 
    }
    
    private function failureMessaging($response) {
        print "\n Failed to create concept, error message: " . $response->getHeader('X-Error-Msg');
        print "\n Failed to create concept, response message: " . $response->getMessage();
        print "\n Failed to create concept, response body: " . $response->getBody();
    }

}