<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    requireManager();
    $id = intval($_GET['delete']);
    
    // Check if article is used in orders
    $stmt = $db->prepare("SELECT COUNT(*) FROM order_items WHERE article_id = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        setFlashMessage('error', 'Impossible de supprimer cet article car il est utilisé dans des commandes.');
    } else {
        $stmt = $db->prepare("DELETE FROM articles WHERE id = ?");
        if ($stmt->execute([$id])) {
            logActivity('Suppression article', 'article', $id);
            setFlashMessage('success', 'Article supprimé avec succès.');
        } else {
            setFlashMessage('error', 'Erreur lors de la suppression.');
        }
    }
    redirect('index.php');
}

// Get filter parameters
$categoryFilter = $_GET['category'] ?? '';
$stockFilter = $_GET['stock'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT a.*, c.name as category_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    WHERE 1=1";
$params = [];

if ($categoryFilter) {
    $sql .= " AND a.category_id = ?";
    $params[] = $categoryFilter;
}

if ($stockFilter === 'low') {
    $sql .= " AND a.quantity <= a.min_stock";
} elseif ($stockFilter === 'out') {
    $sql .= " AND a.quantity = 0";
}

if ($search) {
    $sql .= " AND (a.name LIKE ? OR a.code LIKE ? OR a.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY a.name ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Get categories for filter
$categories = $db->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();

$pageTitle = 'Gestion des Articles';
$currentPage = 'articles';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-boxes"></i> Articles</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Articles</span>
        </nav>
    </div>
    <?php if (isManager()): ?>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouvel Article
    </a>
    <?php endif; ?>
</div>

<!-- Filter Bar -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="" class="filter-bar">
            <div class="form-group" style="margin-bottom: 0;">
                <input type="text" name="search" class="form-control" placeholder="Rechercher..." 
                       value="<?php echo $search; ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <select name="category" class="form-control">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo $cat['name']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <select name="stock" class="form-control">
                    <option value="">Tous les stocks</option>
                    <option value="low" <?php echo $stockFilter === 'low' ? 'selected' : ''; ?>>Stock faible</option>
                    <option value="out" <?php echo $stockFilter === 'out' ? 'selected' : ''; ?>>Rupture de stock</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Filtrer
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Réinitialiser
            </a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Liste des Articles</h3>
        <span class="badge badge-primary"><?php echo count($articles); ?> article(s)</span>
    </div>
    <div class="card-body">
        <?php if (count($articles) > 0): ?>
        <div class="table-responsive">
            <table class="data-table" id="articlesTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Prix Achat</th>
                        <th>Prix Vente</th>
                        <th>Stock</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                    <tr>
                        <td>
                            <?php if ($article['image']): ?>
                            <img src="../../uploads/<?php echo $article['image']; ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                            <div style="width: 50px; height: 50px; background: var(--gray-200); border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-image" style="color: var(--gray-400);"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo $article['code']; ?></strong></td>
                        <td><?php echo $article['name']; ?></td>
                        <td><?php echo $article['category_name'] ?: '-'; ?></td>
                        <td><?php echo formatMoney($article['purchase_price']); ?></td>
                        <td><?php echo formatMoney($article['sale_price']); ?></td>
                        <td>
                            <?php if ($article['quantity'] <= $article['min_stock']): ?>
                            <span class="badge badge-danger" title="Stock faible (Min: <?php echo $article['min_stock']; ?>)">
                                <?php echo $article['quantity']; ?> <?php echo $article['unit']; ?>
                            </span>
                            <?php else: ?>
                            <span class="badge badge-success">
                                <?php echo $article['quantity']; ?> <?php echo $article['unit']; ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td><span class="status status-<?php echo $article['status']; ?>"><?php echo $article['status'] === 'active' ? 'Actif' : 'Inactif'; ?></span></td>
                        <td class="actions">
                            <a href="view.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-info btn-icon" data-tooltip="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (isManager()): ?>
                            <a href="edit.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-warning btn-icon" data-tooltip="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $article['id']; ?>" class="btn btn-sm btn-danger btn-icon" data-confirm="Êtes-vous sûr de vouloir supprimer cet article?" data-tooltip="Supprimer">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-boxes"></i>
            <h3>Aucun article trouvé</h3>
            <p>Aucun article ne correspond à vos critères de recherche</p>
            <?php if (isManager()): ?>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvel Article
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
