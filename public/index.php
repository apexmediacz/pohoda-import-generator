<?php

require_once("../vendor/autoload.php");

use Apexmediacz\PohodaExportGenerator;

$invoice_item = (object)[
	"name" => "Vložka do boty",
	"quantity" => 1,
	"unit_label" => "ks",
	"vat_rate_percent" => 21,
	"unit_price_base" => 200,
	"unit_price_vat_inclusive" => 242,
	"total_item_price_base" => 200,
	"total_item_price_vat_inclusive" => 242,
];

$invoice = (object)[
	"invoice_id" => "TFV20240101",
	"issue_date" => "2024-05-30",
	"tax_point_date" => "2024-05-30",
	"is_vat_applicable" => true,
	"description" => "Faktura za služby provedené",
	"customer" => (object)[
		"name" => "Protetika Plzeň, s.r.o.",
		"identification_number" => "48363405",
		"tax_number" => "CZ48363405",
		"address" => (object)[
			"street" => "Bolevecká",
			"house_number" => "38",
			"city" => "Plzeň",
			"zip_code" => "301 00",
			"country_code" => "CZ"
		],
	],
	"supplier" => (object)[
		"name" => "APEX MEDIA, s.r.o.",
		"identification_number" => "07968116",
		"tax_number" => "CZ07968116",
		"address" => (object)[
			"street" => "Vltavínová",
			"house_number" => "7",
			"city" => "Plzeň",
			"zip_code" => "326 00",
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
		"payment_due_date" => "2024-06-20",
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
$xmlString = $pohoda->generateXml($data, true);
echo $xmlString;
