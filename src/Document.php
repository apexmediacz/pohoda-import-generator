<?php

namespace Apexmediacz;

class Document
{

	private $accountingIco;
	public function __construct($accountingIco)
	{
		$this->accountingIco = $accountingIco;
	}
	public function generateDataPack($invoices)
	{
		return '<?xml version="1.0" encoding="Windows-1250"?>
		<dat:dataPack version="2.0" id="00044" ico="' . $this->accountingIco . '" application="PAMICA" note="" xmlns:dat="http://www.stormware.cz/schema/version_2/data.xsd" xmlns:inv="http://www.stormware.cz/schema/version_2/invoice.xsd" xmlns:vch="http://www.stormware.cz/schema/version_2/voucher.xsd" xmlns:int="http://www.stormware.cz/schema/version_2/intDoc.xsd" xmlns:typ="http://www.stormware.cz/schema/version_2/type.xsd" xmlns:lst="http://www.stormware.cz/schema/version_2/list.xsd" xmlns:pam="http://www.stormware.cz/schema/version_2/pamica.xsd" xmlns:acu="http://www.stormware.cz/schema/version_2/accountingunit.xsd" xmlns:lCon="http://www.stormware.cz/schema/version_2/list_contract.xsd">
			' . implode("", $invoices) . '
		</dat:dataPack>';
	}
}
