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

    // task modification. Add total transactions per every month
    $transactions_per_month = array();
    foreach($monthly_transactions as $month => $transaction) {
        $transactions_per_month[$month] = count($monthly_transactions[$month]);
    }

    // print_r($transactions_per_month);

    // Calculate monthly balance
    $monthly_transactions_balance = array();
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

        // $monthly_transactions[$month]['amount'] = $monthly_balance;
        // $monthly_transactions['total'] = $transactions_per_month[$month];
        $monthly_transactions_balance[$month]['balance'] = $monthly_balance;
        $monthly_transactions_balance[$month]['amount'] = $transactions_per_month[$month];
    }
    
    header('Content-Type: application/json');
    echo json_encode($monthly_transactions_balance);
}