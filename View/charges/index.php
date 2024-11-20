<!DOCTYPE html>
<html>

<head>
    <title>Charges - My Portfolio</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>
    <div class="nav-bar">
        <div>
            <h1>Charges Management</h1>
            <!-- <p class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['user']['Fullname'] ?? 'User'); ?> -->
            </p>
        </div>
        <div>
            <a href="index.php?controller=portefeuille&action=index" class="action-button">Back to Dashboard</a>
            <a href="index.php?controller=user&action=logout" class="action-button">Logout</a>
        </div>
    </div>

    <div class="card">
        <button class="action-button" onclick="openModal()">Add New Charge</button>
        <h2>Your Charges</h2>
        <div class="filters">
            <select id="typeFilter">
                <option value="all">All Types</option>
                <option value="fixed">Fixed</option>
                <option value="variable">Variable</option>
            </select>
            <select id="monthFilter">
                <option value="all">All Months</option>
                <!-- Add month options dynamically if needed -->
            </select>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($charges as $charge): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($charge['NomCharge']); ?></td>
                        <td><?php echo htmlspecialchars($charge['Description']); ?></td>
                        <td><?php echo htmlspecialchars($charge['Montant']); ?> DH</td>
                        <td><?php echo htmlspecialchars($charge['Variable'] == 1 ? 'Variable' : 'Fixe'); ?></td>
                        <td><?php echo htmlspecialchars($charge['DateCharge']); ?></td>
                        <td>
                            <form method="post" action="index.php?controller=charges&action=delete"
                                style="display: flex; align-items: center; justify-content: center;">
                                <input type="hidden" name="CodeCharge" value="<?php echo $charge['CodeCharge']; ?>">
                                <button type="button" class="edit-button"
                                    onclick="openEditModal(<?php echo $charge['CodeCharge']; ?>)">Edit</button>
                                <button type="submit" class="delete-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="chargeModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2>Add New Charge</h2>
            <form method="post" action="index.php?controller=charges&action=create" class="form-group">
                <div>
                    <input type="hidden" name="CodePortefeuille"
                        value="<?php echo htmlspecialchars($_SESSION['user']['CodePortefeuille']); ?>">
                </div>
                <div>
                    <label>Nom de la charge:</label>
                    <input type="text" name="NomCharge" required>
                </div>
                <div>
                    <label>Description:</label>
                    <input type="text" name="Description" required>
                </div>
                <div>
                    <label>Amount:</label>
                    <input type="number" name="Montant" required>
                </div>
                <div>
                    <div class="radio-group">
                        <label>Type:</label>
                        <div class="radio-option">
                            <input type="radio" id="variable" name="Variable" value="1" required>
                            <label for="variable">Variable</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="fixe" name="Variable" value="0">
                            <label for="fixe">Fixe</label>
                        </div>
                    </div>
                </div>
                <div class="date-group">
                    <label>Date:</label>
                    <input type="date" name="DateCharge" required id="chargeDate">
                </div>
                <button type="submit" class="action-button">Add Charge</button>
            </form>
        </div>
    </div>

    <script>
        // Set default date to today
        document.addEventListener('DOMContentLoaded', function () {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('chargeDate').value = today;
        });

        // Modal functions
        function openModal() {
            document.getElementById('chargeModal').style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
        }

        function openEditModal(chargeId) {
            document.getElementById('chargeModal').style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
            
            // Update modal title
            document.querySelector('.modal-content h2').textContent = 'Edit Charge';
            
            // Change form action to edit
            const form = document.querySelector('.modal-content form');
            form.action = 'index.php?controller=charges&action=update';
            
            // Add charge ID to form
            let chargeIdInput = form.querySelector('input[name="CodeCharge"]');
            if (!chargeIdInput) {
                chargeIdInput = document.createElement('input');
                chargeIdInput.type = 'hidden';
                chargeIdInput.name = 'CodeCharge';
                form.appendChild(chargeIdInput);
            }
            chargeIdInput.value = chargeId;
            
            // Fetch charge data using AJAX
            fetch(`index.php?controller=charges&action=get&id=${chargeId}`)
                .then(response => response.json())
                .then(charge => {
                    // Populate form fields
                    form.querySelector('input[name="NomCharge"]').value = charge.NomCharge;
                    form.querySelector('input[name="Description"]').value = charge.Description;
                    form.querySelector('input[name="Montant"]').value = charge.Montant;
                    form.querySelector(`input[name="Variable"][value="${charge.Variable}"]`).checked = true;
                    form.querySelector('input[name="DateCharge"]').value = charge.DateCharge;
                    
                    // Update submit button text
                    form.querySelector('button[type="submit"]').textContent = 'Update Charge';
                })
                .catch(error => {
                    console.error('Error fetching charge data:', error);
                    alert('Error loading charge data. Please try again.');
                    closeModal();
                });
        }

        function closeModal() {
            document.getElementById('chargeModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore scrolling
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            const modal = document.getElementById('chargeModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Close modal on escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>

</html>