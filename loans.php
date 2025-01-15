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
        /* Custom Styles for Loan Table */
        table {
            border-style: outset !important;
            border-width: thin !important;
            border-collapse: collapse !important; /* Ensures proper table border behavior */
            width: 100%;
        }

        table th, table td {
            border-style: inset !important;
            border-width: thin !important;
            padding: 5px !important;
            text-align: left;
        }

        /* Background color for table headers */
        table th {
            background-color: #f2f2f2 !important;
        }

        /* Body Styles */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Footer Styling */
        footer {
            margin-top: auto;
        }
    </style>

</head>
<body>    
    <?php
    session_start();
    $validSession = require('check-session.php');
    $validLogin = require("check-login.php");

    if ($validLogin || $validSession) {
        echo '<h1>List of Loans</h1>';
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
        <br>
        <table>
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
            createButtonColumn1("DB_set", $id, "View Changing Elements", "loan-elements.php");
            createButtonColumn2("DB_set", $id, "Edit", "edit-loan.php");
            createButtonColumn3("DB_set", $id, "Delete", "delete-loan.php");
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

    //view changing elements in loan
    function createButtonColumn1($hiddenName, $hiddenValue, $buttonText, $actionPage) {
        echo "<td>";
        echo "<form action=\"$actionPage\" method=\"GET\">";
        echo "<input type=\"hidden\" name=\"$hiddenName\" value=\"$hiddenValue\">";
        echo "<button type=\"submit\" class=\"btn btn-primary\">$buttonText</button>";
        echo "</form>";             
        echo "</td>";
    }
    //edit loan
    function createButtonColumn2($hiddenName, $hiddenValue, $buttonText, $actionPage) {
        echo "<td>";
        echo "<form action=\"$actionPage\" method=\"GET\">";
        echo "<input type=\"hidden\" name=\"$hiddenName\" value=\"$hiddenValue\">";
        echo "<button type=\"submit\" class=\"btn btn-warning\">$buttonText</button>";
        echo "</form>";            
        echo "</td>";
    }
    //delete loan
    function createButtonColumn3($hiddenName, $hiddenValue, $buttonText, $actionPage) {
        echo "<td>";
        echo "<form action=\"$actionPage\" method=\"GET\">";
        echo "<input type=\"hidden\" name=\"$hiddenName\" value=\"$hiddenValue\">";
        echo "<button type=\"submit\" class=\"btn btn-danger\">$buttonText</button>";
        echo "</form>";            
        echo "</td>";
    }
    ?>
</body>
</html>
