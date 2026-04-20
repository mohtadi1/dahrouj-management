<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    redirect('index.php');
}

// Get article with category
$stmt = $db->prepare("SELECT a.*, c.name as category_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    WHERE a.id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    setFlashMessage('error', 'Article non trouvé.');
    redirect('index.php');
}

// Get order history
$orderHistory = $db->prepare("SELECT oi.*, o.order_number, o.order_date, o.status, c.company_name 
    FROM order_items oi 
    JOIN orders o ON oi.order_id = o.id 
    LEFT JOIN customers c ON o.customer_id = c.id 
    WHERE oi.article_id = ? 
    ORDER BY o.order_date DESC 
    LIMIT 10");
$orderHistory->execute([$id]);
$orderHistory = $orderHistory->fetchAll();

$pageTitle = 'Détails Article';
$currentPage = 'articles';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-boxes"></i> <?php echo $article['name']; ?></h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Articles</a>
            <i class="fas fa-chevron-right"></i>
            <span>Détails</span>
        </nav>
    </div>
    <div class="quick-actions">
        <?php if (isManager()): ?>
        <a href="edit.php?id=<?php echo $article['id']; ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Modifier
        </a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Article Image & Basic Info -->
    <div class="widget">
        <div class="widget-body" style="text-align: center;">
            <?php if ($article['image']): ?>
            <img src="../../uploads/<?php echo $article['image']; ?>" alt="" style="max-width: 100%; max-height: 250px; border-radius: 8px; margin-bottom: 20px;">
            <?php else: ?>
            <div style="width: 100%; height: 200px; background: var(--gray-200); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <i class="fas fa-image" style="font-size: 64px; color: var(--gray-400);"></i>
            </div>
            <?php endif; ?>
            
            <h3 style="margin-bottom: 5px;"><?php echo $article['name']; ?></h3>
            <p style="color: var(--gray-600);"><?php echo $article['code']; ?></p>
            
            <div style="margin-top: 20px;">
                <span class="status status-<?php echo $article['status']; ?>" style="font-size: 14px;">
                    <?php echo $article['status'] === 'active' ? 'Actif' : 'Inactif'; ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Article Details -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-info-circle"></i> Informations</h3>
        </div>
        <div class="widget-body">
            <table class="data-table">
                <tr>
                    <td><strong>Catégorie:</strong></td>
                    <td><?php echo $article['category_name'] ?: '-'; ?></td>
                </tr>
                <tr>
                    <td><strong>Unité:</strong></td>
                    <td><?php echo $article['unit']; ?></td>
                </tr>
                <tr>
                    <td><strong>Prix d'achat:</strong></td>
                    <td><?php echo formatMoney($article['purchase_price']); ?></td>
                </tr>
                <tr>
                    <td><strong>Prix de vente:</strong></td>
                    <td><?php echo formatMoney($article['sale_price']); ?></td>
                </tr>
                <tr>
                    <td><strong>Prix gros:</strong></td>
                    <td><?php echo formatMoney($article['wholesale_price']); ?></td>
                </tr>
                <tr>
                    <td><strong>Stock actuel:</strong></td>
                    <td>
                        <?php if ($article['quantity'] <= $article['min_stock']): ?>
                        <span class="badge badge-danger"><?php echo $article['quantity']; ?> <?php echo $article['unit']; ?></span>
                        <?php else: ?>
                        <span class="badge badge-success"><?php echo $article['quantity']; ?> <?php echo $article['unit']; ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Stock minimum:</strong></td>
                    <td><?php echo $article['min_stock']; ?> <?php echo $article['unit']; ?></td>
                </tr>
                <tr>
                    <td><strong>Créé le:</strong></td>
                    <td><?php echo formatDateTime($article['created_at']); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- Description -->
<?php if ($article['description']): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-align-left"></i> Description</h3>
    </div>
    <div class="card-body">
        <p><?php echo nl2br($article['description']); ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Order History -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history"></i> Historique des Commandes</h3>
    </div>
    <div class="card-body">
        <?php if (count($orderHistory) > 0): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                        <th>Total</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderHistory as $item): ?>
                    <tr>
                        <td><a href="../orders/view.php?id=<?php echo $item['order_id']; ?>">#<?php echo $item['order_number']; ?></a></td>
                        <td><?php echo $item['company_name'] ?: '-'; ?></td>
                        <td><?php echo formatDate($item['order_date']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo formatMoney($item['unit_price']); ?></td>
                        <td><?php echo formatMoney($item['total']); ?></td>
                        <td><span class="status status-<?php echo $item['status']; ?>"><?php echo ucfirst($item['status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state" style="padding: 30px;">
            <i class="fas fa-shopping-cart"></i>
            <h3>Aucune commande</h3>
            <p>Cet article n'a pas encore été commandé</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
