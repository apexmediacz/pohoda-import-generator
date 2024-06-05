<?php

namespace Apexmediacz;

class Invoice
{
	public function __construct()
	{
	}
	public function createInvoice($invoiceData, $counter)
	{
		$invoice_currency = "czk";
		$invoiceTotals = (new \Apexmediacz\PohodaExportGenerator)->calculateTotalInvoiceAmounts($invoiceData);

		$invoiceItems = [];
		foreach ($invoiceData->items as $item) {
			$invoiceItems[] = $this->createInvoiceItem($item);
		}

		//return mb_detect_encoding($invoiceData->description);

		return '<dat:dataPackItem version="2.0" id="' . str_pad($counter, 3, "0", STR_PAD_LEFT) . '">
		<inv:invoice version="2.0">
			<inv:invoiceHeader>
				<inv:invoiceType>' . ($invoiceData->invoice_type ?? "issuedInvoice") . '</inv:invoiceType>
				<inv:number>
					' . ($invoiceData->request_number === true ? '<typ:numberRequested>' . $invoiceData->accounting_series_no . '' . $invoiceData->invoice_id . '</typ:numberRequested>' : '<typ:ids>' . $invoiceData->accounting_series_no . '</typ:ids>') . '
				</inv:number>
				<inv:symVar>' . $invoiceData->payment->variable_symbol . '</inv:symVar>
				<inv:date>' . $invoiceData->issue_date . '</inv:date>
				<inv:dateTax>' . $invoiceData->tax_point_date . '</inv:dateTax>
				<inv:dateAccounting>' . $invoiceData->tax_point_date . '</inv:dateAccounting>
				<inv:dateDue>' . $invoiceData->payment->payment_due_date . '</inv:dateDue>
				<inv:classificationVAT>
					<typ:classificationVATType>inland</typ:classificationVATType>
				</inv:classificationVAT>
				<inv:text>' . $this->encodeStringIfNeeded($invoiceData->description) . '</inv:text>
				<inv:partnerIdentity>
					' . $this->createParty($invoiceData->customer) . '
				</inv:partnerIdentity>
				<inv:paymentType>
					<typ:paymentType>' . ($invoiceData->payment->payment_type ?? "") . '</typ:paymentType>
				</inv:paymentType>
				<inv:symConst>0138</inv:symConst>
				<inv:paymentAccount>
					<typ:accountNo>' . $invoiceData->payment->bank_account_number . '</typ:accountNo>
					<typ:bankCode>' . $invoiceData->payment->bank_code . '</typ:bankCode>
				</inv:paymentAccount>
			</inv:invoiceHeader>
			<inv:invoiceDetail>' . implode("", $invoiceItems) . '</inv:invoiceDetail>
			<inv:invoiceSummary>
				<inv:roundingDocument>math2one</inv:roundingDocument>
				<inv:roundingVAT>none</inv:roundingVAT>
			' . ($invoice_currency !== 'czk' ? '<inv:foreignCurrency>
						<typ:currency>
							<typ:ids>' . strtoupper($invoiceData->payment->currency_code) . '</typ:ids>
						</typ:currency>
						<typ:priceSum>10000</typ:priceSum>
					</inv:foreignCurrency>' : '<inv:homeCurrency>
						<typ:priceLow>' . $invoiceTotals->low->totalWithoutVat . '</typ:priceLow>
						<typ:priceLowVAT>' . $invoiceTotals->low->totalVat . '</typ:priceLowVAT>
						<typ:priceLowSum>' . $invoiceTotals->low->totalWithVat . '</typ:priceLowSum>
						<typ:priceHigh>' . $invoiceTotals->high->totalWithoutVat . '</typ:priceHigh>
						<typ:priceHighVAT>' . $invoiceTotals->high->totalVat . '</typ:priceHighVAT>
						<typ:priceHighSum>' . $invoiceTotals->high->totalWithoutVat . '</typ:priceHighSum>
						<typ:round>
							<typ:priceRound>0</typ:priceRound>
						</typ:round>
					</inv:homeCurrency>') . '
			</inv:invoiceSummary>
		</inv:invoice>
	</dat:dataPackItem>';
	}

	public function createParty($partyData)
	{
		return '<typ:address>
		<typ:name>' . $this->encodeStringIfNeeded($partyData->name) . '</typ:name>
		<typ:city>' . $this->encodeStringIfNeeded($partyData->address->city) . '</typ:city>
		<typ:street>' . $this->encodeStringIfNeeded($partyData->address->street) . ' ' . $partyData->address->house_number . '</typ:street>
		<typ:zip>' . $partyData->address->zip_code . '</typ:zip>
		<typ:country>
			<typ:ids>' . $partyData->address->country_code . '</typ:ids>
		</typ:country>
		<typ:ico>' . $partyData->identification_number . '</typ:ico>
		<typ:dic>' . $partyData->tax_number . '</typ:dic>
	</typ:address>';
	}

	public function createInvoiceItem($item)
	{
		$invoiceRate = (new \Apexmediacz\PohodaExportGenerator)->getInvoiceItemVatRate($item);
		return '<inv:invoiceItem>
		<inv:text>' . $this->encodeStringIfNeeded($item->name) . '</inv:text>
		<inv:quantity>' . $item->quantity . '</inv:quantity>
		<inv:unit>' . $item->unit_label . '</inv:unit>
		<inv:payVAT>false</inv:payVAT>
		<inv:rateVAT>' . $invoiceRate . '</inv:rateVAT>
		<inv:homeCurrency>
			<typ:unitPrice>' . $item->unit_price_base . '</typ:unitPrice>
		</inv:homeCurrency>
		<inv:note />
		<inv:code />
	</inv:invoiceItem>';
	}

	public function encodeStringIfNeeded($string)
	{
		$stringEnc = mb_detect_encoding($string);
		if ($stringEnc != "Windows-1250") {
			return iconv($stringEnc, "Windows-1250", $string);
		}
		return $string;
	}
}
