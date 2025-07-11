# Guide d'Installation - MedZone

## Prérequis

Avant d'installer MedZone, assurez-vous d'avoir les éléments suivants :

- **PHP 8.0 ou supérieur**
- **MySQL 5.7 ou supérieur** (ou MariaDB 10.2+)
- **Serveur web** (Apache ou Nginx)
- **Composer** (optionnel, pour les dépendances)

## Étapes d'installation

### 1. Téléchargement et extraction

```bash
# Cloner le projet (si vous utilisez Git)
git clone [URL_DU_REPO] medzone
cd medzone

# Ou télécharger et extraire l'archive ZIP
```

### 2. Configuration de la base de données

1. **Créer une base de données MySQL :**
   ```sql
   CREATE DATABASE medzone CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Importer le schéma :**
   ```bash
   mysql -u root -p medzone < database/schema.sql
   ```

3. **Configurer la connexion :**
   - Ouvrir `config/database.php`
   - Modifier les paramètres de connexion :
     ```php
     $host = 'localhost';
     $dbname = 'medzone';
     $username = 'votre_utilisateur';
     $password = 'votre_mot_de_passe';
     ```

### 3. Configuration du serveur web

#### Apache
1. **Activer le module rewrite :**
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. **Configurer le DocumentRoot :**
   - Pointer le DocumentRoot vers le dossier du projet
   - S'assurer que le fichier `.htaccess` est présent

#### Nginx
```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    root /chemin/vers/medzone;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 4. Permissions des dossiers

```bash
# Donner les permissions d'écriture aux dossiers nécessaires
chmod 755 uploads/
chmod 755 assets/images/
chmod 644 .htaccess
```

### 5. Configuration PHP

Assurez-vous que les extensions PHP suivantes sont activées :
- `pdo_mysql`
- `gd` (pour le traitement d'images)
- `mbstring`
- `json`

### 6. Test de l'installation

1. **Accéder à l'application :**
   - Ouvrir votre navigateur
   - Aller sur `http://votre-domaine.com`

2. **Vérifier les fonctionnalités :**
   - Page d'accueil
   - Inscription/Connexion
   - Catalogue de produits
   - Recherche

## Configuration avancée

### Variables d'environnement

Créer un fichier `.env` à la racine du projet :

```env
DB_HOST=localhost
DB_NAME=medzone
DB_USER=root
DB_PASS=

APP_URL=http://localhost
APP_NAME=MedZone
APP_ENV=production
```

### Sécurité

1. **Changer les mots de passe par défaut**
2. **Configurer HTTPS**
3. **Limiter les tentatives de connexion**
4. **Sauvegarder régulièrement la base de données**

### Performance

1. **Activer le cache PHP OPcache**
2. **Configurer le cache MySQL**
3. **Optimiser les images**
4. **Utiliser un CDN pour les assets statiques**

## Dépannage

### Erreurs courantes

1. **Erreur de connexion à la base de données :**
   - Vérifier les paramètres dans `config/database.php`
   - S'assurer que MySQL est démarré

2. **Erreur 500 :**
   - Vérifier les logs d'erreur Apache/Nginx
   - S'assurer que les permissions sont correctes

3. **Images non affichées :**
   - Vérifier les permissions du dossier `uploads/`
   - S'assurer que le chemin est correct

### Logs

- **Apache :** `/var/log/apache2/error.log`
- **Nginx :** `/var/log/nginx/error.log`
- **PHP :** `/var/log/php/error.log`

## Mise à jour

1. **Sauvegarder la base de données :**
   ```bash
   mysqldump -u root -p medzone > backup.sql
   ```

2. **Remplacer les fichiers**
3. **Exécuter les migrations de base de données**
4. **Tester l'application**

## Support

Pour toute question ou problème :
- Consulter la documentation
- Vérifier les logs d'erreur
- Contacter l'équipe de développement

---

**MedZone** - Votre pharmacie en ligne de confiance 