<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    requireAdmin();
    $id = intval($_GET['delete']);
    
    // Check if category has articles
    $stmt = $db->prepare("SELECT COUNT(*) FROM articles WHERE category_id = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        setFlashMessage('error', 'Impossible de supprimer cette catégorie car elle contient des articles.');
    } else {
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$id])) {
            logActivity('Suppression catégorie', 'category', $id);
            setFlashMessage('success', 'Catégorie supprimée avec succès.');
        } else {
            setFlashMessage('error', 'Erreur lors de la suppression.');
        }
    }
    redirect('index.php');
}

// Get all categories with article count
$categories = $db->query("SELECT c.*, COUNT(a.id) as article_count, p.name as parent_name 
    FROM categories c 
    LEFT JOIN articles a ON c.id = a.category_id 
    LEFT JOIN categories p ON c.parent_id = p.id
    GROUP BY c.id 
    ORDER BY c.name ASC")->fetchAll();

$pageTitle = 'Gestion des Catégories';
$currentPage = 'categories';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-tags"></i> Catégories</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Catégories</span>
        </nav>
    </div>
    <?php if (isManager()): ?>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouvelle Catégorie
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Liste des Catégories</h3>
        <div class="header-search" style="max-width: 300px;">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" placeholder="Rechercher..." data-search="#categoriesTable">
        </div>
    </div>
    <div class="card-body">
        <?php if (count($categories) > 0): ?>
        <div class="table-responsive">
            <table class="data-table" id="categoriesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Catégorie Parent</th>
                        <th>Articles</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo $category['id']; ?></td>
                        <td><strong><?php echo $category['name']; ?></strong></td>
                        <td><?php echo $category['description'] ?: '-'; ?></td>
                        <td><?php echo $category['parent_name'] ?: '-'; ?></td>
                        <td><span class="badge badge-primary"><?php echo $category['article_count']; ?></span></td>
                        <td><span class="status status-<?php echo $category['status']; ?>"><?php echo $category['status'] === 'active' ? 'Actif' : 'Inactif'; ?></span></td>
                        <td class="actions">
                            <a href="view.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-info btn-icon" data-tooltip="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (isManager()): ?>
                            <a href="edit.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-warning btn-icon" data-tooltip="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($category['article_count'] == 0): ?>
                            <a href="?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger btn-icon" data-confirm="Êtes-vous sûr de vouloir supprimer cette catégorie?" data-tooltip="Supprimer">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-tags"></i>
            <h3>Aucune catégorie</h3>
            <p>Commencez par créer une nouvelle catégorie</p>
            <?php if (isManager()): ?>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Catégorie
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
