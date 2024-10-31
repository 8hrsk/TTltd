<?php
include_once('db.php');
include_once('model.php');

$user_id = isset($_GET['user'])
    ? (int)$_GET['user']
    : null;

if ($user_id) {
    // Get transactions balances
    $transactions = get_user_transactions_balances($user_id, $conn);
    // TODO: implement
}
?>