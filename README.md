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

## 🗄️ Base de données

Connexion à la base données via Adminer :
- **Système** : PostgreSQL
- **Serveur** : database 
- **User** : app
- **Password** : !ChangeMe!

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


## 🗃️ Initialisation de la base de données
docker exec php php bin/console doctrine:database:create --if-not-exists
docker exec php php bin/console doctrine:migrations:migrate --no-interaction
docker exec php php bin/console hautelook:fixtures:load --no-interaction
