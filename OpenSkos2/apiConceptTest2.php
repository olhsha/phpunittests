<?php

require_once dirname(__DIR__) . '/Utils/Authenticator.php';

class ApiConceptTest extends PHPUnit_Framework_TestCase {
	public $client;
	
    protected function setUp() {
    	$this->client = Authenticator::authenticate();
    }
    
    public function testFindConcept() {
    	/*1.- voor de normalisatie van termen bij instroom:
    	* HTTP GET: /api/find-concepts?q=Term
    	* 		Parameters=q
    	*/
    	print "\nTest /api/find-concepts?q=Term";
        $this->client->setUri(BASE_URI_ . '/api/find-concepts?q=' . QUERY_term);
       $response = $this->client -> request(Zend_Http_Client::GET);
       $this -> AssertEquals(200, $response->getStatus());
       $this->assertionsForXMLSearchResults($response);
    }
    
    public function testFindConceptJSONP() {
    	/*  HTTP GET: /api/find-concepts in jsonP format en q=’term’
    	*  	Parameters: q, format, callback
    	*/
    	print "\nTest /api/find-concepts in jsonP format en q=’term’";
    }
    
    public function testApiConceptUpdate() {
    	/* 2. 'Hidden label generator' in OpenSKOS (eigen instantie Beeld en Geluid):
    	*  API-update (om geupdate concepten te updaten in OpenSKOS)
    	*  HTTP PUT: /api/concept?
    	*  	Parameters: tenant, collection, key
    	*/
    	print "\nTest Update: HTTP PUT /api/concept?";
    }
    
    public function testApiAutocomplete() {
    	/*  3. Sprekerherkenning:
    	*  HTTP GET: /api/autocomplete/[term] in jsonP format
    	*  	Parameters: callback, format??
    	*/
    	print "\nTest /api/autocomplete/[term] in jsonP format";
    }
    
    public function testApiConceptCreate() {
    	/*  4. POMS integratie
    	*  HTTP POST: ./api/concept?tenant=beng&key=[key]&collection=gtaa&autoGenerateIdentifiers=true
    	*  	Parameters: tenant, collection, autoGenerateIdenttifiers
    	*/
    	print "\nTest Create: HTTP POST api/concept?tenant=beng&key=[key]&collection=gtaa&autoGenerateIdentifiers=true";
    }
    
    public function testFindConcepts() {
    	/* POMS integratie
    	*  HTTP GET: ./api/find-concepts?tenant=beng&collection=gtaa&q=status:(candidate OR approved OR not_compliant OR rejected OR deleted) AND inScheme:”http://data.beeldengeluid.nl/gtaa/Persoonsnamen AND (*[input]* OR “[input]”)&fl=uuid,uri,prefLabel,altLabel,hiddenLabel&rows=[rows]
    	*  	Parameters: tenant, collection, q, fl, row
    	*/
    	print "\nTest POMS query: /api/find-concepts?tenant=beng&collection=gtaa&q=status:(candidate OR approved OR not_compliant OR rejected OR deleted) AND inScheme:”http://data.beeldengeluid.nl/gtaa/Persoonsnamen AND (*[input]* OR “[input]”)&fl=uuid,uri,prefLabel,altLabel,hiddenLabel&rows=[rows]";
    }
    
	/* XML assertions
	 */
    private function assertionsForXMLSearchResults($response) {
    	$dom = new Zend_Dom_Query();
    	$dom->setDocumentXML($response->getBody());
    	//$dom->registerXpathNamespaces($namespaces);
    	$xml = $response->getBody();
    	
    	//corretions should be removed after bug fixing
    	$xmlCorrected = str_replace ('xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#type"', 'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"', $xml);
    	//$xmlCorrected = str_replace ('dc:creator', 'dcterms:creator', $xmlCorrected);
    	//var_dump($xmlCorrected);
    	$dom->setDocumentXML($xmlCorrected);
    	//

    	$results1 = $dom->query('rdf:RDF');
    	$count =  intval($results1->current()-> getAttribute('openskos:numFound'));
    	//$this->assertInternalType('integer',$count);
    	
    	$xpathQuery = '/rdf:RDF/rdf:Description';
    	$results = $dom->queryXpath($xpathQuery);
    	$this->assertCount($count,$results,"Number of records doesn't match openskos:numFound.");
    	
    }
    
    private function assertionsForXMLRdfDescription($response) {
    	//	rdf:about, skos:prefLabel, skos:inScheme,
    	$dom = new Zend_Dom_Query();
    	$dom->setDocumentXML($response->getBody());
    	$xml = $response->getBody();
    	 
    	//corretions should be removed after bug fixing
    	$xmlCorrected = str_replace ('xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#type"', 'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"', $xml);
    	$dom->setDocumentXML($xmlCorrected);
    	//
    	
    	$results = $dom->query('rdf:Description');
    	foreach ($results as $description) {
    		print "lmnlkvnflknbxnb kxn";
    		print_r ($description);
 /*    		$xpathQuery = 'skos:prefLabel[@xml:lang="nl"]';
    		$results = $dom->queryXpath($xpathQuery);
    		$this->assertCount(1,$results);
    		
    		$results1 = $dom->query('skos:Concept');
    		$this -> AssertEquals(CONCEPT_Description_about, $results1 -> current()-> getAttribute('rdf:about'));
    		
    		$results3 = $dom->query('skos:notation');
    		$this -> AssertEquals(CONCEPT_notation, $results3 -> current()-> nodeValue);
    		
    		$results4 = $dom->query('skos:inScheme');
    		$this -> AssertEquals(CONCEPT_NUMBER_inScheme, count($results4));
    		
    		$results5 = $dom->query('skos:topConceptOf');
    		$this -> AssertEquals(CONCEPT_NUMBER_topConceptOf, count($results5));
    		
    		$results6 = $dom->query('skos:prefLabel');
    		$this -> AssertEquals(CONCEPT_prefLabel_lang, $results6 -> current()-> getAttribute('xml:lang'));
    		$this -> AssertEquals(CONCEPT_prefLabel, $results6 -> current()-> nodeValue); */
    		
    	}
    }
 
}
?>

