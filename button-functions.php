<?php
	//simulate button
    function createButtonColumn1($hiddenName, $hiddenValue, $buttonText, $actionPage) {
        echo "<td>";
        echo "<form action=\"$actionPage\" method=\"GET\">";
        echo "<input type=\"hidden\" name=\"$hiddenName\" value=\"$hiddenValue\">";
        echo "<button type=\"submit\" class=\"btn btn-primary\">$buttonText</button>";
        echo "</form>";            
        echo "</td>";
    }

    function addBtn($addType, $hiddenValue, $buttonText) {
        echo "<td><div class='d-grid gap-2 d-md-block'>";
        if($addType == "add-loan-btn"){
            echo "<button 
                class=\"btn btn-primary $addType\">
                $buttonText
              </button>";         
            echo "</div></td>";
        }
        else
        {
            echo "<button 
                    class=\"btn btn-primary $addType\" 
                    data-db=\"$hiddenValue\">
                    $buttonText
                  </button>";         
            echo "</div></td>";
        }
    }

    //edit button
    function editBtn($editType, $dbSet, $paymentId, $date, $amount, $pmt, $buttonText, $interest, $duration, $interval) {
	    echo "<td>";
        if($editType == "edit-loan-btn"){
            echo "<button 
            class=\"btn btn-warning $editType\" 
            data-db=\"$dbSet\" 
            data-date=\"$date\" 
            data-interest=\"$interest\"
            data-amount=\"$amount\" 
            data-time=\"$duration\"
            data-interval=\"$interval\">
            $buttonText
        </button>";
        }
        else
        {
	    echo "<button 
            class=\"btn btn-warning $editType\" 
            data-db=\"$dbSet\" 
            data-id=\"$paymentId\" 
            data-date=\"$date\" 
            data-amount=\"$amount\" 
            data-pmt=\"$pmt\">
	        $buttonText
	    </button>";
        }
	    echo "</td>";
	}


    //delete button
    function deleteBtn($deleteType, $dbSet, $paymentId, $date, $amount, $pmt, $buttonText, $interest, $duration, $interval) {
        echo "<td>";
        if($deleteType == "delete-loan-btn"){
            echo "<button 
            class=\"btn btn-danger $editType\" 
            data-db=\"$dbSet\" 
            data-date=\"$date\" 
            data-interest=\"$interest\"
            data-amount=\"$amount\" 
            data-time=\"$duration\"
            data-interval=\"$interval\">
            $buttonText
        </button>";
        }
        else
        {
        echo "<button 
            class=\"btn btn-danger $deleteType\" 
            data-db=\"$dbSet\" 
            data-id=\"$paymentId\" 
            data-date=\"$date\" 
            data-amount=\"$amount\" 
            data-pmt=\"$pmt\">
            $buttonText
        </button>";
        }
        echo "</td>";
    }
?>