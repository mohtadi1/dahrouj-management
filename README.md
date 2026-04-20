# Société Dahrouj Import Textile - Système de Gestion

Un système de gestion complet pour la société Dahrouj Import Textile, développé en HTML, CSS, JavaScript et PHP avec MySQL.

## 🚀 Fonctionnalités

### Modules Principaux

1. **Gestion des Catégories**
   - Création, modification, suppression des catégories
   - Hiérarchie de catégories (parent/enfant)
   - Association avec les articles

2. **Gestion des Articles**
   - Fiches articles complètes (code, nom, description, prix, stock)
   - Gestion des images
   - Alertes de stock faible
   - Historique des commandes par article

3. **Gestion des Clients**
   - Fiches clients détaillées
   - Historique des commandes
   - Suivi des paiements
   - Limite de crédit

4. **Gestion des Commandes**
   - Création de commandes avec articles multiples
   - Calcul automatique des totaux (sous-total, remise, TVA)
   - Suivi des statuts (en attente, confirmée, en traitement, expédiée, livrée, annulée)
   - Gestion des paiements

5. **Gestion des Fournisseurs**
   - Fiches fournisseurs
   - Suivi des achats
   - Gestion des partenariats

6. **Gestion des Utilisateurs**
   - Rôles (Administrateur, Manager, Employé)
   - Gestion des permissions
   - Historique des activités

7. **Comptabilité**
   - Suivi des revenus et dépenses
   - Plan comptable
   - Transactions
   - Bilan et résultat

8. **Statistiques et Rapports**
   - Tableau de bord avec indicateurs clés
   - Graphiques de ventes
   - Top articles et clients
   - Rapports personnalisables

## 📋 Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache, Nginx)
- Extension PDO PHP

## 🔧 Installation

### Étape 1: Télécharger le projet
```bash
cd /var/www/html
git clone [url-du-projet] dahrouj-management
cd dahrouj-management
```

### Étape 2: Créer la base de données
```bash
mysql -u root -p
```

Dans MySQL:
```sql
CREATE DATABASE dahrouj_textile CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

Importer le schéma:
```bash
mysql -u root -p dahrouj_textile < database.sql
```

### Étape 3: Configurer la connexion à la base de données
Modifier le fichier `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');
define('DB_NAME', 'dahrouj_textile');
```

### Étape 4: Configurer les permissions
```bash
chmod -R 755 /var/www/html/dahrouj-management
chmod -R 777 /var/www/html/dahrouj-management/uploads
```

### Étape 5: Accéder à l'application
Ouvrir un navigateur et accéder à:
```
http://localhost/dahrouj-management
```

## 🔐 Identifiants par défaut

- **Nom d'utilisateur:** admin
- **Mot de passe:** admin123

**⚠️ Important:** Changez le mot de passe par défaut après la première connexion!

## 📁 Structure du projet

```
dahrouj-management/
├── assets/
│   ├── css/
│   │   └── style.css          # Styles principaux
│   ├── js/
│   │   └── main.js            # JavaScript principal
│   └── images/
│       └── logo.png           # Logo de la société
├── includes/
│   ├── config.php             # Configuration et fonctions
│   ├── header.php             # En-tête commun
│   └── footer.php             # Pied de page commun
├── modules/
│   ├── articles/              # Gestion des articles
│   ├── categories/            # Gestion des catégories
│   ├── customers/             # Gestion des clients
│   ├── orders/                # Gestion des commandes
│   ├── partners/              # Gestion des fournisseurs
│   ├── users/                 # Gestion des utilisateurs
│   ├── accounting/            # Comptabilité
│   └── statistics/            # Statistiques
├── uploads/                   # Dossier des images uploadées
├── database.sql               # Schéma de la base de données
├── index.php                  # Tableau de bord
├── login.php                  # Page de connexion
├── logout.php                 # Déconnexion
└── README.md                  # Ce fichier
```

## 👥 Rôles et Permissions

| Fonctionnalité | Administrateur | Manager | Employé |
|----------------|---------------|---------|---------|
| Tableau de bord | ✅ | ✅ | ✅ |
| Commandes | ✅ | ✅ | ✅ |
| Clients | ✅ | ✅ | ✅ |
| Articles | ✅ | ✅ | ✅ |
| Catégories | ✅ | ✅ | ✅ |
| Fournisseurs | ✅ | ✅ | ✅ |
| Utilisateurs | ✅ | ❌ | ❌ |
| Comptabilité | ✅ | ✅ | ❌ |
| Statistiques | ✅ | ✅ | ✅ |

## 🎨 Personnalisation

### Modifier le logo
Remplacer le fichier `assets/images/logo.png` par votre propre logo.

### Modifier les couleurs
Éditer le fichier `assets/css/style.css` et modifier les variables CSS:
```css
:root {
    --primary-color: #1a5f7a;
    --secondary-color: #c84b31;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --info-color: #17a2b8;
}
```

### Modifier la devise
Dans `includes/config.php`:
```php
define('APP_CURRENCY', 'TND');
define('APP_CURRENCY_SYMBOL', 'DT');
```

### Modifier le taux de TVA
Dans `includes/config.php`:
```php
define('APP_TAX_RATE', 19); // 19% pour la Tunisie
```

## 🔒 Sécurité

- Mots de passe hashés avec bcrypt
- Protection contre les injections SQL (PDO prepared statements)
- Protection XSS avec htmlspecialchars
- CSRF token pour les formulaires
- Gestion des sessions sécurisée
- Vérification des permissions par rôle

## 📝 Notes importantes

1. **Sauvegardes régulières:** Effectuez des sauvegardes régulières de la base de données.

2. **Mises à jour:** Gardez PHP et MySQL à jour pour des raisons de sécurité.

3. **Logs:** Les activités des utilisateurs sont enregistrées dans la table `activity_log`.

4. **Stock:** La mise à jour du stock se fait automatiquement lors de la création d'une commande.

## 🐛 Dépannage

### Problème de connexion à la base de données
Vérifiez les paramètres dans `includes/config.php` et assurez-vous que MySQL est en cours d'exécution.

### Erreur 500 (Internal Server Error)
Vérifiez les permissions des dossiers et fichiers.

### Images non uploadées
Vérifiez que le dossier `uploads/` existe et a les permissions d'écriture (777).

### Caractères spéciaux incorrects
Vérifiez que la base de données utilise utf8mb4.

## 📞 Support

Pour toute question ou problème, contactez:
- Email: support@dahrouj.tn
- Téléphone: +216 XX XXX XXX

## 📄 Licence

Ce projet est propriétaire de la Société Dahrouj Import Textile.
Tous droits réservés © <?php echo date('Y'); ?>

---

**Développé avec ❤️ pour la Société Dahrouj Import Textile**
