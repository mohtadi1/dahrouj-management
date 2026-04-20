<?php 
// Calculate base path based on current file location
$currentDir = dirname($_SERVER['SCRIPT_NAME']);
$basePath = '';
if (strpos($currentDir, '/modules/') !== false) {
    $basePath = '../../';
} elseif (strpos($currentDir, '/modules') !== false) {
    $basePath = '../';
}

require_once $basePath . 'includes/config.php'; 
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php if (isLoggedIn()): ?>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="<?php echo $basePath; ?>assets/images/logo.png" alt="<?php echo APP_NAME; ?>" class="logo">
            <h2 class="company-name">DAHROUJ IMPORT TEXTILE</h2>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de Bord</span>
                    </a>
                </li>
                
                <li class="menu-title">Gestion Commerciale</li>
                
                <li class="<?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>modules/orders/index.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Commandes</span>
                        <?php 
                        $db = getDB();
                        $pendingOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
                        if ($pendingOrders > 0): 
                        ?>
                        <span class="badge"><?php echo $pendingOrders; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <li class="<?php echo $currentPage === 'customers' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>modules/customers/index.php">
                        <i class="fas fa-users"></i>
                        <span>Clients</span>
                    </a>
                </li>
                
                <li class="<?php echo $currentPage === 'articles' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>modules/articles/index.php">
                        <i class="fas fa-boxes"></i>
                        <span>Articles</span>
                    </a>
                </li>
                
                <li class="<?php echo $currentPage === 'categories' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>modules/categories/index.php">
                        <i class="fas fa-tags"></i>
                        <span>Catégories</span>
                    </a>
                </li>
                
                <li class="menu-title">Achats & Stock</li>
                
                <li class="<?php echo $currentPage === 'purchases' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>modules/purchases/index.php">
                        <i class="fas fa-truck-loading"></i>
                        <span>Achats</span>
                    </a>
                </li>
                
                <li class="<?php echo $currentPage === 'partners' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>modules/partners/index.php">
                        <i class="fas fa-handshake"></i>
                        <span>Fournisseurs</span>
                    </a>
                </li>
                <?php if (isManager()): ?>
                <li class="menu-title">Finance & Comptabilité</li>
                
                <li class="<?php echo $currentPage === 'accounting' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>modules/accounting/index.php">
                        <i class="fas fa-calculator"></i>
                        <span>Comptabilité</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (isManager()): ?>
                <li class="<?php echo $currentPage === 'expenses' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>modules/accounting/expenses.php">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Dépenses</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (isManager()): ?>
                <li class="menu-title">Rapports & Administration</li>
                <li class="<?php echo $currentPage === 'statistics' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>modules/statistics/index.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Statistiques</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (isManager()): ?>
                <li class="<?php echo $currentPage === 'reports' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>modules/reports/index.php">
                        <i class="fas fa-file-alt"></i>
                        <span>Rapports</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (isAdmin()): ?>
                <li class="<?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>modules/users/index.php">
                        <i class="fas fa-user-cog"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Top Header -->
        <header class="top-header">
             <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="header-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Rechercher..." id="globalSearch">
            </div>
            
            <div class="header-actions">
                <div class="user-dropdown">
                    <button class="user-btn" id="userBtn">
                        <?php
                        // Récupérer la photo depuis la session (définie lors de l'upload ou du login)
                        $profilePhoto = $_SESSION['profile_photo'] ?? '';
                        // Si le chemin est absolu (commence par /), on le passe directement.
                        // Sinon, on utilise $basePath (fallback)
                        if (!empty($profilePhoto) && file_exists($_SERVER['DOCUMENT_ROOT'] . $profilePhoto)) {
                            $avatarSrc = $profilePhoto;
                        } else {
                            $avatarSrc = $basePath . 'assets/images/default-avatar.png';
                        }
                        ?>
                        <img src="<?php echo $avatarSrc; ?>" alt="User" class="avatar" style="width:32px; height:32px; border-radius:50%; object-fit:cover;" onerror="this.src='<?php echo $basePath; ?>assets/images/default-avatar.png'">
                        <span><?php echo $_SESSION['user_name'] ?? 'Utilisateur'; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>   
                    <div class="dropdown-menu" id="userMenu">
                        <a href="<?php echo $basePath; ?>modules/users/profile.php"><i class="fas fa-user"></i> Mon Profil</a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo $basePath; ?>logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="main-content">
            <?php 
            $flash = getFlashMessage();
            if ($flash): 
            ?>
            <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
                <?php echo $flash['message']; ?>
                <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
            </div>
            <?php endif; ?>
<?php endif; ?>
