<?php


/**
 * Return all users with at least one transaction.
 *
 * @param object $conn The database connection.
 *
 * @return array The array where the key is the user id and the value is the user name.
 */
function get_users(object $conn): array
{
    $users = array();
    $statement = $conn->query("
        SELECT DISTINCT`users`.`id`, `users`.`name` FROM `users`
        LEFT JOIN `user_accounts` ON `users`.`id` = `user_accounts`.`user_id`
        RIGHT JOIN `transactions` ON `user_accounts`.`id` = `transactions`.`account_from`
            OR `user_accounts`.`id` = `transactions`.`account_to`
        GROUP BY `users`.`id`
    ");

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $users[$row['id']] = $row['name'];
    }

    return $users;
}


/**
 * Return transactions balances of given user.
 * 
 * @param int $user_id The ID of the user whose transactions are to be retrieved.
 * @param object $conn The database connection.
 *
 * @return array An array of transactions.
 */
function get_user_transactions_balances(int $user_id, object $conn): array
{
     $transactions = array();
     $statement = $conn->query("
        SELECT * FROM `user_accounts`
        LEFT JOIN `transactions` ON `user_accounts`.`id` = `transactions`.`account_from`
            OR `user_accounts`.`id` = `transactions`.`account_to`
        WHERE `user_accounts`.`user_id` = " . $user_id 
    );

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        // $transactions[$row["id"]] = $row["name"];
        $row['month'] = month_regexp($row['trdate']);
        print_r($row);
        echo '<br/>';
    }

    return $transactions;
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
    preg_match('/\d{4}-(\d{2})-\d{2} \d{2}:\d{2}:\d{2}/', $date, $matches);
    $month = $matches[1];
    return $month;
}