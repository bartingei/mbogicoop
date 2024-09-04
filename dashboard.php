<?php
session_start();
include('db_connection.php');

$member_id = $_SESSION['member_id'];
$sql = "SELECT * FROM members WHERE member_id = $member_id";
$result = mysqli_query($conn, $sql);
$member = mysqli_fetch_assoc($result);

echo "<h1>Welcome, " . $member['name'] . "</h1>";
echo "<p>Your balance: $" . $member['balance'] . "</p>";

$sql = "SELECT * FROM transactions WHERE member_id = $member_id ORDER BY transaction_date DESC";
$result = mysqli_query($conn, $sql);

echo "<h2>Your Transactions:</h2>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<p>Amount: $" . $row['amount'] . " on " . $row['transaction_date'] . "</p>";
}
?>
