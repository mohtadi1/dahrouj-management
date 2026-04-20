<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();

// Get report type
$reportType = $_GET['type'] ?? 'sales';
$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

$data = [];
$title = '';

switch ($reportType) {
    case 'sales':
        $title = 'Rapport des Ventes';
        $stmt = $db->prepare("SELECT o.*, c.company_name, c.contact_name 
            FROM orders o 
            LEFT JOIN customers c ON o.customer_id = c.id 
            WHERE o.status != 'cancelled' AND o.order_date BETWEEN ? AND ?
            ORDER BY o.order_date DESC");
        $stmt->execute([$dateFrom, $dateTo]);
        $data = $stmt->fetchAll();
        break;
        
    case 'purchases':
        $title = 'Rapport des Achats';
        $stmt = $db->prepare("SELECT p.*, pr.company_name 
            FROM purchases p 
            LEFT JOIN partners pr ON p.partner_id = pr.id 
            WHERE p.status != 'cancelled' AND p.purchase_date BETWEEN ? AND ?
            ORDER BY p.purchase_date DESC");
        $stmt->execute([$dateFrom, $dateTo]);
        $data = $stmt->fetchAll();
        break;
        
    case 'inventory':
        $title = 'Rapport des Stocks';
        $data = $db->query("SELECT a.*, c.name as category_name 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            ORDER BY a.quantity ASC")->fetchAll();
        break;
        
    case 'customers':
        $title = 'Rapport des Clients';
        $data = $db->query("SELECT c.*, 
            (SELECT COUNT(*) FROM orders WHERE customer_id = c.id) as order_count,
            (SELECT COALESCE(SUM(total), 0) FROM orders WHERE customer_id = c.id AND status != 'cancelled') as total
            FROM customers c 
            ORDER BY total DESC")->fetchAll();
        break;
}

$pageTitle = 'Rapports';
$currentPage = 'reports';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-file-alt"></i> Rapports</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Rapports</span>
        </nav>
    </div>
    <button class="btn btn-info" onclick="window.print()">
        <i class="fas fa-print"></i> Imprimer
    </button>
</div>

<!-- Report Type Selection -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="" class="filter-bar">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Type de rapport</label>
                <select name="type" class="form-control">
                    <option value="sales" <?php echo $reportType === 'sales' ? 'selected' : ''; ?>>Ventes</option>
                    <option value="purchases" <?php echo $reportType === 'purchases' ? 'selected' : ''; ?>>Achats</option>
                    <option value="inventory" <?php echo $reportType === 'inventory' ? 'selected' : ''; ?>>Stocks</option>
                    <option value="customers" <?php echo $reportType === 'customers' ? 'selected' : ''; ?>>Clients</option>
                </select>
            </div>
            <?php if ($reportType !== 'inventory' && $reportType !== 'customers'): ?>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Du</label>
                <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Au</label>
                <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary" style="margin-top: 24px;">
                <i class="fas fa-sync"></i> Générer
            </button>
        </form>
    </div>
</div>

<!-- Report Content -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?php echo $title; ?></h3>
        <span class="text-muted">Période: <?php echo formatDate($dateFrom); ?> - <?php echo formatDate($dateTo); ?></span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="reportTable">
                <thead>
                    <?php if ($reportType === 'sales'): ?>
                    <tr>
                        <th>N° Commande</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Total</th>
                        <th>Payé</th>
                        <th>Statut</th>
                    </tr>
                    <?php elseif ($reportType === 'purchases'): ?>
                    <tr>
                        <th>N° Achat</th>
                        <th>Date</th>
                        <th>Fournisseur</th>
                        <th>Total</th>
                        <th>Payé</th>
                        <th>Statut</th>
                    </tr>
                    <?php elseif ($reportType === 'inventory'): ?>
                    <tr>
                        <th>Code</th>
                        <th>Article</th>
                        <th>Catégorie</th>
                        <th>Stock</th>
                        <th>Min</th>
                        <th>Prix Vente</th>
                        <th>Valeur Stock</th>
                    </tr>
                    <?php elseif ($reportType === 'customers'): ?>
                    <tr>
                        <th>Client</th>
                        <th>Téléphone</th>
                        <th>Ville</th>
                        <th>Commandes</th>
                        <th>Total Achats</th>
                    </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach ($data as $row): 
                        if ($reportType === 'sales') {
                            $total += $row['total'];
                        } elseif ($reportType === 'purchases') {
                            $total += $row['total'];
                        } elseif ($reportType === 'inventory') {
                            $total += $row['quantity'] * $row['purchase_price'];
                        } elseif ($reportType === 'customers') {
                            $total += $row['total'];
                        }
                    ?>
                    <?php if ($reportType === 'sales'): ?>
                    <tr>
                        <td>#<?php echo $row['order_number']; ?></td>
                        <td><?php echo formatDate($row['order_date']); ?></td>
                        <td><?php echo $row['company_name'] ?: $row['contact_name']; ?></td>
                        <td><?php echo formatMoney($row['total']); ?></td>
                        <td><?php echo formatMoney($row['paid_amount']); ?></td>
                        <td><span class="status status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                    </tr>
                    <?php elseif ($reportType === 'purchases'): ?>
                    <tr>
                        <td>#<?php echo $row['purchase_number']; ?></td>
                        <td><?php echo formatDate($row['purchase_date']); ?></td>
                        <td><?php echo $row['company_name']; ?></td>
                        <td><?php echo formatMoney($row['total']); ?></td>
                        <td><?php echo formatMoney($row['paid_amount']); ?></td>
                        <td><span class="status status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                    </tr>
                    <?php elseif ($reportType === 'inventory'): ?>
                    <tr>
                        <td><?php echo $row['code']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['category_name'] ?: '-'; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo $row['min_stock']; ?></td>
                        <td><?php echo formatMoney($row['sale_price']); ?></td>
                        <td><?php echo formatMoney($row['quantity'] * $row['purchase_price']); ?></td>
                    </tr>
                    <?php elseif ($reportType === 'customers'): ?>
                    <tr>
                        <td><?php echo $row['company_name'] ?: $row['contact_name']; ?></td>
                        <td><?php echo $row['phone']; ?></td>
                        <td><?php echo $row['city'] ?: '-'; ?></td>
                        <td><?php echo $row['order_count']; ?></td>
                        <td><?php echo formatMoney($row['total']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot style="background: var(--gray-100); font-weight: bold;">
                    <tr>
                        <td colspan="<?php echo $reportType === 'inventory' ? 6 : ($reportType === 'customers' ? 4 : 3); ?>" style="text-align: right;">TOTAL:</td>
                        <td><?php echo formatMoney($total); ?></td>
                        <?php if ($reportType === 'sales' || $reportType === 'purchases'): ?>
                        <td colspan="2"></td>
                        <?php endif; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
