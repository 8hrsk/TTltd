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
    $user_accounts = get_user_accounts($user_id, $conn);

     $transactions = array();
     $statement = $conn->query("
        SELECT *
        FROM `user_accounts`
        LEFT JOIN `transactions` ON `user_accounts`.`id` = `transactions`.`account_from`
            OR `user_accounts`.`id` = `transactions`.`account_to`
        WHERE `user_accounts`.`user_id` = $user_id
        GROUP BY `transactions`.`id`
    ");

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $row['month'] = month_regexp($row['trdate']);
        // Set action type
        if (
            in_array($row['account_from'], $user_accounts) &&
            in_array($row['account_to'], $user_accounts)
        ) {
            $row['action'] = 'self';
        }

        if (
            in_array($row['account_from'], $user_accounts) &&
            !in_array($row['account_to'], $user_accounts)
        ) {
            $row['action'] = 'send';
        }

        if (
            !in_array($row['account_from'], $user_accounts) &&
            in_array($row['account_to'], $user_accounts)
        ) {
            $row['action'] = 'receive';
        }

        $transactions[] = $row;
    }

    return $transactions;
}

/**
 * Return all user accounts of given user.
 * 
 * @param int $user_id The ID of the user whose accounts are to be retrieved.
 * @param object $conn The database connection.
 *
 * @return array An array of user accounts.
 */
function get_user_accounts(int $user_id, object $conn): array
{
    $accounts = array();
    $statement = $conn->query("
        SELECT *
        FROM `user_accounts`
        WHERE `user_accounts`.`user_id` = $user_id
    ");

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $accounts[] = $row['id'];
    }

    return $accounts;
}


/**
 * Extracts the month from a given date string, using a regular expression.
 *
 * @param string $date The date string from which to extract the month.
 *
 * @return string The month (as a two-digit string) extracted from the given date.
 *
 * @throws Exception If the date string is invalid or if an error occurs during regular expression matching.
 */
function month_regexp(string $date): string
{
    $regexp = preg_match('/\d{4}-(\d{2})-\d{2} \d{2}:\d{2}:\d{2}/', $date, $matches);

    if ($regexp !== 1) {
        $exception = new Exception("Invalid date format or an error occured. Date: $date");
        throw $exception;
    }

    return $matches[1];
}