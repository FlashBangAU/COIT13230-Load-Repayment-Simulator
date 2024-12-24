<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <script src="js/bootstrap.min.js"></script>
    <title>Edit Payment</title>
</head>
<body>
    <?php
        session_start();
        $validSession = require('check-session.php');
        $validLogin = require("check-login.php");

        if ($validLogin || $validSession) {
            echo "<h1>Edit Payment</h1>";

            require('db-connection.php');

            // Validate and get DB_set from GET parameter
            if (isset($_GET['DB_set']) && is_numeric($_GET['DB_set'])) {
                $DbID = (int)$_GET['DB_set'];
            } else {
                echo "Invalid loan ID.";
                $db->close();
                exit;
            }

            if (isset($_GET['payment_ID']) && is_numeric($_GET['payment_ID'])) {
                $paymentID = (int)$_GET['payment_ID'];
            } else {
                echo "Invalid payment ID.";
                $db->close();
                exit;
            }

            $search = $_SESSION['id-user'];

            // Fetch existing loan details
            $queryLoanDetails = "SELECT * FROM additional_payments WHERE ID_user = ? AND DB_set = ? AND payment_ID = ?";
            $stmtLoanDetails = $db->prepare($queryLoanDetails);
            $stmtLoanDetails->bind_param("iii", $search, $DbID, $paymentID);
            $stmtLoanDetails->execute();
            $result = $stmtLoanDetails->get_result();

            if ($result->num_rows === 0) {
                echo "payment not found.";
                $stmtLoanDetails->close();
                $db->close();
                exit;
            }

            $row = $result->fetch_assoc();
            $date = htmlspecialchars($row['date_additional_payment']);
            $payment = htmlspecialchars($row['amount_additional_payments']);
            $stmtLoanDetails->close();

            if (isset($_POST['submit'])) {
                $submit = $_POST['submit'];

                if ($submit == "Cancel") {
                    $db->close();
                    header('Location: loans.php');
                    exit();
                }

                // Validate input
                if (empty($_POST['date']) || empty($_POST['payment'])){
                    echo "Error: All fields are required.";
                } else {
                    $newDate = $_POST['date'];
                    $newPayment = $_POST['payment'];

                    // Check if values have changed
                    if ($newDate === $date && $newPayment === $payment) {
                        echo "No changes made. Please modify the fields before submitting.";
                    } else {
                        // Proceed to edit loan if changes were made
                        $query = "UPDATE additional_payments SET date_additional_payment = ?, amount_additional_payments = ? WHERE ID_user = ? AND DB_set = ? AND payment_ID = ?";
                        $stmt = $db->prepare($query);

                        if ($stmt) {
                            $stmt->bind_param("sdiii", $newDate, $newPayment, $search, $DbID, $paymentID);
                            $stmt->execute();
                            $affectedRows = $stmt->affected_rows;
                            $stmt->close();

                            // Check if the query executed successfully (even if no rows were affected)
                            if ($affectedRows >= 0) {
                                echo "Successfully Updated Payment<br>";
                            } else {
                                echo "Failed to Update Payment in Database.<br>";
                            }
                        } else {
                            echo "Error preparing statement: " . $db->error;
                        }

                        echo "<a href=\"loan-elements.php?DB_set=$DbID\">Back to Loan Elements</a>";
                        echo "<br><hr>";
                        $db->close();
                        exit;
                    }
                }
            }

            // Display loan details for confirmation before deletion
            echo <<<END
            <form action="" method="POST">
                <table>
                    <tr>
                        <td>Payment Date:</td>
                        <td><input type="date" name="date" class="form-control" value="$date" maxlength="20" required>
                    </tr>
                    <tr>
                        <td>Payment Amount:</td>
                        <td><input type="number" name="payment" class="form-control" value="$payment" step="0.01" maxlength="20" required>
                    </tr>
                </table>
                <br>
                <input type="hidden" name="DB_set" value="$DbID">
                <input type="submit" name="submit" class="btn btn-primary" value="Update">
                <input type="submit" name="submit" class="btn btn-danger" value="Cancel">
            </form>
END;

            $result->free();
            $db->close();
            require('footer-logged-in.php');
        } else {
            if (isset($_SESSION['valid-user'])) {
                echo "Could not log you in.<br>";
            }
            require('login.php');
        }
    ?>
</body>
</html>
