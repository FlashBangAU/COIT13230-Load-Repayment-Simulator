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
        echo "<button 
                class=\"btn btn-primary $addType\" 
                data-db=\"$hiddenValue\">
                $buttonText
              </button>";         
        echo "</div></td>";
    }

    //edit button
    function editBtn($editType, $dbSet, $paymentId, $date, $amount, $pmt, $buttonText) {
	    echo "<td>";
	    $btnClass = $editType === "edit-interest-btn" ? "edit-interest-btn" : "edit-payment-btn";
	    echo "<button 
	        class=\"btn btn-warning $btnClass\" 
	        data-db=\"$dbSet\" 
	        data-id=\"$paymentId\" 
	        data-date=\"$date\" 
	        data-amount=\"$amount\" 
	        data-pmt=\"$pmt\">
	        $buttonText
	    </button>";
	    echo "</td>";
	}


    //delete button
    function deleteBtn($deleteType, $dbSet, $paymentId, $date, $amount, $pmt, $buttonText) {
        echo "<td>";
        $btnClass = $deleteType === "delete-interest-btn" ? "delete-interest-btn" : "delete-payment-btn";
        echo "<button 
            class=\"btn btn-danger $btnClass\" 
            data-db=\"$dbSet\" 
            data-id=\"$paymentId\" 
            data-date=\"$date\" 
            data-amount=\"$amount\" 
            data-pmt=\"$pmt\">
            $buttonText
        </button>";
        echo "</td>";
    }
?>