<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "Visitors_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get selected month (if any)
$selectedMonth = $_GET['month'] ?? '';

// Total visitors
$totalQuery = "SELECT SUM(visitor_count) as total_visitors FROM visitor";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalVisitors = $totalRow['total_visitors'] ?? 0;

// Visitors grouped by purpose
$groupQuery = "SELECT purpose, SUM(visitor_count) as count FROM visitor GROUP BY purpose";
$groupResult = $conn->query($groupQuery);

// Monthly summary
$monthlyQuery = "
    SELECT DATE_FORMAT(visit_date, '%Y-%m') as month_key, DATE_FORMAT(visit_date, '%M %Y') as month_year, SUM(visitor_count) as count 
    FROM visitor 
    GROUP BY YEAR(visit_date), MONTH(visit_date)
    ORDER BY YEAR(visit_date) DESC, MONTH(visit_date) DESC
";
$monthlyResult = $conn->query($monthlyQuery);

// Get monthly options
$monthOptions = [];
$monthCounts = [];

while ($row = $monthlyResult->fetch_assoc()) {
    $monthOptions[$row['month_key']] = $row['month_year'];
    $monthCounts[$row['month_key']] = $row['count'];
}

// Filtered visitor count
$filteredCount = '';
if ($selectedMonth && isset($monthCounts[$selectedMonth])) {
    $filteredCount = $monthCounts[$selectedMonth];
}

// Fetch all visitors
$allQuery = "SELECT * FROM visitor ORDER BY visit_date DESC";
$allResult = $conn->query($allQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Visitor Records</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <style>
       body {
           font-family: 'Roboto', sans-serif;
           background-color: #f7f9fc;
           background-size: cover; 
           background-position: center center; 
           background-attachment: fixed; 
           margin: 0;
           padding: 20px;
           color: #333;
       }

        h1, h2, h3 {
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e0f7fa;
        }

        img {
            border-radius: 5px;
        }

        .total-visitors {
            font-size: 1.4em;
            background: #eaf2f8;
            padding: 10px 20px;
            border-left: 5px solid #2980b9;
            margin-bottom: 30px;
            display: inline-block;
        }

        .filter-box {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Visitor Record System</h1>

    <div class="total-visitors">👥 Total Visitors: <?= $totalVisitors ?></div>

    <div class="filter-box">
        <form method="get">
            <label for="month">📅 Select Month:</label>
            <select name="month" id="month" onchange="this.form.submit()">
                <option value="">-- All Months --</option>
                <?php foreach ($monthOptions as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($selectedMonth == $key) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($selectedMonth && $filteredCount !== ''): ?>
        <div class="total-visitors">
            ✅ Visitors in <?= $monthOptions[$selectedMonth] ?>: <strong><?= $filteredCount ?></strong>
        </div>
    <?php endif; ?>
    <h3>🧭 Visitors Grouped by Exposure</h3>
    <table>
        <thead>
            <tr>
                <th>Exposure</th>
                <th>Number of Visitors</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $groupResult->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['purpose']) ?></td>
                    <td><?= $row['count'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h3>📋 All Visitor Entries</h3>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Exposure</th>
                <th>Date</th>
                <th>No. of Visitors</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $allResult->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['purpose']) ?></td>
                    <td><?= $row['visit_date'] ?></td>
                    <td><?= $row['visitor_count'] ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><img src="<?= $row['image_path'] ?>" width="50" height="50" alt="visitor image"></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>

<?php $conn->close(); ?>
