<html>
<head>
<style>
body {
	font-family:Arial, sans-serif;
	font-size:13px;
}

table {
	border-spacing: 0;
}

th {
	text-align:left;
	font-size:12px;
}

tfoot td {
	text-align:right;
}

.invoice-header {
	margin-bottom:20px;
}

.footer {
	font-size:12px;
}
</style>
</head>
<body>

<!-- START INVOICE HEADER -->
<htmlpageheader name="header">
	<img src="{{ site_path }}/images/invoice-header.png" width="200" class="invoice-header" />
</htmlpageheader>
<sethtmlpageheader name="header" value="on" show-this-page="1" />
<!-- END INVOICE HEADER -->

<table width="100%" style="margin-top:20px">
	<tr>
		<td colspan="3">
			<h1>Factuur</h1>
		</td>
	</tr>
	<tr>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td width="60%">
			{{ name }}<br/>
			{{ address }}<br/>
			{{ zipcode }} {{ city }}<br/>
			{{ country }}
		</td>
		<td width="20%">
			Klantnummer:<br/>
			Factuurnummer:<br/>
			Factuurdatum:<br/>
		</td>
		<td width="20%">
			{{ customer_nr }}<br/>
			{{ invoice_nr }}<br/>
			{{ invoice_date }}
		</td>
	</tr>
</table>

<table width="100%" cellspacing="0" cellpadding="3" style="margin-top:30px">
	<thead>
		<tr>
			<th width="10%">Aantal</td>
			<th width="60%">Omschrijving</td>
			<th width="15%" style="text-align:right">Prijs per stuk</td>
			<th width="15%" style="text-align:right">Prijs</td>
		</tr>
	</thead>
	
	<tbody>
		{% for details in order_details %}
			<tr>
				<td>{{ details.quantity }}</td>
				<td>{{ details.title }}</td>
				<td style="text-align:right">&euro; {{ details.price }}</td>
				<td style="text-align:right">&euro; {{ details.priceTotal }}</td>
			</tr>
		{% endfor %}
		
			<tr>
				<td colspan="4"></td>
			</tr>
		
			<tr>
				<td>1</td>
				<td>Verzendkosten</td>
				<td style="text-align:right">&euro; {{ shipping_costs }}</td>
				<td style="text-align:right">&euro; {{ shipping_costs }}</td>
			</tr>
					
		{% if discount_amount < 0 %}
			<tr>
				<td>1</td>
				<td>Kortingscode {{ discount_code }}</td>
				<td style="text-align:right">&euro; {{ discount_amount }}</td>
				<td style="text-align:right">&euro; {{ discount_amount }}</td>
			</tr>
		{% endif %}
		
	</tbody>
	
	<tfoot>
		<tr>
			<td colspan="4">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">Totaal exclusief BTW</td>
			<td>&euro; {{ total_ex }}</td>
		</tr>
		
		{% for taxRate, value in tax %}
			<tr>
				<td colspan="2"></td>
				<td>{{ taxRate }}% BTW</td>
				<td>&euro; {{ value }}</td>
			</tr>
		{% endfor %}
		
		<tr>
			<td colspan="4"></td>
		</tr>
		
		<tr>
			<td colspan="2"></td>
			<td style="font-size:18px;border-top:1px solid #000">Totaal</td>
			<td style="font-size:18px;border-top:1px solid #000">&euro; {{ total }}</td>
		</tr>
			
	</tfoot>
	
</table>

<!-- START INVOICE FOOTER -->
<htmlpagefooter name="footer">

	<table width="100%" cellpadding="1" class="footer">
		<tr valign="top">
			<td valign="top" width="25%">
				Printduurzaam.nl<br/>
				W.M. Dudokweg 27<br/>
				1703 DA Heerhugowaard
			</td>
			<td valign="top" width="10%" style="font-weight:bold">
				Telefoon:<br/>
				E-mail:<br/>
				Website:
			</td>
			<td valign="top" width="20%">
				072 - 567 50 52<br/>
				info@printduurzaam.nl<br/>
				www.printduurzaam.nl
			</td>
			<td valign="top" width="6%" style="font-weight:bold">
				KVK:<br/>
				BTW:
			</td>
			<td valign="top" width="18%">
				60680326<br/>
				NL854012965B01
			</td>
			<td valign="top" width="6%" style="font-weight:bold">
				IBAN:<br/>
				BIC:
			</td>
			<td valign="top" width="15%">
				NL77RABO0148966128<br/>
				RABONL2U
			</td>
		</tr>
		<tr>
			<td colspan="7" style="padding-top:10px;text-align:center">
				Op al onze transacties zijn onze algemene voorwaarden van toepassing. Deze zijn te vinden op: www.printduurzaam.nl
			</td>
		</tr>
	</table>		

</htmlpagefooter>
<sethtmlpagefooter name="footer" value="on" show-this-page="1" />
<!-- END INVOICE HEADER -->

</body>
</html>