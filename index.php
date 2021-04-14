<?php
	require_once 'settings.php';
	require 'vendor/autoload.php';

	use Hitmeister\Component\Api\ClientBuilder;
	use Hitmeister\Component\Api\Endpoints\OrderInvoices\Post;
	use Hitmeister\Component\Api\Tests\TransportAwareTestCase;
	use Hitmeister\Component\Api\Transfers\OrderInvoiceAddTransfer;
	
	$client = ClientBuilder::create()
		->setClientKey($api_client)
		->setClientSecret($api_secret)
		->build();
		
		
	$directory = '../PDF-OrderNumber-Converter/pdf/';
	
	//Cycle throught files
	$filepaths = array();
	$filenames = array();
	$ordernumbers = array();
	foreach (scandir($directory) as $file) {
		if ($file !== '.' && $file !== '..') {
			$filepaths[] = $directory . $file;
			$filenames[] = $file;
			$ordernumbers[] = str_replace('.pdf', '', $file);
		}
	}	
	
	for($i = 0; $i < sizeof($filepaths); $i++){
		try{
			//Convert to base64
			$base64_pdf = chunk_split(base64_encode(file_get_contents($filepaths[$i])));
			
			//Upload to real
			$result = $client->orderInvoices()->post($ordernumbers[$i], $ordernumbers[$i].".pdf", 'application/pdf', $base64_pdf);
		} catch (\Exception $e) {
			echo "[!] Unable to upload " . $filenames[$i] . " to order number " . $ordernumbers[$i] . "\r\n";
			continue;
		}
		
		try{
			//Delete file after upload
			unlink($filepaths[$i]);
			
			echo "Added invoice " . $filenames[$i] . " to order number " . $ordernumbers[$i] . "\r\n";
		} catch (\Exception $e) {
			echo "[!] Added invoice " . $filenames[$i] . " to order number " . $ordernumbers[$i] . ", but couldn't delete invoice pdf!\r\n";
		}
	}

?>