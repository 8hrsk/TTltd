<?php
include_once('db.php');
include_once('model.php');

$conn = get_connect();

$month_names = [
    '01' => 'January',
    '02' => 'Februarry',
    '03' => 'March'
];

$user_id = isset($_GET['user'])
    ? (int)$_GET['user']
    : null;

if ($user_id) {
    // Get transactions balances
    $transactions = get_user_transactions_balances($user_id, $conn);

    // Group transactions by month
    $monthly_transactions = array();
    foreach ($transactions as $key => $transaction) {
        $transaction['month'] = $month_names[$transaction['month']];
        $monthly_transactions[$transaction['month']][] = $transaction;
    }

    // Calculate monthly balance
    foreach ($monthly_transactions as $month => $transactions) {
        $monthly_balance = 0;
        foreach ($transactions as $key => $transaction) {
            // I decided to skip self transactions because the total amount of money on the user accounts stays the same
            if ($transaction['action'] === 'self') continue; // Skip self transactions

            if ($transaction['action'] === 'receive') {
                $monthly_balance += $transaction['amount'];
            }

            if ($transaction['action'] === 'send') {
                $monthly_balance -= $transaction['amount'];
            }
        }
        $monthly_transactions[$month] = $monthly_balance;
    }
    
    header('Content-Type: application/json');
    echo json_encode($monthly_transactions);
}