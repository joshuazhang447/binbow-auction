<?php
require_once(__DIR__ . "/../app/bootstrap.php");

use App\Lib\Logger;
use App\Models\Payment;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

$environment = new SandboxEnvironment(CLIENT_ID, CLIENT_SECRET);
$client = new PayPalHttpClient($environment);

$data = file_get_contents('php://input');
//Logger::getLogger()->debug("Data: " . $data);

$headers = getallheaders();
$headers = array_change_key_case($headers, CASE_UPPER);
//Logger::getLogger()->debug("Headers: " . $headers);

$json_data = json_decode($data);
if ($json_data->event_type != "PAYMENT.CAPTURE.COMPLETED") {
	Logger::getLogger()->error("PayPal: Non payment capture");
	die();
}


//Verify webhook sig
$result = openssl_verify(
	data: implode(separator: '|', array: [
		$headers['PAYPAL-TRANSMISSION-ID'],
		$headers['PAYPAL-TRANSMISSION-TIME'],
		WEBHOOK_ID,
		crc32(string: $data),
	]),
	signature: base64_decode(string: $headers['PAYPAL-TRANSMISSION-SIG']),
	public_key: openssl_pkey_get_public(public_key: file_get_contents(filename: $headers['PAYPAL-CERT-URL'])),
	algorithm: 'sha256WithRSAEncryption'
);


if ($result == 0) {
	Logger::getLogger()->error("PayPal: failed to verify");
	die();
} elseif ($result != 1) {
	Logger::getLogger()->error("PayPal: failed to attempt to verify");
	die();
}


Logger::getLogger()->debug("PayPal: verification passed");
$orderId = $json_data->resource->supplementary_data->related_ids->order_id;

try {
	// Call API with your client and get a response for your call
	$response = $client->execute(new OrdersGetRequest($orderId));

	//Log all
	//Logger::getLogger()->debug("OrderGetRequest Response: " . json_encode($response, JSON_PRETTY_PRINT));

	//Item info
	$item_number = $response->result->purchase_units[0]->items[0]->sku;
	$item_name = $response->result->purchase_units[0]->items[0]->name;

	//Transaction info
	$payment_gross = $response->result->purchase_units[0]->payments->captures[0]->amount->value;
	$currency_code = $response->result->purchase_units[0]->payments->captures[0]->amount->currency_code;

	//Payer Info
	$payer_id = $response->result->payer->payer_id;
	$payer = $response->result->payer->email_address;

	//Shipping info
	$full_name = $response->result->purchase_units[0]->shipping->name->full_name;
	$addressStreet = $response->result->purchase_units[0]->shipping->address->address_line_1;
	$addressCity = $response->result->purchase_units[0]->shipping->address->admin_area_2;
	$addressProvince = $response->result->purchase_units[0]->shipping->address->admin_area_1;
	$addressPostal = $response->result->purchase_units[0]->shipping->address->postal_code;
	$addressCountry = $response->result->purchase_units[0]->shipping->address->country_code;

	//Transaction info
	$payment_status = $response->result->purchase_units[0]->payments->captures[0]->status;
	$txn_id = $response->result->purchase_units[0]->payments->captures[0]->id;
	$date_obj = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $response->result->create_time);
	$payment_date = $date_obj->format("Y-m-d H:i:s");

	$paymentObj = new Payment(
		$txn_id,
		$payment_gross,
		$payment_status,
		$item_number,
		$item_name,
		$payer_id,
		$payer,
		$full_name,
		$addressStreet,
		$addressCity,
		$addressProvince,
		$addressPostal,
		$addressCountry,
		$payment_date);

	//Insert payment into db
	$result = $paymentObj->create();

	if ($result) {
		Logger::getLogger()->debug("PayPal: Payment has been made & successfully inserted into the Database");
	} else {
		Logger::getLogger()->alert("PayPal: Error inserting into DB ", ["POST" => $_POST]);
	}

} catch (Exception $e) {
	Logger::getLogger()->error("PayPal: Transaction failed to execute ", ['exception' => $e]);
	die();
}