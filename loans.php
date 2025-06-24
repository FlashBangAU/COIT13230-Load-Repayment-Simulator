<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <script src="js/bootstrap.min.js"></script>
    <title>List of Loans</title>
    <style type="text/css">
        table {border-style: outset; border-width: thin;}
        th, td {border-style: inset; border-width: thin;}
    </style>

</head>
<body>    
    <div class="container d-flex flex-column min-vh-100">
        <?php
        session_start();
        $validSession = require('check-session.php');
        $validLogin = require("check-login.php");

        if ($validLogin || $validSession) {
            echo '<h1 class="text-center my-4">List of Loans</h1>';
            require("db-connection.php");

            $search = $_SESSION['id-user'];
            $query = "SELECT * FROM starting_loan_values WHERE ID_user = ? ORDER BY start_date, duration_years ";

            $stmt = $db->prepare($query);
            if (!empty($search)) {
                $stmt->bind_param("i", $search);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            $numResults = $result->num_rows;

            echo <<<END
            <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Loan ID</th>
                    <th>Starting Date</th>
                    <th>Starting Interest</th>
                    <th>Start Principle</th>
                    <th>Duration (years)</th>
                    <th>Payment Intervals</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
    END;
            while ($row = $result->fetch_assoc()) {
                $id = $row['DB_set'];
                $startDate = $row['start_date'];
                $startInterest = $row['start_interest'];
                $startPrinciple = $row['start_principle'];
                $durationYears = $row['duration_years'];
                $paymentInterval = $row['payment_interval'];

                echo "<tr>";
                echo "<td valign=\"top\">$id</td>";
                echo "<td valign=\"top\">$startDate</td>";
                echo "<td valign=\"top\">$startInterest%</td>";
                echo "<td valign=\"top\">$$startPrinciple</td>";
                echo "<td valign=\"top\">$durationYears</td>";
                echo "<td valign=\"top\">$paymentInterval</td>";
                createButtonColumn1($id, "View Changing Elements", "loan-elements.php", "btn btn-primary");
                createButtonColumn1($id, "Edit", "edit-loan.php", "btn btn-warning");
                createButtonColumn1($id, "Delete", "delete-loan.php", "btn btn-danger");
                echo "</tr>";
            }

            $result->free();
            $db->close();

            echo '</tbody>';
            echo '</table>';
            require('footer-logged-in.php');
        } else {
            if (isset($_SESSION['valid_user'])) {
                echo "Could not log you in.<br>";
            }
            require('login.php');
        }

        //view changing elements in loan, edit, delete loan
        function createButtonColumn1($Db_set, $buttonText, $actionPage, $btnType) {
            echo "<td>";
            echo "<form action=\"$actionPage\" method=\"GET\">";
            echo "<input type=\"hidden\" name=\"DB_set\" value=\"$Db_set\">";
            echo "<button type=\"submit\" class=\"$btnType\">$buttonText</button>";
            echo "</form>";             
            echo "</td>";
        }
        ?>
    </div>
</body>
</html>
