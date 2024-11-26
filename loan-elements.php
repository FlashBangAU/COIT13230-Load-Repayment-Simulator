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
        $query = "SELECT * FROM interest_repayments WHERE ID_user = ? AND DB_set = ? ORDER BY date_interest";

        $stmt = $db->prepare($query);
        if (!empty($search)) {
            $stmt->bind_param("ii", $search, $DbID);
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
                <th>Interest Change Date</th>
                <th>Interest Change Amount</th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
END;
        while ($row = $result->fetch_assoc()) {
            $id = $row['DB_set'];
            $interestDate = $row['date_interest'];
            $interestAmount = $row['new_val_interest'];

            echo "<tr>";
            echo "<td valign=\"top\">$interestDate</td>";
            echo "<td valign=\"top\">$interestAmount</td>";
            createButtonColumn("DB_set", $id, "Edit", "edit-interest.php");
            createButtonColumn("DB_set", $id, "Delete", "delete-interest.php");
            echo "</tr>";
        }

        $result->free();
        $db->close();

        echo '</tbody>';
        echo '</table>';



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

        $query = "SELECT * FROM additional_payments WHERE ID_user = ? AND DB_set = ? ORDER BY date_additional_payment";

        $stmt = $db->prepare($query);
        if (!empty($search)) {
            $stmt->bind_param("ii", $search, $DbID);
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
                <th>Additional Payment Date</th>
                <th>Payment Amount</th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
END;
        while ($row = $result->fetch_assoc()) {
            $id = $row['DB_set'];
            $paymentDate = $row['date_additional_payment'];
            $paymentAmount = $row['amount_additional_payments'];

            echo "<tr>";
            echo "<td valign=\"top\">$paymentDate</td>";
            echo "<td valign=\"top\">$paymentAmount</td>";
            createButtonColumn("DB_set", $id, "Edit", "edit-payment.php");
            createButtonColumn("DB_set", $id, "Delete", "delete-payment.php");
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

    function createButtonColumn($hiddenName, $hiddenValue, $buttonText, $actionPage) {
        echo "<td>";
        echo "<form action=\"$actionPage\" method=\"GET\">";
        echo "<input type=\"hidden\" name=\"$hiddenName\" value=\"$hiddenValue\">";
        echo "<button type=\"submit\">$buttonText</button>";
        echo "</form>";            
        echo "</td>";
    }
    ?>
</body>
</html>
