<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();
// Date range
$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

// Sales statistics
$salesStats = $db->prepare("SELECT 
    COUNT(*) as order_count,
    SUM(total) as total_sales,
    AVG(total) as avg_order,
    SUM(tax_amount) as total_tax
    FROM orders 
    WHERE status != 'cancelled' AND order_date BETWEEN ? AND ?");
$salesStats->execute([$dateFrom, $dateTo]);
$salesStats = $salesStats->fetch();
// Get accounting summary
$totalRevenue = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$totalExpenses = $db->query("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE status = 'approved'")->fetchColumn();
$totalPurchases = $db->query("SELECT COALESCE(SUM(total), 0) FROM purchases WHERE status != 'cancelled'")->fetchColumn();
$pendingPayments = $db->query("SELECT COALESCE(SUM(total - paid_amount), 0) FROM orders WHERE payment_status != 'paid' AND status != 'cancelled'")->fetchColumn();

// Monthly data
$monthlyRevenue = $db->query("SELECT MONTH(created_at) as month, SUM(total) as total FROM orders WHERE status != 'cancelled' AND YEAR(created_at) = YEAR(CURDATE()) GROUP BY MONTH(created_at)")->fetchAll(PDO::FETCH_KEY_PAIR);
$monthlyExpenses = $db->query("SELECT MONTH(expense_date) as month, SUM(amount) as total FROM expenses WHERE status = 'approved' AND YEAR(expense_date) = YEAR(CURDATE()) GROUP BY MONTH(expense_date)")->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Comptabilité';
$currentPage = 'accounting';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-calculator"></i> Comptabilité</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Comptabilité</span>
        </nav>
    </div>
    <div class="quick-actions">
        <a href="expenses.php" class="btn btn-warning"><i class="fas fa-money-bill-wave"></i> Dépenses</a>
    </div>
</div>

<!-- Summary Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-arrow-up"></i></div>
        <div class="stat-content">
            <h3><?php echo formatMoney($totalRevenue); ?></h3>
            <p>Revenus Totaux</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-arrow-down"></i></div>
        <div class="stat-content">
            <h3><?php echo formatMoney($totalExpenses); ?></h3>
            <p>Dépenses Totales</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-shopping-cart"></i></div>
        <div class="stat-content">
            <h3><?php echo formatMoney($totalPurchases); ?></h3>
            <p>Total Achats</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><i class="fas fa-wallet"></i></div>
        <div class="stat-content">
            <h3><?php echo formatMoney($pendingPayments); ?></h3>
            <p>Paiements en Attente</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-chart-line"></i></div>
        <div class="stat-content">
            <h3><?php echo formatMoney($totalRevenue - $totalExpenses - $totalPurchases); ?></h3>
            <p>Résultat Net</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-percentage"></i></div>
        <div class="stat-content">
            <h3><?php echo formatMoney($salesStats['total_tax'] ?: 0); ?></h3>
            <p>TVA Collectée</p>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="dashboard-grid">
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-chart-bar"></i> Revenus vs Dépenses</h3>
        </div>
        <div class="widget-body">
            <div class="chart-container">
                <canvas id="accountingChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-list"></i> Comptes</h3>
        </div>
        <div class="widget-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Compte</th>
                            <th>Type</th>
                            <th>Solde</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $accounts = $db->query("SELECT * FROM accounts WHERE status = 'active' ORDER BY account_code ASC LIMIT 10")->fetchAll();
                        foreach ($accounts as $account):
                        ?>
                        <tr>
                            <td><?php echo $account['account_code']; ?> - <?php echo $account['account_name']; ?></td>
                            <td><span class="badge badge-secondary"><?php echo ucfirst($account['account_type']); ?></span></td>
                            <td><?php echo formatMoney($account['balance']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    -->
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
const revenueData = <?php echo json_encode(array_values($monthlyRevenue + array_fill(1, 12, 0))); ?>;
const expenseData = <?php echo json_encode(array_values($monthlyExpenses + array_fill(1, 12, 0))); ?>;

new Chart(document.getElementById('accountingChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Revenus',
            data: revenueData,
            backgroundColor: 'rgba(40, 167, 69, 0.8)'
        }, {
            label: 'Dépenses',
            data: expenseData,
            backgroundColor: 'rgba(220, 53, 69, 0.8)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>
