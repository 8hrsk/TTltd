<?php

/**
 * Return list of users.
 */
function get_users(object $conn): array
{
    $users = array();
    $statement = $conn->query("
        SELECT * FROM `users`
    ");

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $users[$row['id']] = $row['name'];
    }

    return $users;
}

/**
 * Return transactions balances of given user.
 */
function get_user_transactions_balances(int $user_id, object $conn): array
{     
    $user_transactions = get_user_transactions($user_id, $conn);

    $balances = array();
    foreach ($user_transactions as $transaction) {
        $balances[$transaction['month']] = $balances[$transaction['month']] ?? 0;
        if ($transaction['reciever'] == 'true') {
            $balances[$transaction['month']] += $transaction['amount'];
        } else {
            $balances[$transaction['month']] -= $transaction['amount'];
        }
    }

    return $balances;
}

function get_user_transactions(int $user_id, object $conn): array
{

    $transactions = array();
    $statement = $conn->query('
        SELECT * FROM `transactions`
        WHERE `account_from` = ' . $user_id . ' OR `account_to` = ' . $user_id
    );
    
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $row['month'] = month_regexp($row['trdate']);

        if ($row['account_to'] == $user_id) {
            $row['reciever'] = 'true';
        } else {
            $row['reciever'] = 'false';
        }

        array_push($transactions, $row);
    }

    return $transactions;
}

function month_regexp(string $date): string
{
    $regexp = preg_match('/\d{4}-(\d{2})-\d{2} \d{2}:\d{2}:\d{2}/', $date, $matches);
    $month = $matches[1];
    return $month;
}