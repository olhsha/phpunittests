<?php

require_once dirname(__DIR__) . '/Utils/Authenticator.php';

class ApiConceptTest extends PHPUnit_Framework_TestCase {
	public $client;
	public $getClient;

	protected function setUp() {
		//$this->client = Authenticator::authenticate();
		$this->getClient = new Zend_Http_Client(BASE_URI_ . '/api/find-concepts' );
/* 		$this->getClient->SetHeaders(array(
	 		'Accept' => 'text/html,application/xhtml+xml,application/xml',
			'Content-Type' => 'application/xml+rdf',
			'Accept-Language'=>'en-US,en',
			'Accept-Encoding'=>'gzip, deflate',
			'Connection'=>'keep-alive')
		); */
	}

	public function test01FindConcepts() {
		/* 1.- voor de normalisatie van termen bij instroom:
		 *  HTTP GET: /api/find-concepts?q=Term
		*  Solr parameters: q
		*  OpenSKOS paramters: none
		*/
		print "\nTest /api/find-concepts?q=Term";
	 
		$this->getClient->setParameterGet('q', QUERY_term);
		$response = $this->getClient-> request(Zend_Http_Client::GET);
		
		$this->AssertEquals(200, $response->getStatus());
		$this->assertionsForXMLSearchResults($response);
		$this->assertionsForXMLRdfDescription($response);
	}

	public function test02FindConcepts() {
		/*  HTTP GET: /api/find-concepts in jsonP format en q=’term’
		 *  	Solr Parameters: q
		*   OpenSKOS parameters: format, callback
		*   test jsonp output
		*/
		print "\nTest /api/find-concepts in jsonP format en q=’term’";
		 
		$this->getClient->setParameterGet(array(
				'q'  => QUERY_term,
				'format'=>'jsonp',
				'callback'=>'myCallback'
		));
		
		$response = $this->getClient-> request(Zend_Http_Client::GET);
		$this -> AssertEquals(200, $response->getStatus());
		$this->assertionsForJSONPResult($response);
	}
	 
	public function test03FindConcepts() {
		/* POMS integratie
		 *  HTTP GET: ./api/find-concepts?tenant=beng&collection=gtaa&q=status:(candidate OR approved OR not_compliant OR rejected OR deleted) AND inScheme:”http://data.beeldengeluid.nl/gtaa/Persoonsnamen AND (*[input]* OR “[input]”)&fl=uuid,uri,prefLabel,altLabel,hiddenLabel&rows=[rows]
		*  Solr parameters: q, rows
		*  OpenSKOS parameters: tenant, collection, fl
		*/
		print "\nTest POMS query: /api/find-concepts?\
				tenant=beng&\
				collection=gtaa&\
				q=status:(candidate OR approved OR not_compliant OR rejected OR deleted) AND inScheme:”http://data.beeldengeluid.nl/gtaa/Persoonsnamen AND (*[input]* OR “[input]”)&\
				fl=uuid,uri,prefLabel,altLabel,hiddenLabel&\
				rows=[rows]";
		$this->getClient->setUri(BASE_URI_ . '/api/find-concepts?q=' . QUERY_term);
		$response = $this->getClient -> request(Zend_Http_Client::GET);
		$this -> AssertEquals(200, $response->getStatus());
		 
		$this->assertionsForXMLSearchResults($response);
		$this->assertionsForXMLRdfDescription($response);
	}

	/* XML assertions
	 */
	private function assertionsForXMLSearchResults($response) {
		$dom = new Zend_Dom_Query();
		$dom->setDocumentXML($response->getBody());
		$xml = $response->getBody();
		 
/* 		//corretions should be removed after bug fixing
		$xmlCorrected = str_replace ('xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#type"', 'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"', $xml);
		//$xmlCorrected = str_replace ('dc:creator', 'dcterms:creator', $xmlCorrected);
		$dom->setDocumentXML($xmlCorrected);
		// */

		$results1 = $dom->query('rdf:RDF');
		$count =  intval($results1->current()-> getAttribute('openskos:numFound'));
		$xpathQuery = '/rdf:RDF/rdf:Description';
		$results = $dom->queryXpath($xpathQuery);
		$this->assertCount($count,$results,"Number of records doesn't match openskos:numFound.");
		 
	}

	/* RDF and SKOS assertions
	 * 
	 */
	private function assertionsForXMLRdfDescription($response) {
		//	rdf:about, skos:prefLabel, skos:inScheme,
		$dom = new Zend_Dom_Query();
		$dom->setDocumentXML($response->getBody());
		$xml = $response->getBody();

		foreach ($dom->query('rdf:Description') as $description) {
			$this->assertNotNull($description->getAttribute('rdf:about'));
			
			$xpathnode = $description->getNodePath();

			$xpathQuery = $xpathnode.'/rdf:type[@rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"]';
			$results = $dom->queryXpath($xpathQuery);
			$this->assertCount(1,$results);
			
			$xpathQuery = $xpathnode.'/skos:prefLabel[@xml:lang="nl"]';
			$results = $dom->queryXpath($xpathQuery);
			$this->assertCount(1,$results);
			
/* 			$xpathQuery = $xpathnode.'/skos:notation';
			$results = $dom->queryXpath($xpathQuery);
			$this->assertNotNull($results->current());
			$this->assertInternalType('integer', intval($results->current()->nodeValue)); */
			
			$xpathQuery = $xpathnode.'/skos:inScheme';
			$results = $dom->queryXpath($xpathQuery);
			$this->assertGreaterThanOrEqual(1, count($results));
						
		}
	}
	/* JSON assertions
	 *
	*/
	private function assertionsForJSONPResult($response) {
		//	response is JSONP response
		$jsonp = $response->getBody();
		$this->assertInternalType('string', $jsonp);
		//$this->assertRegExp('/\w+(.)/',$jsonp);
		
		$pattern = '/(?<callback>\w+)/';
		preg_match($pattern, $jsonp, $matches);
		$callback = $matches['callback'];
		
		$pattern2 = '/^'.$callback.'\((?<json>.+)\);$/';
		preg_match($pattern2, $jsonp, $morematches);
		$json = $morematches['json'];
		
		$pattern3 = '/^\w+\((?<json>.+)\);$/';
		preg_match($pattern3, $jsonp, $morematches);
		$json = $morematches['json'];
		$this->assertJson($json);
	}
}
?>

