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
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const DB_set = urlParams.get("DB_set");

                console.log("Submitting with:", { DB_set });

                fetch(`api/payments.php?DB_set=${DB_set}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log("Got response:", data);
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Fetch error:", error);
                        alert('A network or server error occurred.');
                    });
            });
        </script>



        <?php
        session_start();
        $validSession = require('check-session.php');
        $validLogin = require("check-login.php");

        if ($validLogin || $validSession) {
            echo '<h1 class="text-center my-4">Loan Elements</h1>';

            require("db-connection.php");

            // Validate and get DB_set from GET parameter
            if (isset($_GET['DB_set']) && is_numeric($_GET['DB_set'])) {
                $DbID = (int)$_GET['DB_set'];
            } else {
                echo "Invalid loan ID.";
                $db->close();
                exit;
            }

            require_once("button-functions.php");

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
                $id = $row['DB_set'];
                $startDate = $row['start_date'];
                $startInterest = $row['start_interest'];
                $startPrinciple = $row['start_principle'];
                $durationYears = $row['duration_years'];
                $paymentInterval = $row['payment_interval'];

                echo "<div class='mb-3 text-center row'><div class='col-md'>";
                createButtonColumn1("DB_set", $DbID, "Simulate", "simulate.php");
                        echo "</div>
                        <div class='col-md'>
                            <b>Loan Start Date:</b> $startDate &nbsp; 
                        </div>
                        <div class='col-md'>
                            <b>Principle:</b> $$startPrinciple &nbsp; 
                        </div>
                        <div class='col-md'>
                            <b>Beginning Interest:</b> $startInterest% &nbsp; 
                        </div>
                        <div class='col-md'>
                            <b>Duration:</b> $durationYears years &nbsp; 
                        </div>
                        <div class='col-md'>
                            <b>Interest Added:</b> $paymentInterval
                        </div>
                    </div>";
                }
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

            addBtn("add-interest-btn", $DbID, "Add Interest");
            echo <<<END
            <table id="interestsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Interest Change Date</th>
                        <th>Interest Change Amount</th>
                        <th>Update Payment</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
    END;
            ?>

             <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const userId = <?= json_encode($_SESSION['id-user'] ?? 0) ?>;
                    const dbSet = <?= json_encode($_GET['DB_set'] ?? 0) ?>;

                    fetch(`api/interests.php?user_id=${userId}&db_set=${dbSet}`)
                        .then(response => response.text())  // because API returns HTML
                        .then(htmlRows => {
                            document.querySelector("#interestsTable tbody").innerHTML = htmlRows;

                            // OPTIONAL: attach JS event listeners here if needed
                        })
                        .catch(error => {
                            console.error("Error loading payments:", error);
                        });
                });
            </script>


            <?php
            echo "<br>";
            addBtn("add-payment-btn", $DbID, "Add Payment");
            echo <<<END
            <table id="paymentsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Additional Payment Date</th>
                        <th>Payment Amount</th>
                        <th>Update Payment</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            END;
            ?>

            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const userId = <?= json_encode($_SESSION['id-user'] ?? 0) ?>;
                    const dbSet = <?= json_encode($_GET['DB_set'] ?? 0) ?>;

                    fetch(`api/payments.php?user_id=${userId}&db_set=${dbSet}`)
                        .then(response => response.text())  // because API returns HTML
                        .then(htmlRows => {
                            document.querySelector("#paymentsTable tbody").innerHTML = htmlRows;

                            // OPTIONAL: attach JS event listeners here if needed
                        })
                        .catch(error => {
                            console.error("Error loading payments:", error);
                        });
                });
            </script>



        <?php
        if($validLogin && $validSession){
            require('footer-logged-in.php');
        } else {
            if (isset($_SESSION['valid_user'])) {
                echo "Could not log you in.<br>";
            }
            require('login.php');
        }

        ?>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // === INTEREST ADD ===
        const addInterestModal = new bootstrap.Modal(document.getElementById('addInterestModal'));
        document.querySelectorAll('.add-interest-btn').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('int-add-db-set').value = button.getAttribute('data-db');
                addInterestModal.show();
            });
        });

        document.getElementById('addInterestForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // build payload + fetch (your original code)
            const ID_user = <?php echo isset($_SESSION['id-user']) ? (int)$_SESSION['id-user'] : 'null'; ?>;
            const formData = new FormData(this);

            const payload = {
                ID_user,
                DB_set: parseInt(document.getElementById('int-add-db-set').value),
                date_interest: formData.get("date"),
                new_val_interest: parseFloat(formData.get("amount")),
                update_PMT: formData.get("add-int-update_PMT") ? 1 : 0
            };

            console.log("Submitting with:", payload);

            fetch('api/interests.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error(error);
                alert('A network or server error occurred.');
            });
        });

        // === INTEREST EDIT ===
        const editInterestModal = new bootstrap.Modal(document.getElementById('editInterestModal'));
        document.querySelector("#interestsTable tbody").addEventListener("click", function (e) {
            if (e.target.classList.contains("edit-interest-btn")) {
                // fill form + show modal
                const button = e.target;

                document.getElementById('int-edit-db-set').value = button.getAttribute('data-db');
                document.getElementById('int-edit-interest-id').value = button.getAttribute('data-id');
                document.getElementById('int-edit-date').value = button.getAttribute('data-date');
                document.getElementById('int-edit-amount').value = button.getAttribute('data-amount');
                document.getElementById('int-edit-update-pmt').checked = button.getAttribute('data-pmt') === "1";

                editInterestModal.show();
            }
        });

        document.getElementById('editInterestForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // build payload + fetch (PUT)
            const ID_user = <?= json_encode($_SESSION['id-user'] ?? null) ?>;
            const formData = new FormData(this);

            const payload = {
                ID_user,
                DB_set: parseInt(document.getElementById('int-edit-db-set').value),
                interest_ID: parseInt(document.getElementById('int-edit-interest-id').value),
                date_interest: formData.get("date"),
                new_val_interest: Number(formData.get("amount")),
                update_PMT: formData.get("int-edit-update_PMT") ? 1 : 0
            };

            console.log("Submitting with:", payload);

            fetch('api/interests.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error(error);
                alert('A network or server error occurred.');
            });
        });

        // === INTEREST DELETE ===
        const deleteInterestModal = new bootstrap.Modal(document.getElementById('deleteInterestModal'));
        document.querySelector("#interestsTable tbody").addEventListener("click", function (e) {
            if (e.target.classList.contains("delete-interest-btn")) {
                // fill form + show modal
                const button = e.target;

                document.getElementById('int-delete-db-set').value = button.getAttribute('data-db');
                document.getElementById('int-delete-interest-id').value = button.getAttribute('data-id');
                document.getElementById('int-delete-date').value = button.getAttribute('data-date');
                document.getElementById('int-delete-amount').value = button.getAttribute('data-amount');
                document.getElementById('int-delete-update-pmt').checked = button.getAttribute('data-pmt') === "1";

                deleteInterestModal.show();
            }
        });

        document.getElementById('deleteInterestForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // build payload + fetch (DELETE)
            const ID_user = <?= json_encode($_SESSION['id-user'] ?? null) ?>;
            const formData = new FormData(this);

            const payload = {
                ID_user,
                DB_set: parseInt(document.getElementById('int-delete-db-set').value),
                interest_ID: parseInt(document.getElementById('int-delete-interest-id').value)
            };

            console.log("Submitting with:", payload);

            fetch('api/interests.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error(error);
                alert('A network or server error occurred.');
            });
        });

        // === PAYMENT ADD ===
        const addPaymentModal = new bootstrap.Modal(document.getElementById('addPaymentModal'));
        document.querySelectorAll('.add-payment-btn').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('pay-add-db-set').value = button.getAttribute('data-db');
                addPaymentModal.show();
            });
        });

        document.getElementById('addPaymentForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // build payload + fetch (POST)
            const ID_user = <?php echo isset($_SESSION['id-user']) ? (int)$_SESSION['id-user'] : 'null'; ?>;
            const formData = new FormData(this);

            const payload = {
                ID_user,
                DB_set: parseInt(document.getElementById('pay-add-db-set').value),
                date_additional_payment: formData.get("date"),
                amount_additional_payments: Number(formData.get("amount")),
                update_PMT: formData.get("add-pay-update_PMT") ? 1 : 0
            };

            console.log("Submitting with:", payload);

            fetch('api/payments.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error(error);
                alert('A network or server error occurred.');
            });
        });

        // === PAYMENT EDIT  ===
        const editPaymentModal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
        document.querySelector("#paymentsTable tbody").addEventListener("click", function (e) {
            if (e.target.classList.contains("edit-payment-btn")) {
                // fill form + show modal
                const button = e.target;

                document.getElementById('pay-edit-db-set').value = button.getAttribute('data-db');
                document.getElementById('pay-edit-payment-id').value = button.getAttribute('data-id');
                document.getElementById('pay-edit-date').value = button.getAttribute('data-date');
                document.getElementById('pay-edit-amount').value = button.getAttribute('data-amount');
                document.getElementById('pay-edit-update-pmt').checked = button.getAttribute('data-pmt') === "1";

                editPaymentModal.show();
            }
        });

        document.getElementById('editPaymentForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // build payload + fetch (PUT)
            const ID_user = <?= json_encode($_SESSION['id-user'] ?? null) ?>;
            const formData = new FormData(this);

            const payload = {
                ID_user,
                DB_set: parseInt(document.getElementById('pay-edit-db-set').value),
                payment_ID: parseInt(document.getElementById('pay-edit-payment-id').value),
                date_additional_payment: formData.get("date"),
                amount_additional_payments: Number(formData.get("amount")),
                update_PMT: formData.get("edit-pay-update_PMT") ? 1 : 0
            };

            console.log("Submitting with:", payload);

            fetch('api/payments.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error(error);
                alert('A network or server error occurred.');
            });
        });

        // === PAYMENT DELETE  ===
        const deletePaymentModal = new bootstrap.Modal(document.getElementById('deletePaymentModal'));
        document.querySelector("#paymentsTable tbody").addEventListener("click", function (e) {
            if (e.target.classList.contains("delete-payment-btn")) {
                // fill form + show modal
                const button = e.target;

                document.getElementById('pay-delete-db-set').value = button.getAttribute('data-db');
                document.getElementById('pay-delete-payment-id').value = button.getAttribute('data-id');
                document.getElementById('pay-delete-date').value = button.getAttribute('data-date');
                document.getElementById('pay-delete-amount').value = button.getAttribute('data-amount');
                document.getElementById('pay-delete-update-pmt').checked = button.getAttribute('data-pmt') === "1";

                deletePaymentModal.show();
            }
        });

        document.getElementById('deletePaymentForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // build payload + fetch (DELETE)
            const ID_user = <?= json_encode($_SESSION['id-user'] ?? null) ?>;
            const formData = new FormData(this);

            const payload = {
                ID_user,
                DB_set: parseInt(document.getElementById('pay-delete-db-set').value),
                payment_ID: parseInt(document.getElementById('pay-delete-payment-id').value)
            };

            console.log("Submitting with:", payload);

            fetch('api/payments.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error(error);
                alert('A network or server error occurred.');
            });
        });
    });
</script>

<?php
    include("modal-functions.html");
?>

</body>
</html>
