<?php

require_once __DIR__."/../vendor/autoload.php"; 

class ImportExportTest extends \PHPUnit_Framework_TestCase {
    
    protected $notationg = "";
    protected $clientg;
    
    
    public function testImport() {
        print "\n" . "Testing import ... ";
        // retrieve the current amount of tripls for the "object"
        
        $results0 = $this -> sparqlRetrieveTriplesForCreator(CREATOR);
        $before=count($results0);
        print("\n The current amount of triples for the given creator is " . $before);
        
        //create import job
        $this -> authenticate();
        $response = $this -> importFromRawData();
        
       // send import job 
       if ($response->getStatus() == 200) {
            $output = array(
                '0' => "The ouput of sending jobs: "
            );
            $retvar = 0;

            $sendjob = exec(PHP_JOBS_PROCESS, $output, $retvar);
        }
       
        // check 1
        // retrieve imported concept: the amount of the concepts created by CREATOR must increase by 1
        $results1= $this ->sparqlRetrieveTriplesForCreator(CREATOR);
        print("\n The new  amount of triples for the given creator is " . count($results1) . "\n");
        // Asserting
        $this -> AssertEquals($before+1, count($results1));
        
        // check 2
        $results2= $this ->sparqlRetrieveTriplesForNotation($this -> notationg);
        $this -> AssertEquals(1, count($results2));
    }
    
    
    
    private function sparqlRetrieveTriplesForCreator($creator){
        $query = "select ?s ?p  ?o where {?s <http://purl.org/dc/terms/creator> '" . $creator . "'@en . }";
        print $query . "\n";
        $sparqlClient = new \EasyRdf\Sparql\Client('http://192.168.99.100:3030/openskos/query'); 
        $result = $sparqlClient -> query($query);
        return $result;
    }
    
   private function sparqlRetrieveTriplesForNotation($notation){
       // alternative query (the fuseki's query is the same except that # must be encoded as %23 or it will be understood as eof
       //$query = "prefix skos: <http://www.w3.org/2004/02/skos/core#> select ?s ?p  ?o where {?s  skos:notation '" . $notation .  "' . }";
       \EasyRdf\RdfNamespace::set('skos', 'http://www.w3.org/2004/02/skos/core#');
       $query = "select ?s ?p  ?o where {?s  skos:notation '" . $notation .  "' . }";
        $sparqlClient = new \EasyRdf\Sparql\Client('http://192.168.99.100:3030/openskos/query'); 
        $result = $sparqlClient -> query($query);
        return $result;
    }
    
    private function authenticate() {
        $this -> clientg = new Zend_Http_Client();
        $this -> clientg->setCookieJar();
        $this -> clientg -> setUri('http://192.168.99.100/public/editor/login/authenticate');
        $this -> clientg->setConfig(array(
            'maxredirects' => 10,
            'timeout' => 300));
        $this -> clientg->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
                'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept-Language'=>'en-US,en',
            'Accept-Encoding'=>'gzip, deflate',
            'Host' => '192.168.99.100',
            'Referer' => 'http://192.168.99.100/public/editor/login',
            'Connection'=>'keep-alive')
        );
        
        $this -> clientg -> setParameterPost('username', USERNAME);
        $this -> clientg -> setParameterPost('tenant', TENANT);
        $this -> clientg -> setParameterPost('password', PASSWORD);
        $this -> clientg -> setParameterPost('rememberme', '0');
        $this -> clientg -> setParameterPost('login', 'Login');
        $responseAuth = $this -> clientg -> request(Zend_Http_Client::POST);
        print "\n Authentication response status: " . $responseAuth -> getStatus();
        print "\n Authentication response message: " . $responseAuth -> getMessage();
    }
    private function importFromRawData() {
        $randomn = rand(0, 2048);
        $tag= 'Import' . $randomn;
        $notation = 'textCorpus' . $randomn;
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . 
            '<rdf:RDF xmlns:dcterms="http://purl.org/dc/terms/" ' .
              'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"' .
              'xmlns:skos="http://www.w3.org/2004/02/skos/core#">' .
  '<rdf:Description xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"' .
  'rdf:about="http://hdl.handle.net/11148/CCR_C-test_' . $randomn .'">' .
  '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                 '<dcterms:creator>' . CREATOR . '</dcterms:creator>' . 
  '<skos:notation>' . $notation . '</skos:notation>' .
  '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-DialogueActs_1bb8b49f-7260-6731-6479-408c29cead73"/>' .
  '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-LexicalSemantics_0d519a3c-85a6-ea17-d93c-8b89339ffc88"/>' .
  '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-Metadata_deedbe7e-9a1d-4388-2857-ad0daaf06793"/>' .
  '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-Morphosyntax_c99c78ee-1425-c8f3-33e3-fe2a4b2ec7ca"/>' .
  '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-SemanticContentRepresentation_37ab80c4-cf9f-18dd-c319-e6554b1d9462"/>' .
  '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-Syntax_ba63dab1-103c-f8ee-81c3-f32a101e5c96"/>' .
  '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-Terminology_bf8fdd3f-0075-bca2-ae35-1070be98f856"/>' .
  '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-Translation_d8214c27-7c8f-9d05-e6ca-ea8fdc922a1c"/>' .
  '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-Metadata_deedbe7e-9a1d-4388-2857-ad0daaf06793"/>' .
  '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-Terminology_bf8fdd3f-0075-bca2-ae35-1070be98f856"/>' .
  '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-Morphosyntax_c99c78ee-1425-c8f3-33e3-fe2a4b2ec7ca"/>' .
  '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-SemanticContentRepresentation_37ab80c4-cf9f-18dd-c319-e6554b1d9462"/>' .
  '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-Syntax_ba63dab1-103c-f8ee-81c3-f32a101e5c96"/>' .
  '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-LexicalSemantics_0d519a3c-85a6-ea17-d93c-8b89339ffc88"/>' .
  '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-DialogueActs_1bb8b49f-7260-6731-6479-408c29cead73"/>' .
  '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-Translation_d8214c27-7c8f-9d05-e6ca-ea8fdc922a1c"/>' .
  '<skos:prefLabel xml:lang="en">' . $tag . '</skos:prefLabel>' .
  '<skos:scopeNote xml:lang="en">A text corpus may be limited according to aspects of subject fields, size or time, e.g. mathematical texts, certain periodicals from 1986 onwards. It is used as source material for further linguistic analysis or terminology work. (source: ISO 1087-2, 2.7)</skos:scopeNote>' .
  '<skos:definition xml:lang="en">A systematic collection of machine-readable texts or parts of text prepared, coded and stored according to predefined rules. (source: ISO 1087-2, 2.7)</skos:definition>' .
  '<dcterms:modified>2015-01-20T13:09:14Z</dcterms:modified>' .
'</rdf:Description>' .
'</rdf:RDF>';
        $boundary = '36374246216810994721943965972';
        $url = 'http://192.168.99.100/public/editor/collections/import/collection/collection';
        
      
        // importeren
        $this -> clientg ->setUri($url);
        $this -> clientg->setConfig(array(
            'maxredirects' => 10,
            'timeout' => 300));
        $this -> clientg->SetHeaders(array(
            'Accept' => 'application/xml+rdf',
                'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
            'Accept-Language'=>'nl,en-US,en',
            'Accept-Encoding'=>'gzip, deflate',
            'Host' => '192.168.99.100',
            'Connection'=>'keep-alive')
        );
        
        $part1 = 'Content-Disposition: form-data; name="MAX_FILE_SIZE"
           

10485760';
        
        $part2 =  'Content-Disposition: form-data; name="xml"; filename="tttt.xml"
Content-Type: text/xml

' . $xml ; 
        
        $part3 = 'Content-Disposition: form-data; name="status"

candidate';
        
        $part4 = 'Content-Disposition: form-data; name="ignoreIncomingStatus"

0';
        
        $part5 = 'Content-Disposition: form-data; name="lang"

en';

        $part6 = 'Content-Disposition: form-data; name="toBeChecked"

0';

        $part7 = 'Content-Disposition: form-data; name="purge"

0';

        $part8 ='Content-Disposition: form-data; name="onlyNewConcepts"

0';

        $part9 ='Content-Disposition: form-data; name="submit"

Submit';
        
        $boundary = "--" . $boundary;
        $boundaryLn = $boundary . "\n";
        $lnBoundaryLn = "\n" . $boundaryLn;
        $postData = $boundaryLn . $part1 . $lnBoundaryLn . $part2 . $lnBoundaryLn . $part3 . $lnBoundaryLn .
                $part4 . $lnBoundaryLn  . $part5 . $lnBoundaryLn  . $part6 . $lnBoundaryLn  . 
                $part7 . $lnBoundaryLn . $part8 . $lnBoundaryLn  . $part9. "\n" . $boundary . "--";  
        
        
        $this -> clientg -> setRawData($postData);
        $response = $this -> clientg -> request(Zend_Http_Client::POST);
        $status = $response -> GetStatus();
        print "\n Create import response status: " . $status;
        print "\n Create import message: " . $response -> GetMessage();
        $this -> var_error_log("\n Response body ", $response->getBody(), __DIR__."/ImportResponse.html");
        if ($status == 200) {
            $this -> notationg = $notation;
            print "\n Imported concept's notation is " .  $notation;
        }
        return $response;
    }
    
     protected function var_error_log($message, $object, $fileName){
        ob_start(); // start buffer capture
        var_dump($object);
        $contents = ob_get_contents();
        ob_end_clean();
        error_log($message . $contents, 3, $fileName);
    }
}
?>
