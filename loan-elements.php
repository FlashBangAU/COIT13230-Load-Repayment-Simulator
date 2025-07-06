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


            // Validate and get DB_set from GET parameter
            if (isset($_GET['DB_set']) && is_numeric($_GET['DB_set'])) {
                $DbID = (int)$_GET['DB_set'];
            } else {
                echo "Invalid loan ID.";
                $db->close();
                exit;
            }

            require_once("button-functions.php");
        }
            ?>

            <div id="loanDetails" class="mb-3 text-center"></div>

            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const userId = <?= json_encode($_SESSION['id-user'] ?? 0) ?>;
                    const dbSet = <?= json_encode($_GET['DB_set'] ?? 0) ?>;

                    fetch(`api/loans.php?DB_set=${dbSet}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length === 0) {
                                document.getElementById('loanDetails').innerHTML = "<p>No loan found.</p>";
                                return;
                            }
                            
                            const loan = data[0];

                            console.log(data);

                            // Build the HTML string dynamically
                            const html = `
                                <div class="row">
                                    <div class="col-md">
                                        <form action="simulate.php" method="GET" style="display:inline-block;">
                                        <input type="hidden" name="DB_set" value="${loan.DB_set}">
                                        <button type="submit" class="btn btn-primary">Simulate Loan</button>
                                    </form>
                                    </div>
                                    <div class="col-md"><b>Loan Start Date:</b> ${loan.start_date}</div>
                                    <div class="col-md"><b>Principle:</b> $${loan.start_principle}</div>
                                    <div class="col-md"><b>Beginning Interest:</b> ${loan.start_interest}%</div>
                                    <div class="col-md"><b>Duration:</b> ${loan.duration_years} years</div>
                                    <div class="col-md"><b>Interest Added:</b> ${loan.payment_interval}</div>
                                </div>
                            `;

                            // Insert HTML into the page
                            document.getElementById('loanDetails').innerHTML = html;
                        })
                        .catch(err => {
                            console.error("Fetch error:", err);
                            document.getElementById('loanDetails').innerHTML = "<p>Error loading loan details.</p>";
                        });
                });
            </script>

            <?php
            echo "<br>";
            addBtn("add-interest-btn", $DbID, "Add Payment");
            echo <<<END
            <table id="interestsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Interest Change Date</th>
                        <th>Interest Change Amount</th>
                        <th>Recalculate Payment</th>
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
                        .then(response => response.json())
                        .then(data => {
                            const tbody = document.querySelector("#interestsTable tbody");
                            tbody.innerHTML = ""; // clear existing rows

                            console.log(data);

                            data.forEach(row => {
                                const tr = document.createElement("tr");
                                tr.innerHTML = `
                                    <td>${row.date_interest}</td>
                                    <td>${row.new_val_interest}%</td>
                                    <td><input type="checkbox" class="form-check-input" ${row.update_PMT == 1 ? "checked" : ""} disabled></td>
                                    <td>
                                        <button 
                                            class="btn btn-warning edit-interest-btn"
                                            data-db="${dbSet}"
                                            data-id="${row.interest_ID}"
                                            data-date="${row.date_interest}"
                                            data-amount="${row.new_val_interest}"
                                            data-pmt="${row.update_PMT}">
                                            Edit
                                        </button>
                                    </td>
                                    <td>
                                        <button 
                                            class="btn btn-danger delete-interest-btn"
                                            data-db="${dbSet}"
                                            data-id="${row.interest_ID}"
                                            data-date="${row.date_interest}"
                                            data-amount="${row.new_val_interest}"
                                            data-pmt="${row.update_PMT}">
                                            Delete
                                        </button>
                                    </td>
                                `;
                                tbody.appendChild(tr);
                            });

                            // OPTIONAL: attach dynamic listeners if needed
                        })
                        .catch(error => {
                            console.error("Error loading interests:", error);
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
                        <th>End Payment Date (If Recurring)</th>
                        <th>Recalculate Payment</th>
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
                        .then(response => response.json())
                        .then(data => {
                            const tbody = document.querySelector("#paymentsTable tbody");
                            tbody.innerHTML = "";

                            console.log(data);

                            data.forEach(row => {
                                const tr = document.createElement("tr");
                                tr.innerHTML = `
                                    <td>${row.date_additional_payment}</td>
                                    <td>$${row.amount_additional_payments}</td>
                                    <td><input type="checkbox" class="form-check-input" ${row.payment_recurring_toggle == 1 ? "checked" : ""} disabled> ${row.date_end_payments}</td>
                                    <td><input type="checkbox" class="form-check-input" ${row.update_PMT == 1 ? "checked" : ""} disabled></td>
                                    <td>
                                        <button 
                                            class="btn btn-warning edit-payment-btn"
                                            data-db="${dbSet}"
                                            data-id="${row.payment_ID}"
                                            data-date="${row.date_additional_payment}"
                                            data-amount="${row.amount_additional_payments}"
                                            data-enddate="${row.date_end_payments}"
                                            data-recurring="${row.payment_recurring_toggle}"
                                            data-pmt="${row.update_PMT}">
                                            Edit
                                        </button>
                                    </td>
                                    <td>
                                        <button 
                                            class="btn btn-danger delete-payment-btn"
                                            data-db="${dbSet}"
                                            data-id="${row.payment_ID}"
                                            data-date="${row.date_additional_payment}"
                                            data-amount="${row.amount_additional_payments}"
                                            data-enddate="${row.date_end_payments}"
                                            data-recurring="${row.payment_recurring_toggle}"
                                            data-pmt="${row.update_PMT}">
                                            Delete
                                        </button>
                                    </td>
                                `;
                                tbody.appendChild(tr);
                            });

                            // OPTIONAL: attach event handlers to the buttons here if needed
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
                date_end_payments: formData.get("endDate"),
                payment_recurring_toggle: formData.get("add-pay-recurring") ? 1 : 0,
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
                document.getElementById('pay-edit-enddate').value = button.getAttribute('data-enddate');
                document.getElementById('pay-edit-recurring').checked = button.getAttribute('data-recurring') === "1"
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
                date_end_payments: formData.get("endDate"),
                payment_recurring_toggle: formData.get("edit-pay-recurring") ? 1 : 0,
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
                document.getElementById('pay-delete-enddate').value = button.getAttribute('data-enddate');
                document.getElementById('pay-delete-recurring').checked = button.getAttribute('data-recurring') === "1"
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
