<?php
include('db_connection.php');

$json = file_get_contents('php://input');
$mpesaResponse = json_decode($json, true);

$transactionId = $mpesaResponse['Body']['stkCallback']['CheckoutRequestID'];
$resultCode = $mpesaResponse['Body']['stkCallback']['ResultCode'];

if ($resultCode == 0) {
    $amount = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'];
    $phoneNumber = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'];

    $sql = "UPDATE mpesa_transactions SET status='Completed' WHERE transaction_id='$transactionId'";
    mysqli_query($conn, $sql);

    $sql = "UPDATE members SET balance = balance + $amount WHERE phone_number='$phoneNumber'";
    mysqli_query($conn, $sql);
} else {
    $sql = "UPDATE mpesa_transactions SET status='Failed' WHERE transaction_id='$transactionId'";
    mysqli_query($conn, $sql);
}
?>
