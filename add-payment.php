<?php
session_start();
$validSession = require('check-session.php');
$validLogin = require("check-login.php");

if ($validLogin || $validSession) {
    echo '<h1>Add Payment</h1>';

    require('db-connection.php');

   if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
    if ($submit == "Cancel") {
        $db->close();
        header('Location: home.php');
        exit();
    }

    // Validate and get DB_set from GET parameter
    if (isset($_GET['DB_set']) && is_numeric($_GET['DB_set'])) {
        $DbID = (int)$_GET['DB_set'];
    } else {
        echo "Invalid loan ID.";
        $db->close();
        exit();
    }

    // Validate input
    if (empty($_POST['date']) || empty($_POST['payment'])) {
        echo "Error: All fields are required.";
        $db->close();
        exit();
    }

    $date = $_POST['date'];
    $payment = $_POST['payment'];
    $search = $_SESSION['id-user']; // Logged-in user's ID

    // Find the next available payment_ID for the specified DB_set
    $queryNextPaymentId = "
        SELECT MIN(payment_ID) + 1 AS next_payment_ID
        FROM (
            SELECT payment_ID
            FROM additional_payments
            WHERE ID_user = ? AND DB_set = ?
            UNION ALL
            SELECT 0
        ) AS temp
        WHERE (payment_ID + 1) NOT IN (
            SELECT payment_ID
            FROM additional_payments
            WHERE ID_user = ? AND DB_set = ?
        )
        LIMIT 1;
    ";

    $stmtNextPaymentId = $db->prepare($queryNextPaymentId);
    if ($stmtNextPaymentId) {
        $stmtNextPaymentId->bind_param("iiii", $search, $DbID, $search, $DbID);
        $stmtNextPaymentId->execute();
        $stmtNextPaymentId->bind_result($nextPaymentID);
        $stmtNextPaymentId->fetch();
        $stmtNextPaymentId->close();

        if (is_null($nextPaymentID)) {
            echo "Error: Could not determine the next payment_ID.";
            $db->close();
            exit;
        }
    } else {
        echo "Error preparing statement: " . $db->error;
        $db->close();
        exit;
    }

    // Insert the new payment into the database
    $query = "INSERT INTO additional_payments (ID_user, DB_set, payment_ID, date_additional_payment, amount_additional_payments)
        VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);

    if ($stmt) {
        $stmt->bind_param("iiisd", $search, $DbID, $nextPaymentID, $date, $payment);
        $stmt->execute();

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $db->error;
        $db->close();
        exit;
    }

    $db->close();

    if ($affectedRows == 1) {
        echo "Successfully Added Payment<br>";
    } else {
        echo "Failed to Add Payment to Database<br>";
    }

    echo "<a href=\"loan-elements.php?DB_set=$DbID\">Back to Loan Elements</a>";
    echo "<br><hr>";
    exit();
}
 else {
        // Display the form
        echo <<<END
        <form action="" method="POST">
            <table>
                <tr>
                    <td>Payment Date:</td>
                    <td><input type="date" name="date" value="" maxlength="20" required></td>
                </tr>
                <tr>
                    <td>Payment Amount:</td>
                    <td><input type="number" name="payment" value="" step="0.01" min="0" maxlength="20" required></td>
                </tr>
            </table>
            <br>
            <input type="submit" name="submit" value="Add">
            <input type="submit" name="submit" value="Cancel">
        </form>
        END;

        include("footer-logged-in.php");
    }

} else {
    if (isset($_SESSION['valid-user'])) {
        echo "Could not log you in.<br>";
    }
    require('login.php');
}
?>
