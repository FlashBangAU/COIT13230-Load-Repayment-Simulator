<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <script src="js/bootstrap.min.js"></script>
    <title>Edit Interest</title>
</head>
<body>
    <?php
        session_start();
        $validSession = require('check-session.php');
        $validLogin = require("check-login.php");

        if ($validLogin || $validSession) {
            echo "<h1>Edit Interest</h1>";

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
            $changedPMT = htmlspecialchars($row['update_PMT']);
            $stmtLoanDetails->close();

            if($changedPMT == 1){
                $updatePMTinput = '<input type="hidden" name="updatePMT" value="0">
                        <td><input type="checkbox" name="updatePMT" class="form-check-input" value="1" checked></td>';
            }else{
                $updatePMTinput = '<input type="hidden" name="updatePMT" value="0">
                        <td><input type="checkbox" name="updatePMT" class="form-check-input" value="1"></td>';
            }

            if (isset($_POST['submit'])) {
                $submit = $_POST['submit'];

                if ($submit == "Cancel") {
                    $db->close();
                    header("Location: loan-elements.php?DB_set=$DbID");
                    exit();
                }

                // Validate input
                if (empty($_POST['date']) || empty($_POST['interest'])){
                    echo "<p class='text-danger'>Error: All fields are required.</p>";
                } else {
                    $newDate = $_POST['date'];
                    $newInterest = $_POST['interest'];
                    $updatePMT = $_POST['updatePMT'];

                    // Check if values have changed
                    if ($newDate === $date && $newInterest === $interest && $updatePMT === $changedPMT) {
                        echo "No changes made. Please modify the fields before submitting.";
                    } else {
                        // Proceed to edit loan if changes were made
                        $query = "UPDATE interest_repayments SET date_interest = ?, new_val_interest = ?, update_PMT = ? WHERE ID_user = ? AND DB_set = ? AND interest_ID = ?";
                        $stmt = $db->prepare($query);

                        if ($stmt) {
                            $stmt->bind_param("sdiiii", $newDate, $newInterest, $updatePMT, $search, $DbID, $interestID);
                            $stmt->execute();
                            $affectedRows = $stmt->affected_rows;
                            $stmt->close();

                            // Check if the query executed successfully (even if no rows were affected)
                            if ($affectedRows >= 0) {
                                echo "Successfully Updated Interest<br>";
                            } else {
                                echo "Failed to Update Interest in Database.<br>";
                            }
                        } else {
                            echo "Error preparing statement: " . $db->error;
                        }

                        echo "<a href=\"loan-elements.php?DB_set=$DbID\" class=\"btn btn-primary\">Back to Loan Elements</a>";
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
                        <td>New Interest Date:</td>
                        <td><input type="date" name="date" class="form-control" value="$date" maxlength="20">
                    </tr>
                    <tr>
                        <td>New Interest Amount:</td>
                        <td><input type="number" name="interest" class="form-control" value="$interest" step="0.01" maxlength="3" min="0" max="100">
                    </tr>
                    <tr>
                        <td>Update Payment Amount: <br>(Check with bank, automatic if increase)</td>
                        $updatePMTinput
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
