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

        $interestArray = [];
        while ($row = $result->fetch_assoc()) {
            $interestDate = $row['date_interest'];
            $interestAmount = $row['new_val_interest'];

            $day = date('d', strtotime($interestDate));
            $month = date('m', strtotime($interestDate));
            $year = date('Y', strtotime($interestDate));

            $interestArray[] = [(int)$year, (int)$month, (int)$day, (float)$interestAmount];
        }

        $interest = json_encode($interestArray);

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

        $paymentArray = [];
        while ($row = $result->fetch_assoc()) {
            $paymentDate = $row['date_additional_payment'];
            $paymentAmount = $row['amount_additional_payments'];

            $day = date('d', strtotime($paymentDate));
            $month = date('m', strtotime($paymentDate));
            $year = date('Y', strtotime($paymentDate));

            $paymentArray[] = [(int)$year, (int)$month, (int)$day, (float)$paymentAmount];
        }

        $payment = json_encode($paymentArray);


        $result->free();
        $db->close();

        echo <<<END
            <h3>Loan Simulation Results</h3>
             <table>
                    <tr>
                        <td>Start Date:</td>
                        <td>$startDate</td>
                        <td>        </td>
                        <td>Finish Date:</td>
                        <td><div id="currDate"></div></td>
                    </tr>
                    <tr>
                        <td>Start Principle:</td>
                        <td>$$startPrinciple</td>
                        <td>        </td>
                        <td>Total Paid:</td>
                        <td><div id="totalPaid"></div></td>
                    </tr>
                    <tr>
                        <td>Start Interest:</td>
                        <td>$startInterest%</td>
                        <td>        </td>
                        <td>Interest Paid:</td>
                        <td><div id="interestPaid"></div></td>
                    </tr>
                    <tr>
                        <td>Duration:</td>
                        <td>$durationYears years</td>
                        <td>        </td>
                        <td>Time till finish:</td>
                        <td><div id="timeTaken"></div></td>
                    </tr>
                    <tr>
                        <td>Payment Interval:</td>
                        <td>$paymentInterval</td>
                        <td>        </td>
                    </td>
                    </tr>
                </table>
        END;


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
        var date = <?php echo "'$startDate'"; ?>.split(/[-]/);
        var startInterest = <?php echo $startInterest; ?>;
        startInterest = startInterest / 100;
        var startPrinciple = <?php echo $startPrinciple; ?>;
        var startDuration = <?php echo $durationYears; ?>;
        var startIntervalStr = <?php echo "'$paymentInterval'"; ?>;

        var interest = <?php echo $interest; ?>;
        var payment = <?php echo $payment; ?>;

        //test if variables are active
        console.log("startDate: " + date);
        console.log("startInterest: " + startInterest * 100 + "%");
        console.log("startPrinciple: " + startPrinciple);
        console.log("startDuration: " + startDuration);
        console.log("startIntervalStr: " + startIntervalStr);

        console.log("interest: " + interest);
        console.log("payment: " + payment);

//set current date
        var currYear = date[0];
        var currMonth = Number(date[1]);
        var currDay = date[2];

        var currInterestPaymentsAnnual;

        var daysLeftInMonth = daysInMonth(currMonth, currYear) - currDay + 1;

//set how often repayments will be made and interest is charged
        var amountOfPayments;
        var interval = 0;
        switch(startIntervalStr) {
            case "Weekly":
                interval = 7;
                currInterestPaymentsAnnual = startInterest/52;
                amountOfPayments = startDuration * 52;
                break;
            case "Fortnightly":
                interval = 14;
                currInterestPaymentsAnnual = startInterest/26;
                amountOfPayments = startDuration * 26;
                break;
            case "Monthly":
                interval = daysLeftInMonth;
                currInterestPaymentsAnnual = startInterest/12;
                amountOfPayments = startDuration * 12;
                break;
        }
        console.log("interval: " + interval);

        var currPrinciple = startPrinciple;

//set daily interest rate (banks divide by 365 even on leap years)
        var currInterest = startInterest / 365; 

        var PMT = getPMT(currPrinciple, currInterestPaymentsAnnual, amountOfPayments);
        console.log("PMT: $" + PMT);
        console.log('');

        var totalInterestCharged = 0;

        //interest changed count
        var icc = 0;
        var interestReady = false;
        //if interest change is before loan start date this needs to be checked before simulation begins
        while(!interestReady && icc < interest.length){
            if(interest[icc][0] < currYear){
                icc++;
            }else if (interest[icc][1] < currMonth && interest[icc][0] == currYear){
                icc++;
            }else if (interest[icc][2] < currDay && interest[icc][1] == currMonth && interest[icc][0] == currYear){
                icc++;
            }else{
                interestReady = true;
            }
        }

        //payments made count
        var pmc = 0;
        var paymentReady = false;
        //if payment is before loan start date this needs to be checked before simulation begins
        while(!paymentReady && pmc < payment.lenght){
            if(payment[pmc][0] < currYear){
                pmc++;
            }else if (payment[pmc][1] < currMonth && payment[pmc][0] == currYear){
                pmc++;
            }else if (payment[pmc][2] < currDay && payment[pmc][1] == currMonth && payment[pmc][0] == currYear){
                pmc++;
            }else{
                paymentReady = true;
            }
        }



        var stuckInLoop = false;
        var interestForInterval = 0;
//loop to calculate loan
        while(currPrinciple > 0 && stuckInLoop == false)
        {
//change interest if possible
            if(icc < interest.length){
                if(interest[icc][0] == currYear && interest[icc][1] == currMonth && interest[icc][2] == currDay){
                    currInterest = interest[icc][3] / 100 / 365;
                    console.log("Changed interest to " + interest[icc][3] +  "%.");
                    icc++;
                }
            }

//make repayment if possible
            if(pmc < payment.length){
                if(payment[pmc][0] == currYear && payment[pmc][1] == currMonth && payment[pmc][2] == currDay){
                    currPrinciple -= payment[pmc][3];
                    console.log("Additional repayment made: $" + payment[pmc][3]);
                    pmc++; 
                }
            }


//calculate daily interest for interval interest
            interestForInterval = interestForInterval + (currPrinciple * currInterest);

//add repayment at interval and set new interval
            //reset amount left in interval
            if (interval == 0){
                amountOfPayments--;
                interval = setInterval(startIntervalStr, currMonth, currYear);

                totalInterestCharged = totalInterestCharged + (PMT - (PMT - interestForInterval));
                
                currPrinciple = currPrinciple - (PMT - interestForInterval);
                //console.log("interestForInterval: " + interestForInterval);
                interestForInterval = 0;
                //console.log("Pinciple: " + currPrinciple);
            }

            interval--;

//add day to loan and update month or year
            daysLeftInMonth--;
            currDay++;
            if(daysLeftInMonth == 0){
                currMonth = setNextMonth(currMonth);
                //console.log(currMonth + " " + currYear);
                currDay = 1;
                daysLeftInMonth = daysInMonth(currMonth, currYear);
            }


            if (amountOfPayments == -2)//prevents loop from getting stuck infinitly.
                stuckInLoop = true;
        }

        console.log("");
        if (!stuckInLoop){
            console.log("Loan finished normally and paid off principle");
        }else{
            console.log("Got stuck in loop or excided repayments by 2 payments!");
        }
        console.log("amountOfPayments Remaining: " + amountOfPayments);
        console.log("total amount spent on repayments: " + (totalInterestCharged + startPrinciple + currPrinciple));//currPrinciple to remove negative amount
        console.log("totalInterestCharged: " + totalInterestCharged);



        function newAnnualInterest(startIntervalStr, newInterest){
            switch(startIntervalStr) {
                case "Weekly":
                    return newInterest/52;
                case "Fortnightly":
                    return newInterest/26;
                case "Monthly":
                    return newInterest/12;
            }
        }

        function daysInMonth(month, year){
            switch(month){
                case 1: // January
                    return 31;
                case 2: // February
                    if (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0)) //leap year logic
                        return 29;
                    return 28;
                case 3: // March
                    return 31;
                case 4: // April
                    return 30;
                case 5: // May
                    return 31;
                case 6: // June
                    return 30;
                case 7: // July
                    return 31;
                case 8: // August
                    return 31;
                case 9: // September
                    return 30;
                case 10: // October
                    return 31;
                case 11: // November
                    return 30;
                case 12: // December
                    return 31;
                default:
                    console.error("Invalid month: " + month);
                    return 0; // Ensure no undefined value is returned
            }
        }

        function setNextMonth(month){
            month++;
            if(month == 13){
                month = 1;
                currYear++;
            }
            return month;
        }


        //principle, rate, period
        function getPMT(p, r, n) {
            return p * (r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
        }

        function setInterval (startIntervalStr, currMonth, currYear){
            switch(startIntervalStr) {
                case "Weekly":
                    return 7;
                case "Fortnightly":
                    return 14;
                case "Monthly":
                    return daysInMonth(currMonth, currYear);
            }
        }

        document.getElementById("currDate").innerHTML = currYear+"-"+currMonth+"-"+currDay;
        document.getElementById("totalPaid").innerHTML = "$"+(totalInterestCharged+startPrinciple).toFixed(2);
        document.getElementById("interestPaid").innerHTML = "$"+totalInterestCharged.toFixed(2);

    </script>
</body>
</html>
