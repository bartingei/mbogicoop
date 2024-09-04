<?php
session_start();
include('db_connection.php');
include('get_mpesa_token.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = $_POST['amount'];
    $phoneNumber = $_POST['phone_number'];
    $memberId = $_SESSION['member_id'];

    $accessToken = getMpesaAccessToken();
    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $businessShortCode = 'YOUR_SHORTCODE';
    $lipaNaMpesaOnlinePasskey = 'YOUR_PASSKEY';
    $timestamp = date('YmdHis');
    $password = base64_encode($businessShortCode . $lipaNaMpesaOnlinePasskey . $timestamp);

    $curl_post_data = array(
        'BusinessShortCode' => $businessShortCode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phoneNumber,
        'PartyB' => $businessShortCode,
        'PhoneNumber' => $phoneNumber,
        'CallBackURL' => 'https://yourdomain.com/confirm_mpesa_payment.php',
        'AccountReference' => 'SACCO123',
        'TransactionDesc' => 'SACCO Contribution'
    );

    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));

    $curl_response = curl_exec($curl);

    $result = json_decode($curl_response, true);
    if ($result['ResponseCode'] == "0") {
        $transactionId = $result['CheckoutRequestID'];

        $sql = "INSERT INTO mpesa_transactions (member_id, transaction_id, amount, phone_number, status) VALUES ('$memberId', '$transactionId', '$amount', '$phoneNumber', 'Pending')";
        mysqli_query($conn, $sql);

        echo "Please complete the payment on your phone.";
    } else {
        echo "Error initiating payment: " . $result['errorMessage'];
    }
}
?>
