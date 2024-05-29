<?php

require_once("../vendor/autoload.php");

use Apexmediacz\PohodaImportGenerator;

$invoice_item = (object)[
	"name" => "Ortéza",
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
		"contact" => (object)[
			"email" => "info@apexmedia.cz"
		],
	],
	"supplier" => (object)[
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
		"contact" => (object)[
			"email" => "info@protetika-plzen.cz"
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

$pohoda = new PohodaImportGenerator;
$pohoda->generateXml($data);
