<?php

require_once("../vendor/autoload.php");

use Apexmediacz\PohodaExportGenerator;

$invoice_item = (object)[
	"name" => "Položka",
	"quantity" => 1,
	"unit_label" => "ks",
	"vat_rate_percent" => 21,
	"unit_price_base" => 200,
	"unit_price_vat_inclusive" => 242,
	"total_item_price_base" => 200,
	"total_item_price_vat_inclusive" => 242,
];

$invoice = (object)[
	"invoice_id" => "FV20240101",
	"issue_date" => "2024-04-13",
	"tax_point_date" => "2024-04-13",
	"is_vat_applicable" => true,
	"description" => "Faktura za služby",
	"customer" => (object)[
		"name" => "Customer company name",
		"identification_number" => "01234566",
		"tax_number" => "CZ01234566",
		"address" => (object)[
			"street" => "Ulice",
			"house_number" => "7",
			"city" => "Praha",
			"zip_code" => "110 00",
			"country_code" => "CZ"
		],
	],
	"supplier" => (object)[
		"name" => "Dodavatel s.r.o.",
		"identification_number" => "098767656",
		"tax_number" => "CZ098767656",
		"address" => (object)[
			"street" => "Ulice",
			"house_number" => "38",
			"city" => "Praha",
			"zip_code" => "150 00",
			"country_code" => "CZ"
		],
	],
	"payment" => (object)[
		"bank_name" => "ČSOB",
		"bank_account_number" => "111-122388",
		"bank_code" => "0300",
		"variable_symbol" => "8278728",
		"constant_symbol" => null,
		"specific_symbol" => null,
		"payment_due_date" => "2024-04-20",
	],
	"items" => [
		$invoice_item
	]
];

$data = (object)[
	"accounting_unit_identification_number" => "07968116",
	"invoices" => [
		$invoice
	]
];

$pohoda = new PohodaExportGenerator;
$xmlString = $pohoda->generateXml($data);
echo $xmlString;
