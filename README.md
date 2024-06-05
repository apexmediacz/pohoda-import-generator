php-pohoda-export
=============

Export invoices to XML format used in accounting software POHODA using simple object

## Quick Start

```php

require_once("../vendor/autoload.php");

use Apexmediacz\PohodaExportGenerator;

$invoice_item = (object)[
	"name" => "skv-01521/24 VLOŽKY ORTOPEDICKÉ INDIV. ZHOTOVOVANÉ (2 ks)",
	"quantity" => 1,
	"unit_label" => "ks",
	"vat_rate_percent" => 12,
	"unit_price_base" => 946.43,
	"unit_price_vat_inclusive" => 1060,
	"total_item_price_base" => 946.43,
	"total_item_price_vat_inclusive" => 1060,
];

$invoice = (object)[
	"accounting_series_no" => "24FV",
	"request_number" => true,
	"invoice_id" => "00041",
	"invoice_type" => "issuedInvoice", //issuedInvoice = vydaná faktura | commitment = ostatní závazky
	"order_id" => "skv-01521/24",
	"issue_date" => "2024-06-04",
	"tax_point_date" => "2024-06-04",
	"is_vat_applicable" => true,
	"description" => "popis faktury",
	"customer" => (object)[
		"name" => "Zákazník",
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
		"payment_type" => "hotově", //hotově | převodem
		"bank_name" => "ČSOB",
		"bank_account_number" => "12345678",
		"bank_code" => "0300",
		"variable_symbol" => "41240985",
		"constant_symbol" => null,
		"specific_symbol" => null,
		"payment_due_date" => "2024-06-04",
	],
	"items" => [
		$invoice_item
	]
];

$data = (object)[
	"accounting_unit_identification_number" => "ICO_UCETNI_JEDNOTKY",
	"invoices" => [
		$invoice,
	]
];

$pohoda = new PohodaExportGenerator;
$xmlString = $pohoda->generateXml($data, false);


```
