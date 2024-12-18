<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Loan</title>
</head>
<body>
    <?php
        session_start();
        $validSession = require('check-session.php');
        $validLogin = require("check-login.php");

        if ($validLogin || $validSession) {
            echo '<h1>Edit Loan</h1>';

            require('db-connection.php');

            // Validate and get DB_set from GET parameter
            if (isset($_GET['DB_set']) && is_numeric($_GET['DB_set'])) {
                $DbID = (int)$_GET['DB_set'];
            } else {
                echo "Invalid loan ID.";
                $db->close();
                exit;
            }

            $userId = $_SESSION['id-user'];

            // Fetch existing loan details
            $queryLoanDetails = "SELECT * FROM starting_loan_values WHERE ID_user = ? AND DB_set = ?";
            $stmtLoanDetails = $db->prepare($queryLoanDetails);

            if ($stmtLoanDetails) {
                $stmtLoanDetails->bind_param("ii", $userId, $DbID);
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

                switch ($payment) {
                    case 'Weekly':
                        $payment2 = "Fortnightly";
                        $payment3 = "Monthly";
                        break;
                    case 'Fortnightly':
                        $payment2 = "Weekly";
                        $payment3 = "Monthly";
                        break;
                    case 'Monthly':
                        $payment2 = "Weekly";
                        $payment3 = "Fortnightly";
                        break;
                }

                $stmtLoanDetails->close();
            } else {
                echo "Error preparing statement: " . $db->error;
                $db->close();
                exit;
            }

            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
                $submit = $_POST['submit'];
                if ($submit === "Cancel") {
                    $db->close();
                    header('Location: loans.php');
                    exit();
                }

                // Validate input
                if (empty($_POST['date']) || empty($_POST['interest']) || empty($_POST['principle']) || empty($_POST['duration']) || empty($_POST['payment'])) {
                    echo "Error: All fields are required.";
                } else {
                    $date = $_POST['date'];
                    $interest = $_POST['interest'];
                    $principle = $_POST['principle'];
                    $duration = $_POST['duration'];
                    $payment = $_POST['payment'];

                    $query = "UPDATE starting_loan_values 
                              SET start_date = ?, start_interest = ?, start_principle = ?, duration_years = ?, payment_interval = ? 
                              WHERE ID_user = ? AND DB_set = ?";
                    $stmt = $db->prepare($query);

                    if ($stmt) {
                        $stmt->bind_param("sddisii", $date, $interest, $principle, $duration, $payment, $userId, $DbID);
                        $stmt->execute();
                        $affectedRows = $stmt->affected_rows;
                        $stmt->close();

                        if ($affectedRows === 1) {
                            echo "Successfully Updated Loan<br>";
                        } else {
                            echo "Failed to Update Loan in Database. No changes detected.<br>";
                        }
                    } else {
                        echo "Error preparing statement: " . $db->error;
                    }

                    echo "<a href=\"loans.php\">Back to Loan List</a>";
                    echo "<br><hr>";
                    $db->close();
                    exit;
                }
            }

            // Display the form
            echo <<<END
            <form action="" method="POST">
                <table>
                    <tr>
                        <td>Start Date:</td>
                        <td><input type="date" name="date" value="$date" maxlength="20" required></td>
                    </tr>
                    <tr>
                        <td>Start Interest:</td>
                        <td><input type="number" name="interest" value="$interest" step="0.01" maxlength="3" min="0" max="100" required></td>
                    </tr>
                    <tr>
                        <td>Start Principle: (Amount left on loan)</td>
                        <td><input type="number" name="principle" value="$principle" step="0.01" min="0" required></td>
                    </tr>
                    <tr>
                        <td>Duration: (years)</td>
                        <td><input type="number" name="duration" value="$duration" min="0" maxlength="3" required></td>
                    </tr>
                    <tr>
                        <td>Payment Interval:</td>
                        <td>
                        <select name="payment">
                        <option value="$payment">$payment</option>
                        <option value="$payment2">$payment2</option>
                        <option value="$payment3">$payment3</option>
                    </td>
                    </tr>
                </table>
                <br>
                <input type="submit" name="submit" value="Update">
                <input type="submit" name="submit" value="Cancel">
            </form>
            END;

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
