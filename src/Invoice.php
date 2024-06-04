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
				<inv:invoiceType>issuedInvoice</inv:invoiceType>
				<inv:number>
					<typ:ids>' . date('y', strtotime($invoiceData->tax_point_date)) . '19</typ:ids>
				</inv:number>
				<inv:symVar>' . $invoiceData->payment->variable_symbol . '</inv:symVar>
				<inv:date>' . $invoiceData->issue_date . '</inv:date>
				<inv:dateTax>' . $invoiceData->tax_point_date . '</inv:dateTax>
				<inv:dateAccounting>' . $invoiceData->tax_point_date . '</inv:dateAccounting>
				<inv:dateDue>' . $invoiceData->payment->payment_due_date . '</inv:dateDue>
				<inv:accounting>
					<typ:ids>BEZ331020</typ:ids>
				</inv:accounting>
				<inv:classificationVAT>
					<typ:classificationVATType>inland</typ:classificationVATType>
				</inv:classificationVAT>
				<inv:text>' . $this->encodeStringIfNeeded($invoiceData->description) . '</inv:text>
				<inv:partnerIdentity>
					' . $this->createParty($invoiceData->customer) . '
				</inv:partnerIdentity>
				<inv:paymentType>
					<typ:paymentType>draft</typ:paymentType>
				</inv:paymentType>
				<inv:symConst>0138</inv:symConst>
				<inv:paymentAccount>
					<typ:accountNo>' . $invoiceData->payment->bank_account_number . '</typ:accountNo>
					<typ:bankCode>' . $invoiceData->payment->bank_code . '</typ:bankCode>
				</inv:paymentAccount>
			</inv:invoiceHeader>
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
			<inv:invoiceDetail>
				' . implode("", $invoiceItems) . '
			</inv:invoiceDetail>
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
		<inv:text>' . $item->name . '</inv:text>
		<inv:quantity>' . $item->quantity . '</inv:quantity>
		<inv:unit>' . $item->unit_label . '</inv:unit>
		<inv:coefficient>1.0</inv:coefficient>
		<inv:payVAT>false</inv:payVAT>
		<inv:rateVAT>' . $invoiceRate . '</inv:rateVAT>
		<inv:homeCurrency>
			<typ:unitPrice>' . $item->unit_price_base . '</typ:unitPrice>
		</inv:homeCurrency>
		<inv:note />
		<inv:code />
		<inv:guarantee>48</inv:guarantee>
		<inv:guaranteeType>month</inv:guaranteeType>
	</inv:invoiceItem>';
	}

	public function encodeStringIfNeeded($string)
	{
		return $string;
		$stringEnc = mb_detect_encoding($string);
		if ($stringEnc != "Windows-1250") {
			return iconv($stringEnc, "Windows-1250", $string);
		}

		return $string;
	}
}
