<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>List of Loans</title>
    <style>
        table {border-style: outset; border-width: thin;}
        th, td {border-style: inset; border-width: thin;}
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

        // Validate and get DB_set from GET parameter
        if (isset($_GET['DB_set']) && is_numeric($_GET['DB_set'])) {
            $DbID = (int)$_GET['DB_set'];
        } else {
            echo "Invalid loan ID.";
            $db->close();
            exit;
        }

        $search = $_SESSION['id-user'];
        $query = "SELECT * FROM starting_loan_values WHERE ID_user = ? AND DB_set = ?";

        $stmt = $db->prepare($query);
        if (!empty($search)) {
            $stmt->bind_param("ii", $search, $DbID);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        while ($row = $result->fetch_assoc()) {
            $startDate = $row['start_date'];
            $startInterest = $row['start_interest'];
            $startPrinciple = $row['start_principle'];
            $durationYears = $row['duration_years'];
            $paymentInterval = $row['payment_interval'];

            echo "<b>Loan Start Date:</b> $startDate    <b>Beginning Interest:</b> $startInterest%    <b>Principle:</b> $$startPrinciple <b>Duration:</b> $durationYears years <b>Interest Added Every:</b> $paymentInterval <br>";
        }


        require("db-connection.php");

        $query = "SELECT * FROM interest_repayments WHERE ID_user = ? AND DB_set = ? ORDER BY date_interest";

        $stmt = $db->prepare($query);
        if (!empty($search)) {
            $stmt->bind_param("ii", $search, $DbID);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $numResults = $result->num_rows;

        echo "<br>";

        $interest = '';
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            $interestDate = $row['date_interest'];
            $interestAmount = $row['new_val_interest'];

            if($i !=0 ){
                $interest .= ',';
            }


            $day = date('d', strtotime($interestDate));
            $month = date('m', strtotime($interestDate));
            $year = date('Y', strtotime($interestDate));

            $interest .= '[' . $year . ',' . $month . ',' . $day . ',' . $interestAmount . ']';

            $i++;
        }

        $result->free();
        $db->close();


        require("db-connection.php");

        $query = "SELECT * FROM additional_payments WHERE ID_user = ? AND DB_set = ? ORDER BY date_additional_payment";

        $stmt = $db->prepare($query);
        if (!empty($search)) {
            $stmt->bind_param("ii", $search, $DbID);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $numResults = $result->num_rows;

        echo "<br>";

        $payment = '';
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            $paymentDate = $row['date_additional_payment'];
            $paymentAmount = $row['amount_additional_payments'];

            if($i !=0 ){
                $payment .= ',';
            }

            $day = date('d', strtotime($paymentDate));
            $month = date('m', strtotime($paymentDate));
            $year = date('Y', strtotime($paymentDate));

            $payment .= '[' . $year . ',' . $month . ',' . $day . ',' . $paymentAmount . ']';

            $i++;
        }

        $result->free();
        $db->close();


        require('footer-logged-in.php');
    } else {
        if (isset($_SESSION['valid_user'])) {
            echo "Could not log you in.<br>";
        }
        require('login.php');
    }

    function createButtonColumn1($hiddenName, $hiddenValue, $buttonText, $actionPage) {
        echo "<td>";
        echo "<form action=\"$actionPage\" method=\"GET\">";
        echo "<input type=\"hidden\" name=\"$hiddenName\" value=\"$hiddenValue\">";
        echo "<button type=\"submit\">$buttonText</button>";
        echo "</form>";            
        echo "</td>";
    }
    function createButtonColumn2($hiddenName1, $hiddenValue1, $hiddenName2, $hiddenValue2, $buttonText, $actionPage) {
        echo "<td>";
        echo "<form action=\"$actionPage\" method=\"GET\">";
        echo "<input type=\"hidden\" name=\"$hiddenName1\" value=\"$hiddenValue1\">";
        echo "<input type=\"hidden\" name=\"$hiddenName2\" value=\"$hiddenValue2\">";
        echo "<button type=\"submit\">$buttonText</button>";
        echo "</form>";            
        echo "</td>";
    }
    ?>

    <script type="text/javascript">
        var startDate = <?php echo "'$startDate'"; ?>.split(/[-]/);
        var startInterest = <?php echo $startInterest; ?>;
        var startPrinciple = <?php echo $startPrinciple; ?>;
        var startDuration = <?php echo $durationYears; ?>;
        var startInterval = <?php echo "'$paymentInterval'"; ?>;

        var interest = [<?php echo $interest; ?>];
        var payment = [<?php echo $payment; ?>];

        console.log(startDate);
        console.log(interest);
        console.log(payment);
    </script>
</body>
</html>
