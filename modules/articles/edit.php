<?php
require_once '../../includes/config.php';
requireManager();

$db = getDB();
$error = '';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    redirect('index.php');
}

// Get article
$stmt = $db->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    setFlashMessage('error', 'Article non trouvé.');
    redirect('index.php');
}

// Get categories
$categories = $db->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = sanitize($_POST['code'] ?? '');
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $purchase_price = floatval($_POST['purchase_price'] ?? 0);
    $sale_price = floatval($_POST['sale_price'] ?? 0);
    $wholesale_price = floatval($_POST['wholesale_price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $min_stock = intval($_POST['min_stock'] ?? 10);
    $unit = sanitize($_POST['unit'] ?? 'piece');
    $status = $_POST['status'] ?? 'active';
    
    // Handle image upload
    $image = $article['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['image']['type'], $allowedTypes)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                // Delete old image
                if ($image && file_exists($uploadDir . $image)) {
                    unlink($uploadDir . $image);
                }
                $image = $fileName;
            }
        }
    }
    
    if (empty($name)) {
        $error = 'Le nom de l\'article est obligatoire.';
    } else {
        $stmt = $db->prepare("UPDATE articles SET code = ?, name = ?, description = ?, category_id = ?, purchase_price = ?, sale_price = ?, wholesale_price = ?, quantity = ?, min_stock = ?, unit = ?, image = ?, status = ? WHERE id = ?");
        if ($stmt->execute([$code, $name, $description, $category_id, $purchase_price, $sale_price, $wholesale_price, $quantity, $min_stock, $unit, $image, $status, $id])) {
            logActivity('Modification article', 'article', $id);
            setFlashMessage('success', 'Article mis à jour avec succès.');
            redirect('index.php');
        } else {
            $error = 'Erreur lors de la mise à jour de l\'article.';
        }
    }
}

$pageTitle = 'Modifier Article';
$currentPage = 'articles';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-boxes"></i> Modifier Article</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Articles</a>
            <i class="fas fa-chevron-right"></i>
            <span>Modifier</span>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informations de l'Article</h3>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data" data-validate>
            <div class="form-row">
                <div class="form-group">
                    <label for="code">Code Article</label>
                    <input type="text" id="code" name="code" class="form-control" 
                           value="<?php echo $article['code']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="name">Nom de l'article <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required 
                           value="<?php echo $article['name']; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?php echo $article['description']; ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Catégorie</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $article['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="unit">Unité</label>
                    <select id="unit" name="unit" class="form-control">
                        <option value="piece" <?php echo $article['unit'] === 'piece' ? 'selected' : ''; ?>>Pièce</option>
                        <option value="kg" <?php echo $article['unit'] === 'kg' ? 'selected' : ''; ?>>Kilogramme</option>
                        <option value="meter" <?php echo $article['unit'] === 'meter' ? 'selected' : ''; ?>>Mètre</option>
                        <option value="roll" <?php echo $article['unit'] === 'roll' ? 'selected' : ''; ?>>Rouleau</option>
                        <option value="box" <?php echo $article['unit'] === 'box' ? 'selected' : ''; ?>>Boîte</option>
                        <option value="pack" <?php echo $article['unit'] === 'pack' ? 'selected' : ''; ?>>Paquet</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="purchase_price">Prix d'achat (DT)</label>
                    <input type="number" id="purchase_price" name="purchase_price" class="form-control" 
                           step="0.001" min="0" value="<?php echo $article['purchase_price']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="sale_price">Prix de vente (DT)</label>
                    <input type="number" id="sale_price" name="sale_price" class="form-control" 
                           step="0.001" min="0" value="<?php echo $article['sale_price']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="wholesale_price">Prix gros (DT)</label>
                    <input type="number" id="wholesale_price" name="wholesale_price" class="form-control" 
                           step="0.001" min="0" value="<?php echo $article['wholesale_price']; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="quantity">Quantité en stock</label>
                    <input type="number" id="quantity" name="quantity" class="form-control" 
                           min="0" value="<?php echo $article['quantity']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="min_stock">Stock minimum</label>
                    <input type="number" id="min_stock" name="min_stock" class="form-control" 
                           min="0" value="<?php echo $article['min_stock']; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="image">Image</label>
                    <?php if ($article['image']): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="../../uploads/<?php echo $article['image']; ?>" alt="" style="max-width: 150px; border-radius: 4px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    <small class="text-muted">Formats acceptés: JPG, PNG, GIF (max 2MB)</small>
                </div>
                
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active" <?php echo $article['status'] === 'active' ? 'selected' : ''; ?>>Actif</option>
                        <option value="inactive" <?php echo $article['status'] === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Mettre à jour
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
