<?php

namespace Apexmediacz;

class PohodaExportGenerator
{
	const VAT_NONE = "none";
	const VAT_HIGH = "high";
	const VAT_LOW = "low";
	const VAT_THIRD = "third";

	private $vat_high_rate = 21;
	public function generateXml($data, $toScreen = false)
	{
		$pohoda = new \Apexmediacz\Document($data->accounting_unit_identification_number);
		$invoices = new \Apexmediacz\Invoice;
		$invoicesData = [];
		$counter = 1;
		foreach ($data->invoices as $invoice) {
			$invoicesData[] = $invoices->createInvoice($invoice, $counter);
			$counter++;
		}
		$dataPackXml = $pohoda->generateDataPack($invoicesData);

		if ($toScreen) {
			//headers
			header('Content-type: text/xml; charset=windows-1250');
			header('Content-Disposition: attachment; filename="' . $data->accounting_unit_identification_number . '-' . date('d-m-Y-H-i-s') . '.xml"');
			return $dataPackXml;
		}
		return $dataPackXml;
	}

	/* HELPER FUNCTIONS */

	public function createPartyItem($partyData)
	{
		return [
			"company" => $partyData->name,
			"city" => $partyData->address->street,
			"number" => $partyData->address->house_number,
			"zip" => $partyData->address->zip_code,
			"ico" => $partyData->identification_number,
			"dic" => $partyData->tax_number,
			"country" => $partyData->address->country_code,
		];
	}

	public function calculateTotalInvoiceAmounts($invoice)
	{
		$totalHighWithoutVat = 0;
		$totalHighWithVat = 0;
		$totalHighVat = 0;

		$totalLowWithoutVat = 0;
		$totalLowWithVat = 0;
		$totalLowVat = 0;

		foreach ($invoice->items as $item) {
			if ($item->vat_rate_percent === $this->vat_high_rate) {
				$totalHighWithoutVat += $item->total_item_price_base;
				$totalHighWithVat += $item->total_item_price_vat_inclusive;
				$totalHighVat += ($totalHighWithVat - $totalHighWithoutVat);
			} else {
				$totalLowWithoutVat += $item->total_item_price_base;
				$totalLowWithVat += $item->total_item_price_vat_inclusive;
				$totalLowVat += ($totalLowWithVat - $totalLowWithoutVat);
			}
		}
		$totals = (object)[
			"low" => (object)[
				"totalWithoutVat" => $totalLowWithoutVat,
				"totalWithVat" => $totalLowWithVat,
				"totalVat" => $totalLowVat,
			],
			"high" => (object)[
				"totalWithoutVat" => $totalHighWithoutVat,
				"totalWithVat" => $totalHighWithVat,
				"totalVat" => $totalHighVat,
			],
		];
		return $totals;
	}

	public function getInvoiceItemVatRate($item)
	{
		return ($item->vat_rate_percent == 21 ? self::VAT_HIGH : self::VAT_LOW);
	}
}
