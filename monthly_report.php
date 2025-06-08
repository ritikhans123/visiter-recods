<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "Visitors_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$selectedMonth = $_GET['month'] ?? '';

// Fetch month list
$monthQuery = "
    SELECT DISTINCT DATE_FORMAT(visit_date, '%Y-%m') AS month_key, 
           DATE_FORMAT(visit_date, '%M %Y') AS month_name 
    FROM visitor 
    ORDER BY visit_date DESC
";
$monthResult = $conn->query($monthQuery);
$monthOptions = [];
while ($row = $monthResult->fetch_assoc()) {
    $monthOptions[$row['month_key']] = $row['month_name'];
}

// Fetch data for selected month
$visitors = [];
$monthlyCount = 0;

if ($selectedMonth) {
    $stmt = $conn->prepare("
        SELECT * FROM visitor 
        WHERE DATE_FORMAT(visit_date, '%Y-%m') = ?
        ORDER BY visit_date DESC
    ");
    $stmt->bind_param("s", $selectedMonth);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $visitors[] = $row;
        $monthlyCount += $row['visitor_count'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Monthly Visitor Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
            color: #333;
        }

        h1 {
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 20px;
        }

        select {
            padding: 8px;
            font-size: 16px;
        }

        button {
            padding: 8px 16px;
            font-size: 16px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .summary {
            margin-top: 20px;
            font-size: 1.2em;
            background: #eaf2f8;
            padding: 10px;
            border-left: 5px solid #2980b9;
            display: inline-block;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <h1>📅 Monthly Visitor Report</h1>

    <form method="get" class="no-print">
        <div class="form-group">
            <label>Select Month:</label>
            <select name="month" onchange="this.form.submit()">
                <option value="">-- Choose Month --</option>
                <?php foreach ($monthOptions as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($selectedMonth == $key) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php if ($selectedMonth): ?>
        <div class="summary">
            📈 Total Visitors in <?= $monthOptions[$selectedMonth] ?>: <strong><?= $monthlyCount ?></strong>
        </div>

        <button onclick="window.print()" class="no-print">🖨️ Print Report</button>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Purpose</th>
                    <th>Date</th>
                    <th>No. of Visitors</th>
                    <th>Phone</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($visitors as $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($v['name']) ?></td>
                        <td><?= htmlspecialchars($v['purpose']) ?></td>
                        <td><?= $v['visit_date'] ?></td>
                        <td><?= $v['visitor_count'] ?></td>
                        <td><?= htmlspecialchars($v['phone']) ?></td>
                        <td><?= htmlspecialchars($v['address']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif (isset($_GET['month'])): ?>
        <p>No visitors found for the selected month.</p>
    <?php endif; ?>

</body>
</html>

<?php $conn->close(); ?>
