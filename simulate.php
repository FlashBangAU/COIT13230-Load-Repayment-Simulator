<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.js">
    </script>

    <script src="js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <script src="js/bootstrap.min.js"></script>

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


        $interestArray = [];
        while ($row = $result->fetch_assoc()) {
            $interestDate = $row['date_interest'];
            $interestAmount = $row['new_val_interest'];
            $interestUpdatePMT = $row['update_PMT'];

            $day = date('d', strtotime($interestDate));
            $month = date('m', strtotime($interestDate));
            $year = date('Y', strtotime($interestDate));

            $interestArray[] = [(int)$year, (int)$month, (int)$day, (float)$interestAmount, (int)$interestUpdatePMT];
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


        $paymentArray = [];
        while ($row = $result->fetch_assoc()) {
            $paymentDate = $row['date_additional_payment'];
            $paymentAmount = $row['amount_additional_payments'];
            $paymentUpdatePMT = $row['update_PMT'];

            $day = date('d', strtotime($paymentDate));
            $month = date('m', strtotime($paymentDate));
            $year = date('Y', strtotime($paymentDate));

            $paymentArray[] = [(int)$year, (int)$month, (int)$day, (float)$paymentAmount, (int)$paymentUpdatePMT];
        }

        $payment = json_encode($paymentArray);


        $result->free();
        $db->close();

        echo <<<END
        <body>
            <div class="container d-flex flex-column min-vh-100">
                <h1 class="text-center my-4">Loan Simulation Results</h1>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped-columns">
                        <tbody>
                            <tr>
                                <td><b>Start Date:</b></td>
                                <td><div id="startDate"></div></td>
                                <td><b>Finish Date:</b></td>
                                <td><div id="finDate"></div></td>
                            </tr>
                            <tr>
                                <td><b>Start Principle:</b></td>
                                <td><div id="startPrinciple"></div></td>
                                <td><b>Total Paid:</b></td>
                                <td><div id="totalPaid"></div></td>
                            </tr>
                            <tr>
                                <td><b>Start Interest:</b></td>
                                <td><div id="startInterest"></div></td>
                                <td><b>Interest Paid:</b></td>
                                <td><div id="interestPaid"></div></td>
                            </tr>
                            <tr>
                                <td><b>Duration:</b></td>
                                <td><div id="duration"></div></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><b>Payment Interval:</b></td>
                                <td><div id="paymentInterval"></div></td>
                                <td></td>
                                <td><a href="loan-elements.php?DB_set=$DbID" class="btn btn-warning" style="float: right">Back to Changing Variables</a></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><b>No Pay Finish Date:</b></td>
                                <td><div id="finDateNoPay"></div></td>
                                <td><b>No Pay Total Paid:</b></td>
                                <td><div id="totalPaidNoPay"></div></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td><b>No Pay Interest Paid:</b></td>
                                <td><div id="interestPaidNoPay"></div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <h4 class="text-center" style="color: red"><div id="endingWarning"></div></h4>
                <aside class="mt-3">
                    <div class="card chart-container">
                        <div class="card-body">
                            <canvas id="chart" class="w-100"></canvas>
                        </div>
                    </div>
                </aside>
                <div id="variableTable"></div>
                <div id="variableTableNoPay"></div>
        END;
                
                require('footer-logged-in.php');
            echo "</div>";
        echo "</body>";

    } else {
        if (isset($_SESSION['valid_user'])) {
            echo "Could not log you in.<br>";
        }
        require('login.php');
    }
    ?>

    <script type="text/javascript">
        var loanData;
        var interest;
        var intDate;
        var payment;
        var payDate;

        var numOcurringPayments = 0;
        var recurringPayments = [];
        var pmcRecurringPayments = [];
        var higherPMT = false;
        var tempPMT = 0;

        var date;
        var startInterest;
        var startPrinciple;
        var startDuration;
        var startIntervalStr;

        //current date
        var currYear;
        var currMonth;
        var currDay;

        var currInterestPaymentsAnnual;

        var daysLeftInMonth;

        //interval stuff
        var amountOfPayments;
        var interval = 0;


        var principleGraph =[];
        var totalGraph  =[0];
        var principleInterestGraph  =[];
        var principleGraphNoPay  =[];
        var totalGraphNoPay  =[0];
        var principleInterestGraphNoPay  =[];

        var totalPaidGraph = 0;
        var interestGraph = 0;
        var totalPaidGraphNoPay = 0;
        var interestGraphNoPay = 0;

        var countForYearPass = 0;

        var yearsCount = 1;

        var yearsGraph = [];

        var currPrinciple;
        var currPrincipleNoPay;


        var currInterest; 

        var PMTbeforeOccuringPayments;
        var PMT;
        var PMTnoPay;

        var totalInterestCharged = 0;
        var totalInterestChargedNoPay = 0;

        //interests made count
        var icc = 0;
        var interestReady = false;

        //payments made count
        var pmc = 0;
        var paymentReady = false;

        //set array info to display payment changes in loan
        var variableChangeArray = [];
        var variableChangeArrayNoPay = [];

        var variableChangeCount = 1;
        var variableChangeCountNoPay = 1;

        var stuckInLoop = false;
        var interestForInterval = 0;
        var interestForIntervalNoPay = 0;

        var simulationEndDate = null;
        var simulationEndDateNoPay = null;


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

        // Your main code that depends on the data
        function runMainSimulationLogic() {
            //loop to calculate loan
            while(currPrinciple > 0 || currPrincipleNoPay > 0 && stuckInLoop == false)
            {
    //change interest if possible 
                if(icc < interest.length){
                    intDate = interest[icc].date_interest.split("-").map(Number);
                    if(intDate[0] == currYear && intDate[1] == currMonth && intDate[2] == currDay){

                        var lastInterest = currInterest;
                        currInterest = interest[icc].new_val_interest / 100 / 365;
                        console.log("Changed interest to " + interest[icc].new_val_interest +  "%.");

                        variableChangeArray[variableChangeCount] = 
                            ["New Interest Rate",                           //Title
                            currDay + "/" + currMonth + "/" + currYear,     //current date
                            parseFloat(interest[icc].new_val_interest).toFixed(2) + "%",                         //new interest
                            "NO",                                           //Statement if PMT recalculated
                            "<td>$" + PMT.toFixed(2)                        //PMT   
                        ];   

                        variableChangeArrayNoPay[variableChangeCountNoPay] = 
                            ["New Interest Rate",                           //Title
                            currDay + "/" + currMonth + "/" + currYear,     //current date
                            parseFloat(interest[icc].new_val_interest).toFixed(2) + "%",                         //new interest
                            "NO",                                          //Statement if PMT recalculated
                            "<td>$" + PMTnoPay.toFixed(2)                   //PMTnoPay   
                        ];                                       

                        //calculates new PMT
                        currInterestPaymentsAnnual = newAnnualInterest(startIntervalStr, interest[icc].new_val_interest/100);
                        if(parseFloat(interest[icc].update_PMT) == 1 || lastInterest < currInterest){
                            var noPaymentUpdate = false;

                            PMTbeforeOccuringPayments = getPMT(currPrinciple, currInterestPaymentsAnnual, amountOfPayments);
                            if(PMTbeforeOccuringPayments > PMT){
                                PMT = PMTbeforeOccuringPayments;
                                noPaymentUpdate = true;
                            }
                            PMTnoPay = getPMT(currPrincipleNoPay, currInterestPaymentsAnnual, amountOfPayments);
                            console.log("PMT set to: " + PMT);

                            variableChangeArray[variableChangeCount] = 
                                ["New Interest Rate",                           //Title
                                currDay + "/" + currMonth + "/" + currYear,     //current date
                                parseFloat(interest[icc].new_val_interest).toFixed(2) + "%",                         //new interest
                                "YES",                                          //Statement if PMT recalculated
                                "<td style='background-color:#a1c5ff'>$" + PMT.toFixed(2)                            //new PMT   
                            ]; 

                            variableChangeArrayNoPay[variableChangeCountNoPay] = 
                                ["New Interest Rate",                           //Title
                                currDay + "/" + currMonth + "/" + currYear,     //current date
                                parseFloat(interest[icc].new_val_interest).toFixed(2) + "%",                         //new interest
                                "YES",                                          //Statement if PMT recalculated
                                "<td style='background-color:#a1c5ff'>$" + PMTnoPay.toFixed(2)                            //new PMTnoPay   
                            ]; 

                            if(noPaymentUpdate == false){
                                variableChangeArray[variableChangeCount] = 
                                ["New Interest Rate",                           //Title
                                currDay + "/" + currMonth + "/" + currYear,     //current date
                                parseFloat(interest[icc].new_val_interest).toFixed(2) + "%",                         //new interest
                                "NO",                                          //Statement if PMT recalculated
                                "<td style='background-color:#a1c5ff'>$" + PMT.toFixed(2)                            //new PMT   
                            ];
                            }
                        }   

                        icc++;
                        variableChangeCount++;
                        variableChangeCountNoPay++;
                    }
                }

    //make repayment if possible
                if(pmc < payment.length){
                    payDate = payment[pmc].date_additional_payment.split("-").map(Number);
                    if(payDate[0] == currYear && payDate[1] == currMonth && payDate[2] == currDay){
                        if(payment[pmc].payment_recurring_toggle == 0){
                            currPrinciple -= payment[pmc].amount_additional_payments;
                            console.log("Additional repayment made: $" + payment[pmc].amount_additional_payments);
                        }

                        variableChangeArray[variableChangeCount] = 
                            ["Additional Repayment",                        //Title
                            currDay + "/" + currMonth + "/" + currYear,     //current date
                            "$" + payment[pmc].amount_additional_payments.toFixed(2),                          //new interest
                            "NO",                                           //Statement if PMT recalculated
                            "<td>$" + PMT.toFixed(2)                         //PMT   
                        ];

                        //calculates new PMT
                        if(payment[pmc].update_PMT == 1){
                            PMT = getPMT(currPrinciple, currInterestPaymentsAnnual, amountOfPayments);
                            console.log("PMT set to: " + PMT);

                            variableChangeArray[variableChangeCount] = 
                                ["Additional Repayment",                        //Title
                                currDay + "/" + currMonth + "/" + currYear,     //current date
                                "$" + payment[pmc].amount_additional_payments.toFixed(2),                          //new interest
                                "YES",                                          //Statement if PMT recalculated
                                "<td style='background-color:#a1c5ff'>$" + PMT.toFixed(2)                            //PMT   
                            ];
                        }


                        //if recurring payment is selected
                        if(payment[pmc].payment_recurring_toggle == 1){
                            if(higherPMT == false){
                                PMTbeforeOccuringPayments = PMT;
                            }
                            recurringPayments[numOcurringPayments] = payment[pmc].amount_additional_payments;

                            pmcRecurringPayments[numOcurringPayments] = pmc;

                            numOcurringPayments++;

                            tempPMT = 0;

                            for(let i = 0; i < numOcurringPayments; i++){
                                tempPMT += recurringPayments[i];
                            }
                            if(tempPMT > PMT){
                                PMT = tempPMT
                                higherPMT = true;
                                variableChangeArray[variableChangeCount] = 
                                    ["Recurring Payment Begins",                        //Title
                                    currDay + "/" + currMonth + "/" + currYear,     //current date
                                    "$" + payment[pmc].amount_additional_payments.toFixed(2),                          //new interest
                                    "YES",                                          //Statement if PMT recalculated
                                    "<td style='background-color:#fcd85e'>$" + PMT.toFixed(2)                            //PMT   
                                ];
                            }
                            else{
                                console.log("recurringPayments value < PMT");
                                variableChangeArray[variableChangeCount] = 
                                    ["Recurring Payment Begins",                        //Title
                                    currDay + "/" + currMonth + "/" + currYear,     //current date
                                    "$" + payment[pmc].amount_additional_payments.toFixed(2),                          //new interest
                                    "NO",                                          //Statement if PMT recalculated
                                    "<td style='background-color:#fcd85e'>$" + PMT.toFixed(2)                            //PMT   
                                ];
                            }
                        }

                        totalPaidGraph += payment[pmc].amount_additional_payments;

                        pmc++;
                        variableChangeCount++; 
                        continue;
                    }
                }

                //recurring Payment is no longer active
                if(numOcurringPayments > 0){
                    for(let i = 0; i < numOcurringPayments; i++){
                        let element = pmcRecurringPayments[i];
                        payDate = payment[element].date_end_payments.split("-").map(Number);

                        if (payDate[0] == currYear && payDate[1] == currMonth && payDate[2] == currDay) {
                            console.log(`Ending recurring payment on ${currYear}-${currMonth}-${currDay}`);

                            var wasAboveMinimum = false;
                            if(PMT > PMTbeforeOccuringPayments){
                                wasAboveMinimum = true;
                            }

                            // Remove this recurring payment
                            recurringPayments.splice(i, 1);
                            pmcRecurringPayments.splice(i, 1);
                            numOcurringPayments--;

                            tempPMT =0;

                            if (numOcurringPayments > 0) {
                                tempPMT = 0;
                                for(let j = 0; j < numOcurringPayments; j++){
                                    tempPMT += recurringPayments[j];
                                }

                                if(tempPMT > PMTbeforeOccuringPayments){
                                    PMT = tempPMT;
                                    higherPMT = true;
                                }else{
                                    PMT = PMTbeforeOccuringPayments;
                                    higherPMT = false;
                                }
                            } else {
                                PMT = PMTbeforeOccuringPayments;
                                higherPMT = false;
                            }

                            if(payment[element].update_PMT == 1){
                                PMT = getPMT(currPrinciple, currInterestPaymentsAnnual, amountOfPayments);
                                
                                variableChangeArray[variableChangeCount] = 
                                    ["Recurring Payment Ends",                      //Title
                                    currDay + "/" + currMonth + "/" + currYear,     //current date
                                    "$" + payment[element].amount_additional_payments.toFixed(2),                       //new interest
                                    "YES",                                          //Statement if PMT recalculated
                                    "<td style='background-color:#ffab41'>$" + PMT.toFixed(2)                           //PMT   
                                ];
                            }else if(tempPMT < PMTbeforeOccuringPayments && wasAboveMinimum == true){
                                // PMT is reverting, but not recalculated
                                variableChangeArray[variableChangeCount] = 
                                    ["Recurring Payment Ends", currDay + "/" + currMonth + "/" + currYear,
                                    "$" + payment[element].amount_additional_payments.toFixed(2),
                                    "YES", "<td style='background-color:#ffab41'>$" + PMT.toFixed(2)];
                            }else{
                                variableChangeArray[variableChangeCount] = 
                                    ["Recurring Payment Ends",                        //Title
                                    currDay + "/" + currMonth + "/" + currYear,     //current date
                                    "$" + payment[element].amount_additional_payments.toFixed(2),                        //new interest
                                    "NO",                                          //Statement if PMT recalculated
                                    "<td style='background-color:#ffab41'>$" + PMT.toFixed(2)                            //PMT   
                                ];
                            }
                            variableChangeCount++;
                            i--;
                        }
                    }
                }



    //calculate daily interest for interval interest
                interestForInterval = interestForInterval + (currPrinciple * currInterest);
                interestGraph = interestGraph + (currPrinciple * currInterest);

                interestForIntervalNoPay = interestForIntervalNoPay + (currPrincipleNoPay * currInterest);
                interestGraphNoPay = interestGraphNoPay + (currPrincipleNoPay * currInterest);

    //add repayment at interval and set new interval
                //reset amount left in interval
                if (interval == 0){
                    amountOfPayments--;
                    interval = setInterval(startIntervalStr, currMonth, currYear);

                    if(currPrinciple > 0){
                        totalInterestCharged = totalInterestCharged + (PMT - (PMT - interestForInterval));
                        totalPaidGraph += PMT;

                        currPrinciple = currPrinciple - (PMT - interestForInterval);
                    }

                    if(currPrincipleNoPay > 0){
                        totalInterestChargedNoPay = totalInterestChargedNoPay + (PMTnoPay - (PMTnoPay - interestForIntervalNoPay));
                        totalPaidGraphNoPay += PMTnoPay;

                        currPrincipleNoPay = currPrincipleNoPay - (PMTnoPay - interestForIntervalNoPay);
                    }
                    
                    //console.log("interestForInterval: " + interestForInterval);
                    interestForInterval = 0;
                    interestForIntervalNoPay = 0;
                    //console.log("Pinciple: " + currPrinciple);
                }
                interval--;

    //save loan endDates
                if(currPrinciple < 0 && simulationEndDate == null){
                    simulationEndDate = currDay + "/" + currMonth + "/" + currYear;
                }
                if(currPrincipleNoPay < 0 && simulationEndDateNoPay == null){
                    simulationEndDateNoPay = currDay + "/" + currMonth + "/" + currYear;
                }


    //add day to loan and update month or year
                daysLeftInMonth--;
                currDay++;
                if(daysLeftInMonth == 0){
                    currMonth = setNextMonth(currMonth);
                    //console.log(currMonth + " " + currYear);
                    currDay = 1;
                    daysLeftInMonth = daysInMonth(currMonth, currYear);
                }

    //calculate graph information
                countForYearPass++;
                if(countForYearPass == 366 || countForYearPass == 365 && currYear % 4 != 0 && (currYear % 100 == 0 || currYear % 400 != 0)){
                    countForYearPass = 0;
                    if(currPrinciple >= 0){
                        principleGraph[yearsCount] = currPrinciple.toFixed(2);

                        totalGraph[yearsCount] = totalPaidGraph.toFixed(2);

                        principleInterestGraph[yearsCount] = (currPrinciple + interestGraph).toFixed(2);
                        interestGraph = 0;
                    }else{
                        principleGraph[yearsCount] = 0;

                        totalGraph[yearsCount] = (totalPaidGraph + currPrinciple).toFixed(2);

                        principleInterestGraph[yearsCount] = 0;
                        interestGraph = 0;
                    }

                    if(currPrincipleNoPay >= 0){
                        principleGraphNoPay[yearsCount] = currPrincipleNoPay.toFixed(2);

                        totalGraphNoPay[yearsCount] = totalPaidGraphNoPay.toFixed(2);

                        principleInterestGraphNoPay[yearsCount] = (currPrincipleNoPay + interestGraphNoPay).toFixed(2);
                        interestGraphNoPay = 0;
                    }else{
                        principleGraphNoPay[yearsCount] = 0;

                        totalGraphNoPay[yearsCount] = (totalPaidGraphNoPay + currPrincipleNoPay).toFixed(2);

                        principleInterestGraphNoPay[yearsCount] = 0;
                        interestGraph = 0;
                    }

                    yearsCount++;
                }

                if (amountOfPayments == -20)//prevents loop from getting stuck infinitly.
                    stuckInLoop = true;
            }

            console.log("");
            var warningText = "";
            if (!stuckInLoop){
                console.log("Loan finished normally and paid off principle");
            }else{
                console.log("Got stuck in loop or excided repayments by 2 payments!");
                warningText = "Exceeded repayments by 20 payments!";
            }
            console.log("amountOfPayments Remaining: " + amountOfPayments);
            console.log("total amount spent on repayments: " + (totalInterestCharged + startPrinciple + currPrinciple));//currPrinciple to remove negative amount
            console.log("totalInterestCharged: " + totalInterestCharged);

    //if loan finishes a little early for the year mark for the graph
            if(yearsCount == principleGraph.length){
                console.log("");
                console.log("Adding last value to graphs");
                principleGraph[yearsCount] = 0;
                totalGraph[yearsCount] = (totalPaidGraph + currPrinciple).toFixed(2);
                principleInterestGraph[yearsCount] = 0;

                principleGraphNoPay[yearsCount] = 0;
                totalGraphNoPay[yearsCount] = (totalPaidGraphNoPay + currPrincipleNoPay).toFixed(2);
                principleInterestGraphNoPay[yearsCount] = 0;

                console.log("Principle Graph: " + principleGraph);
                console.log("Total Paid Graph: " + totalGraph);
                console.log("Principle and Interest Graph: " + principleInterestGraph);
            }

            var variableChangeTable = "<br><h3>Normal Loan Changes</h3><table class='table table-bordered table-striped'><tbody><thead class='table-'><tr><th>Reason</th><th>Date</th><th>Amount</th><th>Payment Recalculated</th><th>Payment Per Interval</th></tr></thead>";
            console.log("Variable Change Array:" + variableChangeArray);
            var i = 0;
            while(i < variableChangeArray.length){
                variableChangeTable += "<tr><td>"+ variableChangeArray[i][0] +"</td>";
                variableChangeTable += "<td>"+ variableChangeArray[i][1] +"</td>";
                variableChangeTable += "<td>"+ variableChangeArray[i][2] +"</td>";
                variableChangeTable += "<td>"+ variableChangeArray[i][3] +"</td>";
                variableChangeTable += variableChangeArray[i][4] +"</td></tr>";
                i++;
            }
            variableChangeTable += "</tbody></table>"

            var variableChangeTableNoPay = "<br><h3>Loan Changes No Additional Payments</h3><table class='table table-bordered table-striped'><tbody><thead class='table-'><tr><th>Reason</th><th>Date</th><th>Amount</th><th>Payment Recalculated</th><th>Payment Per Interval</th></tr></thead>";
            console.log("Variable Change Array:" + variableChangeArrayNoPay);
            var i = 0;
            while(i < variableChangeArrayNoPay.length){
                variableChangeTableNoPay += "<tr><td>"+ variableChangeArrayNoPay[i][0] +"</td>";
                variableChangeTableNoPay += "<td>"+ variableChangeArrayNoPay[i][1] +"</td>";
                variableChangeTableNoPay += "<td>"+ variableChangeArrayNoPay[i][2] +"</td>";
                variableChangeTableNoPay += "<td>"+ variableChangeArrayNoPay[i][3] +"</td>";
                variableChangeTableNoPay += variableChangeArrayNoPay[i][4] +"</td></tr>";
                i++;
            }
            variableChangeTableNoPay += "</tbody></table>"

            document.getElementById("finDate").innerHTML = simulationEndDate;
            document.getElementById("totalPaid").innerHTML = "$"+(totalPaidGraph + currPrinciple).toFixed(2);
            document.getElementById("interestPaid").innerHTML = "$"+totalInterestCharged.toFixed(2);

            document.getElementById("finDateNoPay").innerHTML = simulationEndDateNoPay;
            document.getElementById("totalPaidNoPay").innerHTML = "$"+(totalPaidGraphNoPay + currPrincipleNoPay).toFixed(2);
            document.getElementById("interestPaidNoPay").innerHTML = "$"+totalInterestChargedNoPay.toFixed(2);

            document.getElementById("endingWarning").innerHTML = warningText;

            document.getElementById("variableTable").innerHTML = variableChangeTable;
            document.getElementById("variableTableNoPay").innerHTML = variableChangeTableNoPay;

            const ctx = document.getElementById("chart").getContext('2d');
            const myChart = new Chart(ctx, {
                type: 'line',
                data: {
                  labels: yearsGraph,
                  datasets: [{
                    label: 'Principle Remaining',
                    backgroundColor: 'rgba(13, 162, 255, 0.2)',
                    borderColor: 'rgb(0, 116, 189)',
                    data: principleGraph,
                  },{
                    label: 'Princple With Interest Remaining',
                    backgroundColor: 'rgba(74, 255, 77, 0.2)',
                    borderColor: 'rgb(0, 199, 3)',
                    data: principleInterestGraph
                },{
                    label: 'Total Paid',
                    backgroundColor: 'rgba(255, 253, 115, 0.2)',
                    borderColor: 'rgb(217, 213, 4)',
                    data: totalGraph
                }

        //DATA FOR nO ADDITIONAL PAYMENTS
                ,{
                    label: 'Principle Remaining No Payments',
                    backgroundColor: 'rgba(160, 117, 240, 0.2)',
                    borderColor: 'rgb(129, 66, 245)',
                    data: principleGraphNoPay,
                  },{
                    label: 'Princple With Interest Remaining No Payments',
                    backgroundColor: 'rgba(240, 134, 238, 0.2)',
                    borderColor: 'rgb(242, 85, 240)',
                    data: principleInterestGraphNoPay
                },{
                    label: 'Total Paid No Payments',
                    backgroundColor: 'rgba(130, 59, 161, 0.2)',
                    borderColor: 'rgb(130, 23, 176)',
                    data: totalGraphNoPay
                }]
                },
                options: {
                  scales: {
                    yAxes: [{
                      ticks: {
                        beginAtZero: true,
                      }
                    }]
                  }
                },
              });
        }



        // This async function fetches and assigns everything before continuing
        async function loadAndRun() {
            const userId = <?= json_encode($_SESSION['id-user'] ?? 0) ?>;
            const dbSet = <?= json_encode($_GET['DB_set'] ?? 0) ?>;

            try {
                // Get loan
                const loanRes = await fetch(`api/loans.php?DB_set=${dbSet}`);
                const loanArr = await loanRes.json();
                if (!loanArr || loanArr.length === 0) throw new Error("Loan not found");
                loanData = loanArr[0];

                date = loanData.start_date.split("-");
                startInterest = parseFloat(loanData.start_interest) / 100;
                startPrinciple = parseFloat(loanData.start_principle);
                startDuration = parseInt(loanData.duration_years);
                startIntervalStr = loanData.payment_interval;

                currYear = date[0];
                currMonth = Number(date[1]);
                currDay = date[2];

                daysLeftInMonth = daysInMonth(currMonth, currYear) - currDay + 1;

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

        //set variables for graph for after simulation
                principleGraph = [startPrinciple.toFixed(2)];
                principleInterestGraph = [startPrinciple.toFixed(2)];
                principleGraphNoPay = [startPrinciple.toFixed(2)];
                principleInterestGraphNoPay = [startPrinciple.toFixed(2)];

                for(var count = 0;  count <= startDuration; count++){
                    yearsGraph[count] = Number(currYear) + count;
                }

                currPrinciple = startPrinciple;
                currPrincipleNoPay = startPrinciple;

                //set daily interest rate (banks divide by 365 even on leap years)
                currInterest = startInterest / 365; 

                PMT = getPMT(currPrinciple, currInterestPaymentsAnnual, amountOfPayments);
                PMTnoPay = PMT;
                console.log("PMT: $" + PMT);
                console.log('');


                // Update DOM
                document.getElementById('startDate').innerText = currDay + "/" + currMonth + "/" + currYear;
                document.getElementById('startPrinciple').innerText = `$${startPrinciple.toFixed(2)}`;
                document.getElementById('startInterest').innerText = `${(startInterest * 100).toFixed(2)}%`;
                document.getElementById('duration').innerText = `${startDuration} years`;
                document.getElementById('paymentInterval').innerText = startIntervalStr;

                // Get interest
                const intRes = await fetch(`api/interests.php?user_id=${userId}&db_set=${dbSet}`);
                interest = await intRes.json();


                //if interest change is before loan start date this needs to be checked before simulation begins
                while(!interestReady && icc < interest.length){
                    intDate = interest[icc].date_interest.split("-").map(Number);

                    if(intDate[0] < currYear){
                        icc++;
                    }else if (intDate[1] < currMonth && intDate[0] == currYear){
                        icc++;
                    }else if (intDate[2] < currDay && intDate[1] == currMonth && intDate[0] == currYear){
                        icc++;
                    }else{
                        interestReady = true;
                    }
                }


                // Get payments
                const payRes = await fetch(`api/payments.php?user_id=${userId}&db_set=${dbSet}`);
                payment = await payRes.json();

                //if payment is before loan start date this needs to be checked before simulation begins
                while(!paymentReady && pmc < payment.lenght){
                    payDate = payment[icc].date_additional_payment.split("-").map(Number);

                    if(payDate[0] < currYear){
                        pmc++;
                    }else if (payDate[1] < currMonth && payDate[0] == currYear){
                        pmc++;
                    }else if (payDate[2] < currDay && payDate[1] == currMonth && payDate[0] == currYear){
                        pmc++;
                    }else{
                        paymentReady = true;
                    }
                }


        //set array info to display payment changes in loan
                variableChangeArray[0] = 
                    ["Starting Values",                             //Title
                    currDay + "/" + currMonth + "/" + currYear,     //current date
                    (startInterest * 100).toFixed(2) + "%",                    //new interest
                    "Starting Payment",                             //Statement if PMT recalculated
                    "<td>$" + PMT.toFixed(2)                        //PMT   
                ]; 
                variableChangeArrayNoPay[0] = 
                    ["Starting Values",                             //Title
                    currDay + "/" + currMonth + "/" + currYear,     //current date
                    (startInterest * 100).toFixed(2) + "%",                    //new interest
                    "Starting Payment",                             //Statement if PMT recalculated
                    "<td>$" + PMTnoPay.toFixed(2)                   //PMTnoPay   
                ]; 


                // Now that everything is ready, run your main logic
                runMainSimulationLogic();

            } catch (err) {
                console.error("Load error:", err);
                alert("Could not load required data.");
            }
        }

        // Start everything after the page is loaded
        document.addEventListener("DOMContentLoaded", loadAndRun);

    </script>

</body>
</html>
