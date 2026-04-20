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

// Top selling articles
$topArticles = $db->prepare("SELECT a.name, SUM(oi.quantity) as qty, SUM(oi.total) as total
    FROM order_items oi
    JOIN articles a ON oi.article_id = a.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'cancelled' AND o.order_date BETWEEN ? AND ?
    GROUP BY a.id
    ORDER BY qty DESC
    LIMIT 10");
$topArticles->execute([$dateFrom, $dateTo]);
$topArticles = $topArticles->fetchAll();

// Top customers
$topCustomers = $db->prepare("SELECT c.company_name, c.contact_name, COUNT(o.id) as orders, SUM(o.total) as total
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    WHERE o.status != 'cancelled' AND o.order_date BETWEEN ? AND ?
    GROUP BY c.id
    ORDER BY total DESC
    LIMIT 10");
$topCustomers->execute([$dateFrom, $dateTo]);
$topCustomers = $topCustomers->fetchAll();

// Daily sales for chart
$dailySales = $db->prepare("SELECT DATE(order_date) as date, SUM(total) as total
    FROM orders
    WHERE status != 'cancelled' AND order_date BETWEEN ? AND ?
    GROUP BY DATE(order_date)
    ORDER BY date");
$dailySales->execute([$dateFrom, $dateTo]);
$dailySales = $dailySales->fetchAll();

$pageTitle = 'Statistiques';
$currentPage = 'statistics';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-chart-bar"></i> Statistiques</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Statistiques</span>
        </nav>
    </div>
</div>

<!-- Date Filter -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="" class="filter-bar">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Du</label>
                <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Au</label>
                <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 24px;">
                <i class="fas fa-filter"></i> Filtrer
            </button>
        </form>
    </div>
</div>

<!-- Summary -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-shopping-cart"></i></div>
        <div class="stat-content">
            <h3><?php echo $salesStats['order_count'] ?: 0; ?></h3>
            <p>Commandes</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-content">
            <h3><?php echo formatMoney($salesStats['total_sales'] ?: 0); ?></h3>
            <p>Ventes Totales</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><i class="fas fa-chart-line"></i></div>
        <div class="stat-content">
            <h3><?php echo formatMoney($salesStats['avg_order'] ?: 0); ?></h3>
            <p>Panier Moyen</p>
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

<!-- Charts & Tables -->
<div class="dashboard-grid">
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-chart-area"></i> Évolution des Ventes</h3>
        </div>
        <div class="widget-body">
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-boxes"></i> Top Articles</h3>
        </div>
        <div class="widget-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Qté</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topArticles as $article): ?>
                        <tr>
                            <td><?php echo $article['name']; ?></td>
                            <td><?php echo $article['qty']; ?></td>
                            <td><?php echo formatMoney($article['total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-users"></i> Top Clients</h3>
        </div>
        <div class="widget-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Cmd</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topCustomers as $customer): ?>
                        <tr>
                            <td><?php echo $customer['company_name'] ?: $customer['contact_name']; ?></td>
                            <td><?php echo $customer['orders']; ?></td>
                            <td><?php echo formatMoney($customer['total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const dailyData = <?php echo json_encode($dailySales); ?>;
const labels = dailyData.map(d => d.date);
const data = dailyData.map(d => d.total);

new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Ventes (DT)',
            data: data,
            borderColor: 'rgba(26, 95, 122, 1)',
            backgroundColor: 'rgba(26, 95, 122, 0.1)',
            fill: true,
            tension: 0.4
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
