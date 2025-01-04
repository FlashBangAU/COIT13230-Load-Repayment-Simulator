<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <script src="js/bootstrap.min.js"></script>
    <title>Delete Interest</title>
</head>
<body>
    <?php
        session_start();
        $validSession = require('check-session.php');
        $validLogin = require("check-login.php");

        if ($validLogin || $validSession) {
            echo "<h1>Delete Interest</h1>";

            require('db-connection.php');

            // Validate and get DB_set from GET parameter
            if (isset($_GET['DB_set']) && is_numeric($_GET['DB_set'])) {
                $DbID = (int)$_GET['DB_set'];
            } else {
                echo "Invalid loan ID.";
                $db->close();
                exit;
            }

            if (isset($_GET['interest_ID']) && is_numeric($_GET['interest_ID'])) {
                $interestID = (int)$_GET['interest_ID'];
            } else {
                echo "Invalid interest ID.";
                $db->close();
                exit;
            }

            $search = $_SESSION['id-user'];

            // Fetch existing loan details
            $queryLoanDetails = "SELECT * FROM interest_repayments WHERE ID_user = ? AND DB_set = ? AND interest_ID = ?";
            $stmtLoanDetails = $db->prepare($queryLoanDetails);
            $stmtLoanDetails->bind_param("iii", $search, $DbID, $interestID);
            $stmtLoanDetails->execute();
            $result = $stmtLoanDetails->get_result();

            if ($result->num_rows === 0) {
                echo "Interest not found.";
                $stmtLoanDetails->close();
                $db->close();
                exit;
            }

            $row = $result->fetch_assoc();
            $date = htmlspecialchars($row['date_interest']);
            $interest = htmlspecialchars($row['new_val_interest']);
            $updatePMT = htmlspecialchars($row['update_PMT']);
            $stmtLoanDetails->close();

            if($updatePMT == 1){
                $updatePMTinput = '<input type="hidden" name="updatePMT" value="0">
                        <td><input type="checkbox" name="updatePMT" class="form-check-input" value="1" checked disabled></td>';
            }else{
                $updatePMTinput = '<input type="hidden" name="updatePMT" value="0">
                        <td><input type="checkbox" name="updatePMT" class="form-check-input" value="1" disabled></td>';
            }

            if (isset($_POST['submit'])) {
                $submit = $_POST['submit'];

                if ($submit == "Cancel") {
                    $db->close();
                    header('Location: header("Location: loan-elements.php?DB_set=$DbID");');
                    exit();
                }

                // Proceed to delete loan
                $query = "DELETE FROM interest_repayments WHERE ID_user = ? AND DB_set = ? AND interest_ID = ?";
                $stmt = $db->prepare($query);
                $stmt->bind_param("iii", $search, $DbID, $interestID);
                $stmt->execute();

                $affectedRows = $stmt->affected_rows;
                $stmt->close();

                if ($affectedRows == 1) {
                    echo "Successfully Deleted Interest<br>";
                } else {
                    echo "Failed to Delete Interest in Database<br>";
                }

                echo "<a href=\"loan-elements.php?DB_set=$DbID\" class=\"btn btn-primary\">Back to Loan Elements</a>";
                echo "<br><hr>";
                exit;
            }

            // Display loan details for confirmation before deletion
            echo <<<END
            <form action="" method="POST">
                <table>
                    <tr>
                        <td>New Interest Date:</td>
                        <td>$date</td>
                    </tr>
                    <tr>
                        <td>New Interest Amount:</td>
                        <td>$interest%</td>
                    </tr>
                    <tr>
                        <td>Update Payment Amount:</td>
                        $updatePMTinput
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
