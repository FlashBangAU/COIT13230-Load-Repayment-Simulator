<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <script src="js/bootstrap.min.js"></script>
    <title>Add Interest</title>
</head>
<body>
    <div class="container d-flex flex-column min-vh-100">
        <?php
        session_start();
        $validSession = require('check-session.php');
        $validLogin = require("check-login.php");

        if ($validLogin || $validSession) {
            echo '<h1 class="my-4">Add Interest</h1>';

            require('db-connection.php');

            if (isset($_POST['submit'])) {
                $submit = $_POST['submit'];
                if ($submit === "Cancel") {
                    $db->close();
                    header("Location: loan-elements.php?DB_set=" . htmlspecialchars($_GET['DB_set']));
                    exit();
                }

                // Validate and get DB_set from GET parameter
                if (isset($_GET['DB_set']) && is_numeric($_GET['DB_set'])) {
                    $DbID = (int)$_GET['DB_set'];
                } else {
                    echo "<p class='text-danger'>Invalid loan ID.</p>";
                    $db->close();
                    exit();
                }

                // Validate input
                if (empty($_POST['date']) || empty($_POST['interest'])) {
                    echo "<p class='text-danger'>All Fields are Required. Redirecting to add interest...</p>";
                    header("Refresh: 2.5; url=add-interest.php?DB_set=$DbID");
                    $db->close();
                    exit();
                }

                $date = $_POST['date'];
                $interest = $_POST['interest'];
                $updatePMT = $_POST['updatePMT'];
                $search = $_SESSION['id-user']; // Logged-in user's ID

                // Find the next available interest_ID for the specified DB_set
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
                        echo "<p class='text-danger'>Error: Could not determine the next interest ID.</p>";
                        $db->close();
                        exit();
                    }
                } else {
                    echo "<p class='text-danger'>Error preparing statement: " . $db->error . "</p>";
                    $db->close();
                    exit();
                }

                // Insert the new interest into the database
                $query = "INSERT INTO interest_repayments (ID_user, DB_set, interest_ID, date_interest, new_val_interest, update_PMT)
                    VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);

                if ($stmt) {
                    $stmt->bind_param("iiisdi", $search, $DbID, $nextInterestID, $date, $interest, $updatePMT);
                    $stmt->execute();
                    $affectedRows = $stmt->affected_rows;
                    $stmt->close();
                } else {
                    echo "<p class='text-danger'>Error preparing statement: " . $db->error . "</p>";
                    $db->close();
                    exit();
                }

                $db->close();

                if ($affectedRows === 1) {
                    echo "<p class='text-success'>Successfully Added Interest</p>";
                } else {
                    echo "<p class='text-danger'>Failed to Add Interest to Database</p>";
                }

                echo "<a href=\"loan-elements.php?DB_set=" . htmlspecialchars($DbID) . "\" class=\"btn btn-primary\">Back to Loan Elements</a>";
                require('footer-logged-in.php');
                exit();
            } else {
                // Display the form
                echo <<<END
                <form action="" method="POST">
                    <table>
                        <tr>
                            <td>Start Date:</td>
                            <td><input type="date" name="date" class="form-control"></td>
                        </tr>
                        <tr>
                            <td>New Interest (%):</td>
                            <td><input type="number" name="interest" class="form-control" step="0.01" min="0" max="100"></td>
                        </tr>
                        <tr>
                            <td>Update Payment Amount: <br>(Check with bank, automatic if increase)</td>
                            <input type="hidden" name="updatePMT" value="0">
                            <td><input type="checkbox" name="updatePMT" class="form-check-input" value="1"></td>
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
                echo "<p class='text-danger'>Could not log you in.</p>";
            }
            require('login.php');
        }
        ?>
    </div>
</body>
</html>
