<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <script src="js/bootstrap.min.js"></script>
    <title>Add Payment</title>
</head>
<body>
<?php
session_start();
$validSession = require('check-session.php');
$validLogin = require("check-login.php");

if ($validLogin || $validSession) {
    echo '<h1>Add Payment</h1>';

    require('db-connection.php');

    // Validate and get DB_set from GET parameter
    if (isset($_GET['DB_set']) && is_numeric($_GET['DB_set'])) {
        $DbID = (int)$_GET['DB_set'];
    } else {
        echo "Invalid loan ID.";
        $db->close();
        exit();
    }

    if (isset($_POST['submit'])) {
        $submit = $_POST['submit'];

        if ($submit == "Cancel") {
            $db->close();
            header("Location: loan-elements.php?DB_set=$DbID");
            exit();
        }

        // Validate input
        if (empty($_POST['date']) || empty($_POST['payment'])) {
            echo "<p class='text-danger'>All Fields are Required. Redirecting to add payment...</p>";
            header("Refresh: 2.5; url=add-payment.php?DB_set=$DbID");
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
            exit();
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
            exit();
        }

        $db->close();

        if ($affectedRows == 1) {
            echo "<p class='text-success'>Successfully Added Payment</p>";
        } else {
            echo "<p class='text-danger'>Failed to Add Payment to Database</p>";
        }

        echo "<a href=\"loan-elements.php?DB_set=$DbID\" class=\"btn btn-primary\">Back to Loan Elements</a>";
        echo "<br><hr>";
        exit();
    } else {
        // Display the form
        echo <<<END
        <form action="" method="POST">
            <table>
                <tr>
                    <td>Payment Date:</td>
                    <td><input type="date" name="date" class="form-control" value="" maxlength="20"></td>
                </tr>
                <tr>
                    <td>Payment Amount:</td>
                    <td><input type="number" name="payment" class="form-control" value="" step="0.01" maxlength="20"></td>
                </tr>
            </table>
            <br>
            <input type="submit" name="submit" class="btn btn-primary" value="Add">
            <input type="submit" name="submit" class="btn btn-danger" value="Cancel">
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
</body>
</html>
