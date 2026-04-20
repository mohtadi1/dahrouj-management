<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    redirect('index.php');
}

// Get category with parent info
$stmt = $db->prepare("SELECT c.*, p.name as parent_name 
    FROM categories c 
    LEFT JOIN categories p ON c.parent_id = p.id 
    WHERE c.id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    setFlashMessage('error', 'Catégorie non trouvée.');
    redirect('index.php');
}

// Get articles in this category
$articles = $db->prepare("SELECT * FROM articles WHERE category_id = ? ORDER BY name ASC");
$articles->execute([$id]);
$articles = $articles->fetchAll();

// Get subcategories
$subcategories = $db->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY name ASC");
$subcategories->execute([$id]);
$subcategories = $subcategories->fetchAll();

$pageTitle = 'Détails Catégorie';
$currentPage = 'categories';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-tags"></i> <?php echo $category['name']; ?></h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Catégories</a>
            <i class="fas fa-chevron-right"></i>
            <span>Détails</span>
        </nav>
    </div>
    <div class="quick-actions">
        <?php if (isManager()): ?>
        <a href="edit.php?id=<?php echo $category['id']; ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Modifier
        </a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Category Info -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-info-circle"></i> Informations</h3>
        </div>
        <div class="widget-body">
            <table class="data-table">
                <tr>
                    <td><strong>ID:</strong></td>
                    <td><?php echo $category['id']; ?></td>
                </tr>
                <tr>
                    <td><strong>Nom:</strong></td>
                    <td><?php echo $category['name']; ?></td>
                </tr>
                <tr>
                    <td><strong>Description:</strong></td>
                    <td><?php echo $category['description'] ?: '-'; ?></td>
                </tr>
                <tr>
                    <td><strong>Catégorie Parent:</strong></td>
                    <td><?php echo $category['parent_name'] ?: '-'; ?></td>
                </tr>
                <tr>
                    <td><strong>Statut:</strong></td>
                    <td><span class="status status-<?php echo $category['status']; ?>"><?php echo $category['status'] === 'active' ? 'Actif' : 'Inactif'; ?></span></td>
                </tr>
                <tr>
                    <td><strong>Créé le:</strong></td>
                    <td><?php echo formatDateTime($category['created_at']); ?></td>
                </tr>
                <tr>
                    <td><strong>Mis à jour le:</strong></td>
                    <td><?php echo formatDateTime($category['updated_at']); ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-chart-pie"></i> Statistiques</h3>
        </div>
        <div class="widget-body">
            <div class="stats-grid" style="grid-template-columns: 1fr 1fr;">
                <div class="stat-card" style="padding: 15px;">
                    <div class="stat-icon primary" style="width: 45px; height: 45px; font-size: 18px;">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3 style="font-size: 20px;"><?php echo count($articles); ?></h3>
                        <p style="font-size: 12px;">Articles</p>
                    </div>
                </div>
                <div class="stat-card" style="padding: 15px;">
                    <div class="stat-icon info" style="width: 45px; height: 45px; font-size: 18px;">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="stat-content">
                        <h3 style="font-size: 20px;"><?php echo count($subcategories); ?></h3>
                        <p style="font-size: 12px;">Sous-catégories</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Articles in Category -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-boxes"></i> Articles dans cette Catégorie</h3>
        <?php if (isManager()): ?>
        <a href="../articles/create.php?category_id=<?php echo $category['id']; ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Ajouter un Article
        </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (count($articles) > 0): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Prix Vente</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                    <tr>
                        <td><?php echo $article['code']; ?></td>
                        <td><?php echo $article['name']; ?></td>
                        <td><?php echo formatMoney($article['sale_price']); ?></td>
                        <td>
                            <?php if ($article['quantity'] <= $article['min_stock']): ?>
                            <span class="badge badge-danger"><?php echo $article['quantity']; ?></span>
                            <?php else: ?>
                            <span class="badge badge-success"><?php echo $article['quantity']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <a href="../articles/view.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-info btn-icon">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state" style="padding: 30px;">
            <i class="fas fa-boxes"></i>
            <h3>Aucun article</h3>
            <p>Cette catégorie ne contient aucun article</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
