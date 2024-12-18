<?php
session_start();
$validSession = require('check-session.php');
$validLogin = require("check-login.php");

if ($validLogin || $validSession) {
    echo '<h1>Add Loan</h1>';

    require('db-connection.php');

    if (isset($_POST['submit'])) {
        $submit = $_POST['submit'];
        if ($submit == "Cancel") {
            $db->close();
            header('Location: home.php');
            exit();
        }

        // Validate input
        if (empty($_POST['date']) || empty($_POST['interest']) || empty($_POST['principle']) || empty($_POST['duration']) || empty($_POST['payment'])) {
            echo "Error: All fields are required.";
            $db->close();
            exit;
        }

        $date = $_POST['date'];
        $interest = $_POST['interest'];
        $principle = $_POST['principle'];
        $duration = $_POST['duration'];
        $payment = $_POST['payment'];

        $search = $_SESSION['id-user']; // Logged-in user's ID

        // Fetch the smallest available DB_set for this user
        $queryMaxDBSet = "
            SELECT MIN(DB_set) + 1 AS next_db_set
            FROM (
                SELECT DB_set 
                FROM starting_loan_values
                WHERE ID_user = ?
                UNION ALL
                SELECT 0
            ) AS temp
            WHERE (DB_set + 1) NOT IN (
                SELECT DB_set 
                FROM starting_loan_values 
                WHERE ID_user = ?
            )
            LIMIT 1;
        ";

        $stmtMaxDBSet = $db->prepare($queryMaxDBSet);

        if ($stmtMaxDBSet) {
            $stmtMaxDBSet->bind_param("ii", $search, $search);
            $stmtMaxDBSet->execute();
            $result = $stmtMaxDBSet->get_result();

            if ($row = $result->fetch_assoc()) {
                $DbID = $row['next_db_set']; // Get the smallest missing DB_set
            } else {
                echo "Error fetching next DB_set.";
                $stmtMaxDBSet->close();
                $db->close();
                exit;
            }

            $stmtMaxDBSet->close();
        } else {
            echo "Error preparing statement for DB_set: " . $db->error;
            $db->close();
            exit;
        }


        // Insert the new loan into the database
        $query = "INSERT INTO starting_loan_values (ID_user, DB_set, start_date, start_interest, start_principle, duration_years, payment_interval)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);

        if ($stmt) {
            $stmt->bind_param("iisddis", $search, $DbID, $date, $interest, $principle, $duration, $payment);
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
            echo "Successfully Added Loan<br>";
        } else {
            echo "Failed to Add Loan to Database<br>";
        }

        echo "<a href=\"loans.php\">Back to Loan List</a>";
        echo "<br><hr>";
        exit;
    } else {
        // Display the form
        echo <<<END
        <form action="" method="POST">
            <table>
                <tr>
                    <td>Start Date:</td>
                    <td><input type="date" name="date" value="" maxlength="20" required></td>
                </tr>
                <tr>
                    <td>Start Interest:</td>
                    <td><input type="number" name="interest" value="" step="0.01" maxlength="3" min="0" max="100" required></td>
                </tr>
                <tr>
                    <td>Start Principle: (Amount left on loan)</td>
                    <td><input type="number" name="principle" min="0" step="0.01" value="" required></td>
                </tr>
                <tr>
                    <td>Duration: (years)</td>
                    <td><input type="number" name="duration" value="" min="0" maxlength="3" required></td>
                </tr>
                <td>Payment Interval:</td>
                <td>
                    <select name="payment">
                    <option value="Weekly">Weekly</option>
                    <option value="Fortnightly">Fortnightly</option>
                    <option value="Monthly">Monthly</option>
                </td>
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
