<script src="js/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/custom.css">
<script src="js/bootstrap.min.js"></script>

<?php
session_start();
$validSession = require('check-session.php');
$validLogin = require("check-login.php");

if ($validLogin || $validSession) {
    echo '<h1>Add Interest</h1>';

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
            exit;
        }

        // Validate input
        if (empty($_POST['date']) || empty($_POST['interest'])) {
            echo "Error: All fields are required.";
            $db->close();
            exit;
        }

        $date = $_POST['date'];
        $interest = $_POST['interest'];

        $search = $_SESSION['id-user']; // Logged-in user's ID

        // Find the next available payment_ID for the specified DB_set
        $queryNextInterestId = "
            SELECT MIN(interest_ID) + 1 AS next_interest_ID
            FROM (
                SELECT interest_ID
                FROM interest_repayments
                WHERE ID_user = ? AND DB_set = ?
                UNION ALL
                SELECT 0
            ) AS temp
            WHERE (interest_ID + 1) NOT IN (
                SELECT interest_ID
                FROM interest_repayments
                WHERE ID_user = ? AND DB_set = ?
            )
            LIMIT 1;
        ";

        $stmtNextInterestId = $db->prepare($queryNextInterestId);
        if ($stmtNextInterestId) {
            $stmtNextInterestId->bind_param("iiii", $search, $DbID, $search, $DbID);
            $stmtNextInterestId->execute();
            $stmtNextInterestId->bind_result($nextInterestID);
            $stmtNextInterestId->fetch();
            $stmtNextInterestId->close();

            if (is_null($nextInterestID)) {
                echo "Error: Could not determine the next interest_ID.";
                $db->close();
                exit;
            }
        } else {
            echo "Error preparing statement: " . $db->error;
            $db->close();
            exit;
        }

        // Insert the new loan into the database
        $query = "INSERT INTO interest_repayments (ID_user, DB_set, interest_ID, date_interest, new_val_interest)
            VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);

        if ($stmt) {
            $stmt->bind_param("iiisd", $search, $DbID, $nextInterestID, $date, $interest);
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
            echo "Successfully Added Interest<br>";
        } else {
            echo "Failed to Add Interest to Database<br>";
        }

        echo "<a href=\"loan-elements.php?DB_set=$DbID\">Back to Loan Elements</a>";
        echo "<br><hr>";
        exit;
    } else {
        // Display the form
        echo <<<END
        <form action="" method="POST">
            <table>
                <tr>
                    <td>Start Date:</td>
                    <td><input type="date" name="date" class="form-control" value="" maxlength="20" required></td>
                </tr>
                <tr>
                    <td>New Interest:</td>
                    <td><input type="number" name="interest" class="form-control" value="" step="0.01" maxlength="3" min="0" max="100" required></td>
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
