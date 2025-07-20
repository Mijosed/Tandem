# Tandem - Plateforme de Mise en Relation

## 🚀 Installation rapide

### Prérequis
- Docker & Docker Compose
- Node.js (pour le développement frontend)

### 1. Cloner le projet
```bash
git clone [votre-repo]
cd Tandem
```

### 2. Installation des dépendances frontend
```bash
cd frontend
npm install
cd ..
```

### 3. Lancer l'application
```bash
docker-compose up --build
```

## 📚 URLs de développement

- **Frontend** : http://localhost:3000
- **API Platform** : http://localhost:8000/api/docs.html  
- **Adminer** : http://localhost:8080
- **MailHog** (Test emails) : http://localhost:8025

## 🗄️ Base de données

Connexion à la base données via Adminer :
- **Système** : PostgreSQL
- **Serveur** : database 
- **User** : admin
- **Password** : admin

## 👥 Comptes de test

Après avoir chargé les fixtures, vous pouvez utiliser ces comptes pour tester l'application :

### Comptes principaux
- **Administrateur**
  - Email : `admin@tandem.com`
  - Mot de passe : `password123`
  - Rôle : ROLE_ADMIN

- **Utilisateur de test**
  - Email : `test@user.fr`
  - Mot de passe : `test1234`
  - Rôle : ROLE_USER

- **Recruteur**
  - Email : `recruiter@tandem.com`
  - Mot de passe : `password123`
  - Rôle : ROLE_RECRUITER

## ⚙️ Commandes utiles

Pour exécuter une commande dans un container :
```bash
docker compose exec [service] [commande]
```

Exemples :
```bash
# Charger les fixtures
docker compose exec backend bin/console hautelook:fixtures:load 

# Installer un package frontend
docker compose exec frontend npm install [package]
```

## 🛠️ Développement

Si vous avez des erreurs de modules manquants :
1. Vérifiez que `npm install` a été fait dans `/frontend`
2. Relancez `docker-compose up --build`


## � Configuration des emails

Le système envoie automatiquement des emails lors de la création de notifications.

### Configuration Gmail (Production)
1. Créer un mot de passe d'application Gmail
2. Configurer les variables dans `backend/.env` :
```bash
MAILER_DSN=gmail://votre-email@gmail.com:votre-mot-de-passe-app@default
FRONTEND_URL=https://votre-domaine.com
```

### Tests locaux avec MailHog
MailHog capture tous les emails pour les tests :
- Interface web : http://localhost:8025
- Les emails ne sont pas envoyés mais stockés localement

### Types d'emails automatiques
- **Interview** : Convocation avec date/heure
- **Information** : Updates importantes
- **Rappel** : Notifications de suivi
- **Candidature** : Statuts de candidature

## �🗃️ Initialisation de la base de données

### Première installation
```bash
docker-compose exec php php bin/console doctrine:database:create --if-not-exists
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec php php bin/console hautelook:fixtures:load --no-interaction
```

### En cas d'erreur de migration
Si vous rencontrez une erreur avec la migration `Version20250720203805` concernant une contrainte inexistante :

```bash
# Marquer la migration problématique comme exécutée
docker-compose exec php php bin/console doctrine:migrations:version DoctrineMigrations\\Version20250720203805 --add --no-interaction

# Puis continuer avec les autres migrations
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

### ✅ État actuel
Le système fonctionne parfaitement. Les heures d'entretien sont correctement sauvegardées et affichées.
Les fixtures sont chargées avec succès.

**📧 Système d'emails configuré :**
- Envoi automatique d'emails lors de création de notifications
- Configuration Gmail active (voir `backend/.env`)
- MailHog disponible pour tests locaux : http://localhost:8025

**Comptes de test disponibles :**
- **Admin** : `admin@tandem.com` / `password123`
- **Utilisateur** : `test@user.fr` / `test1234`
