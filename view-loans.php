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
            require_once("button-functions.php");

            echo '<h1 class="text-center my-4">List of Loans</h1>';

            addBtn("add-loan-btn", null, "Add Loan");
            echo "<br>";

            echo <<<END
            <table id="loansTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Loan ID</th>
                    <th>Starting Date</th>
                    <th>Starting Interest</th>
                    <th>Start Principle</th>
                    <th>Duration (years)</th>
                    <th>Payment Intervals</th>
                    <th>Changing Elements</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            </table>
    END;
        ?>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const userId = <?= json_encode($_SESSION['id-user'] ?? 0) ?>;

                fetch(`api/loans.php?user_id=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        const tbody = document.querySelector("#loansTable tbody");
                        tbody.innerHTML = ""; // clear existing rows

                        data.forEach(row => {
                            const tr = document.createElement("tr");
                            tr.innerHTML = `
                                <td>${row.DB_set}</td>
                                <td>${row.start_date}</td>
                                <td>${row.start_interest}%</td>
                                <td>$${row.start_principle}</td>
                                <td>${row.duration_years}</td>
                                <td>${row.payment_interval}</td>
                                <td>
                                    <form action="loan-elements.php" method="GET">
                                        <input type="hidden" name="DB_set" value="${row.DB_set}">
                                        <button type="submit" class="btn btn-primary">View Changing Elements</button>
                                    </form>
                                </td>
                                <td>
                                    <button 
                                        class="btn btn-warning edit-loan-btn"
                                        data-user="${userId}"
                                        data-db="${row.DB_set}"
                                        data-date="${row.start_date}" 
                                        data-interest="${row.start_interest}"
                                        data-amount="${row.start_principle}" 
                                        data-time="${row.duration_years}"
                                        data-interval="${row.payment_interval}">
                                        Edit
                                    </button>
                                </td>
                                <td>
                                    <button 
                                        class="btn btn-danger delete-loan-btn"
                                        data-user="${userId}"
                                        data-db="${row.DB_set}"
                                        data-date="${row.start_date}" 
                                        data-interest="${row.start_interest}"
                                        data-amount="${row.start_principle}" 
                                        data-time="${row.duration_years}"
                                        data-interval="${row.payment_interval}">
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
            require('footer-logged-in.php');
        } else {
            if (isset($_SESSION['valid_user'])) {
                echo "Could not log you in.<br>";
            }
            require('login.php');
        }


        include("modal-functions.html");
        ?>

        <script>
            // === Loan ADD ===
            const addLoanModal = new bootstrap.Modal(document.getElementById('addLoanModal'));
            document.querySelectorAll('.add-loan-btn').forEach(button => {
                button.addEventListener('click', function () {;
                    addLoanModal.show();
                });
            });

            document.getElementById('addLoanForm').addEventListener('submit', function (e) {
                e.preventDefault();
                // build payload + fetch (POST)
                const ID_user = <?= json_encode($_SESSION['id-user'] ?? null) ?>;
                const formData = new FormData(this);

                const payload = {
                    ID_user,
                    DB_set: parseInt(document.getElementById('loan-edit-db-set').value),
                    start_date: formData.get('date'),
                    start_interest: parseFloat(formData.get('interest')),
                    start_principle: parseFloat(formData.get('principle')),
                    duration_years: parseInt(formData.get('duration')),
                    payment_interval: formData.get('interval')
                };

                console.log("Submitting with:", payload);

                fetch('api/loans.php', {
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


            // === LOAN EDIT ===
            const editLoanModal = new bootstrap.Modal(document.getElementById('editLoanModal'));
            document.querySelector("#loansTable tbody").addEventListener("click", function (e) {
                if (e.target.classList.contains("edit-loan-btn")) {
                    const button = e.target;

                    document.getElementById('loan-edit-db-set').value = button.getAttribute('data-db');
                    document.getElementById('loan-edit-date').value = button.getAttribute('data-date');
                    document.getElementById('loan-edit-interest').value = button.getAttribute('data-interest');
                    document.getElementById('loan-edit-principle').value = button.getAttribute('data-amount');
                    document.getElementById('loan-edit-duration').value = button.getAttribute('data-time');

                    const interval = button.getAttribute('data-interval');
                    const select = document.querySelector('#editLoanModal select[name="interval"]');
                    
                    select.innerHTML = '';  // clear existing

                    const options = ['Monthly', 'Fortnightly', 'Weekly'];
                    // Put selected first, then others
                    [interval, ...options.filter(opt => opt !== interval)].forEach(opt => {
                        const option = document.createElement('option');
                        option.value = opt;
                        option.textContent = opt;
                        select.appendChild(option);
                    });

                    editLoanModal.show();
                }
            });

            document.getElementById('editLoanForm').addEventListener('submit', function (e) {
                e.preventDefault();

                const ID_user = <?= json_encode($_SESSION['id-user'] ?? null) ?>;
                const formData = new FormData(this);

                const payload = {
                    ID_user,
                    DB_set: parseInt(document.getElementById('loan-edit-db-set').value),
                    start_date: formData.get('date'),
                    start_interest: parseFloat(formData.get('interest')),
                    start_principle: parseFloat(formData.get('principle')),
                    duration_years: parseInt(formData.get('duration')),
                    payment_interval: formData.get('interval')
                };

                console.log("Submitting with:", payload);

                fetch('api/loans.php', {
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

            // === LOAN DELETE ===
            const deleteLoanModal = new bootstrap.Modal(document.getElementById('deleteLoanModal'));
            document.querySelector("#loansTable tbody").addEventListener("click", function (e) {
                if (e.target.classList.contains("delete-loan-btn")) {
                    // fill form + show modal
                    const button = e.target;

                    document.getElementById('loan-delete-db-set').value = button.getAttribute('data-db');
                    document.getElementById('loan-delete-date').value = button.getAttribute('data-date');
                    document.getElementById('loan-delete-interest').value = button.getAttribute('data-interest');
                    document.getElementById('loan-delete-principle').value = button.getAttribute('data-amount');
                    document.getElementById('loan-delete-duration').value = button.getAttribute('data-time');
                    document.getElementById('loan-delete-interval').value = button.getAttribute('data-interval');

                    deleteLoanModal.show();
                }
            });

            document.getElementById('deleteLoanForm').addEventListener('submit', function (e) {
                e.preventDefault();
                // build payload + fetch (DELETE)
                const ID_user = <?= json_encode($_SESSION['id-user'] ?? null) ?>;
                const formData = new FormData(this);

                const payload = {
                    ID_user,
                    DB_set: parseInt(document.getElementById('loan-delete-db-set').value),
                };

                console.log("Submitting with:", payload);

                fetch('api/loans.php', {
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
        </script>
    </div>
</body>
</html>
