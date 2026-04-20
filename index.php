<?php
require_once 'includes/config.php';
requireLogin();

$db = getDB();

// Get statistics
$stats = [
    'total_orders' => $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
    'total_sales' => $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn(),
    'total_customers' => $db->query("SELECT COUNT(*) FROM customers WHERE status = 'active'")->fetchColumn(),
    'total_articles' => $db->query("SELECT COUNT(*) FROM articles WHERE status = 'active'")->fetchColumn(),
    'low_stock' => $db->query("SELECT COUNT(*) FROM articles WHERE quantity <= min_stock AND status = 'active'")->fetchColumn(),
    'pending_orders' => $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'pending_payments' => $db->query("SELECT COALESCE(SUM(total - paid_amount), 0) FROM orders WHERE payment_status != 'paid' AND status != 'cancelled'")->fetchColumn(),
];

// Get recent orders
$recentOrders = $db->query("SELECT o.*, c.company_name, c.contact_name 
    FROM orders o 
    LEFT JOIN customers c ON o.customer_id = c.id 
    ORDER BY o.created_at DESC 
    LIMIT 5")->fetchAll();

// Get recent customers
$recentCustomers = $db->query("SELECT * FROM customers ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Get low stock articles
$lowStockArticles = $db->query("SELECT a.*, c.name as category_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    WHERE a.quantity <= a.min_stock AND a.status = 'active'
    ORDER BY a.quantity ASC 
    LIMIT 5")->fetchAll();

// Get monthly sales data for chart
$monthlySales = $db->query("SELECT 
    MONTH(created_at) as month,
    SUM(total) as total
    FROM orders 
    WHERE status != 'cancelled' AND YEAR(created_at) = YEAR(CURDATE())
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)")->fetchAll();

$pageTitle = 'Tableau de Bord';
$currentPage = 'dashboard';
require_once 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-tachometer-alt"></i> Tableau de Bord</h1>
        <nav class="breadcrumb">
            <a href="index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Tableau de Bord</span>
        </nav>
    </div>
    <div class="quick-actions">
        <a href="modules/orders/create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Commande
        </a>
        <a href="modules/customers/create.php" class="btn btn-success">
            <i class="fas fa-user-plus"></i> Nouveau Client
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['total_orders']; ?></h3>
            <p>Commandes Aujourd'hui</p>
            <small><?php echo $stats['pending_orders']; ?> en attente</small>
        </div>
    </div>
    <?php if (isManager()): ?>
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo formatMoney($stats['total_sales']); ?></h3>
            <p>Ventes du Mois</p>
            <small>Chiffre d'affaires</small>
        </div>
    </div>
    <?php endif; ?>
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['total_customers']; ?></h3>
            <p>Clients Actifs</p>
            <small>Base clients totale</small>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-boxes"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['total_articles']; ?></h3>
            <p>Articles en Stock</p>
            <small><?php echo $stats['low_stock']; ?> en rupture</small>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon danger">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo formatMoney($stats['pending_payments']); ?></h3>
            <p>Paiements en Attente</p>
            <small>Créances clients</small>
        </div>
    </div>
</div>

<!-- Dashboard Grid -->
<div class="dashboard-grid">
    <!-- Recent Orders -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-shopping-cart"></i> Commandes Récentes</h3>
            <a href="modules/orders/index.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
        </div>
        <div class="widget-body">
            <?php if (count($recentOrders) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>N° Commande</th>
                            <th>Client</th>
                            <th>Total</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><a href="modules/orders/view.php?id=<?php echo $order['id']; ?>"><strong>#<?php echo $order['order_number']; ?></strong></a></td>
                            <td><?php echo $order['company_name'] ?: $order['contact_name']; ?></td>
                            <td><?php echo formatMoney($order['total']); ?></td>
                            <td><span class="status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <h3>Aucune commande récente</h3>
                <p>Les commandes récentes apparaîtront ici</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Customers -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-users"></i> Nouveaux Clients</h3>
            <a href="modules/customers/index.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
        </div>
        <div class="widget-body">
            <?php if (count($recentCustomers) > 0): ?>
            <div class="recent-list">
                <?php foreach ($recentCustomers as $customer): ?>
                <div class="recent-item">
                    <div class="recent-item-icon" style="background: var(--primary-color); color: white;">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="recent-item-content">
                        <h4><?php echo $customer['company_name'] ?: $customer['contact_name']; ?></h4>
                        <p><?php echo $customer['phone']; ?> • <?php echo $customer['city']; ?></p>
                    </div>
                    <small><?php echo formatDate($customer['created_at']); ?></small>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>Aucun client récent</h3>
                <p>Les nouveaux clients apparaîtront ici</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-exclamation-triangle"></i> Alerte Stock Faible</h3>
            <a href="modules/articles/index.php?filter=low_stock" class="btn btn-sm btn-outline-danger">Voir tout</a>
        </div>
        <div class="widget-body">
            <?php if (count($lowStockArticles) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Catégorie</th>
                            <th>Stock</th>
                            <th>Min</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lowStockArticles as $article): ?>
                        <tr>
                            <td><a href="modules/articles/view.php?id=<?php echo $article['id']; ?>"><strong><?php echo $article['name']; ?></strong></a></td>
                            <td><?php echo $article['category_name']; ?></td>
                            <td><span class="badge badge-danger"><?php echo $article['quantity']; ?></span></td>
                            <td><?php echo $article['min_stock']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-check-circle" style="color: var(--success-color);"></i>
                <h3>Tous les stocks sont OK</h3>
                <p>Aucun article en dessous du seuil minimum</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Monthly Sales Chart -->
     <?php if (isManager()): ?>
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-chart-bar"></i> Ventes Mensuelles</h3>
            <a href="modules/statistics/index.php" class="btn btn-sm btn-outline-primary">Détails</a>
        </div>
        <div class="widget-body">
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
const monthlyData = <?php echo json_encode($monthlySales); ?>;

const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
const salesData = new Array(12).fill(0);

monthlyData.forEach(item => {
    salesData[item.month - 1] = parseFloat(item.total);
});

new Chart(salesCtx, {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Ventes (DT)',
            data: salesData,
            backgroundColor: 'rgba(26, 95, 122, 0.8)',
            borderColor: 'rgba(26, 95, 122, 1)',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString() + ' DT';
                    }
                }
            }
        }
    }
});
</script>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>
