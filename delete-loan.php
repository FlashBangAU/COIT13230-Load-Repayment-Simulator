<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <script src="js/bootstrap.min.js"></script>
    <title>Delete Loan</title>
</head>
<body>
    <?php
        session_start();
        $validSession = require('check-session.php');
        $validLogin = require("check-login.php");

        if ($validLogin || $validSession) {
            echo "<h1>Delete Loan</h1>";

            require('db-connection.php');

            // Validate and get DB_set from GET parameter
            if (isset($_GET['DB_set']) && is_numeric($_GET['DB_set'])) {
                $DbID = (int)$_GET['DB_set'];
            } else {
                echo "Invalid loan ID.";
                $db->close();
                exit;
            }

            $search = $_SESSION['id-user'];

            // Fetch existing loan details
            $queryLoanDetails = "SELECT * FROM starting_loan_values WHERE ID_user = ? AND DB_set = ?";
            $stmtLoanDetails = $db->prepare($queryLoanDetails);
            $stmtLoanDetails->bind_param("ii", $search, $DbID);
            $stmtLoanDetails->execute();
            $result = $stmtLoanDetails->get_result();

            if ($result->num_rows === 0) {
                echo "Loan not found.";
                $stmtLoanDetails->close();
                $db->close();
                exit;
            }

            $row = $result->fetch_assoc();
            $date = htmlspecialchars($row['start_date']);
            $interest = htmlspecialchars($row['start_interest']);
            $principle = htmlspecialchars($row['start_principle']);
            $duration = htmlspecialchars($row['duration_years']);
            $payment = htmlspecialchars($row['payment_interval']);
            $stmtLoanDetails->close();

            if (isset($_POST['submit'])) {
                $submit = $_POST['submit'];

                if ($submit == "Cancel") {
                    $db->close();
                    header('Location: loans.php');
                    exit();
                }

                // Proceed to delete loan
                $query = "DELETE FROM starting_loan_values WHERE ID_user = ? AND DB_set = ?";
                $stmt = $db->prepare($query);
                $stmt->bind_param("ii", $search, $DbID);
                $stmt->execute();

                $affectedRows = $stmt->affected_rows;
                $stmt->close();

                if ($affectedRows == 1) {
                    echo "Successfully Deleted Loan<br>";
                } else {
                    echo "Failed to Delete Loan in Database<br>";
                }

                echo "<a href=\"loans.php\" class=\"btn btn-primary\">Back to Loan List</a>";
                echo "<br><hr>";
                exit;
            }

            // Display loan details for confirmation before deletion
            echo <<<END
            Delete Loan with ID: <strong>$DbID</strong><br><br>
            <form action="" method="POST">
                <table>
                    <tr>
                        <td>Start Date:</td>
                        <td>$date</td>
                    </tr>
                    <tr>
                        <td>Start Interest:</td>
                        <td>$interest</td>
                    </tr>
                    <tr>
                        <td>Start Principle:</td>
                        <td>$principle</td>
                    </tr>
                    <tr>
                        <td>Duration: (years)</td>
                        <td>$duration</td>
                    </tr>
                    <tr>
                        <td>Payment Interval:</td>
                        <td>$payment</td>
                    </tr>
                </table>
                <br>
                <input type="hidden" name="DB_set" value="$DbID">
                <input type="submit" name="submit" class="btn btn-danger" value="Delete">
                <input type="submit" name="submit" class="btn btn-primary" value="Cancel">
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
