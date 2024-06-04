<?php

namespace Apexmediacz;

class PohodaExportGenerator
{
	private $vat_high_rate = 21;
	public function generateXml($data, $toScreen = false)
	{
		// $pohoda = new \Pohoda\Export($data->accounting_unit_identification_number);
		// foreach ($data->invoices as $invoice) {
		// 	$createdInvoice = $this->createInvoice($invoice);
		// 	if ($createdInvoice) {
		// 		if ($createdInvoice->isValid()) {
		// 			$pohoda->addInvoice($createdInvoice);
		// 		}
		// 	}
		// }

		// if ($toScreen) {
		// 	return $pohoda->exportAsXml(time(), 'popis', date("Y-m-d_H-i-s"));
		// }
		// return $pohoda->exportAsString(time(), 'popis', date("Y-m-d_H-i-s"));

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

	public function createInvoice($invoiceData)
	{
		try {
			$invoice = new \Pohoda\Invoice($invoiceData->invoice_id);
			$invoice->setText($invoiceData->description);
			$invoiceTotals = $this->calculateTotalInvoiceAmounts($invoiceData);
			$invoice->setBank($invoiceData->payment->bank_name);

			$invoice->setPriceLow($invoiceTotals->low->totalWithoutVat);
			$invoice->setPriceLowVAT($invoiceTotals->low->totalVat);
			$invoice->setPriceLowSum($invoiceTotals->low->totalWithVat);

			$invoice->setPriceHigh($invoiceTotals->high->totalWithoutVat);
			$invoice->setPriceHightVAT($invoiceTotals->high->totalVat);
			$invoice->setPriceHighSum($invoiceTotals->high->totalWithVat);

			$invoice->setWithVat(true);

			foreach ($invoiceData->items as $item) {
				$invoice->addItem($this->createInvoiceItem($item));
			}

			$invoice->setVariableNumber($invoiceData->payment->variable_symbol);
			$invoice->setDateCreated($invoiceData->issue_date);
			$invoice->setDateTax($invoiceData->tax_point_date);
			$invoice->setDateDue($invoiceData->payment->payment_due_date);

			$invoice->setProviderIdentity($this->createPartyItem($invoiceData->supplier));

			$customerAddress = $invoice->createCustomerAddress($this->createPartyItem($invoiceData->customer));
			$invoice->setCustomerAddress($customerAddress);

			return $invoice;
		} catch (\Pohoda\InvoiceException $e) {
			//print_r($e);
		} catch (\InvalidArgumentException $e) {
			//print_r($e);
		}
	}

	public function createInvoiceItem($item)
	{
		$invoiceItem = new \Pohoda\InvoiceItem();
		$invoiceItem->setText($item->name);
		$invoiceItem->setQuantity($item->quantity);
		$invoiceItem->setUnit($item->unit_label);
		$invoiceItem->setUnitPrice($item->total_item_price_base);
		$invoiceItem->setRateVAT($this->getInvoiceItemVatRate($item));
		$invoiceItem->setPayVAT(false); //zadaná cena je bez daně
		return $invoiceItem;
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
		return ($item->vat_rate_percent == 21 ? \Pohoda\InvoiceItem::VAT_HIGH : \Pohoda\InvoiceItem::VAT_LOW);
	}
}
