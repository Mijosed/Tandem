# Configuration des Variables d'Environnement - Frontend

## 📁 Fichiers de configuration

### 🔧 `.env` (Production par défaut)
Fichier principal avec les URLs de production. Utilisé par défaut.

### 🛠️ `.env.development`
Configuration pour le développement local avec `npm run dev`.

### 📋 `.env.example` 
Template de configuration avec tous les paramètres documentés.

### 🚀 `.env.prod`
Configuration spécifique pour la production (utilisée avec `npm run build`).

## 🌐 URLs configurées

### Production
- **Frontend** : `https://tandems.social`
- **API** : `https://api.tandems.social/`
- **Analytics** : `https://matomo.tandems.social/`

### Développement
- **Frontend** : `http://localhost:3000`
- **API** : `http://localhost:8888/`
- **Analytics** : `http://localhost:8080/` (optionnel)

## 🔑 Variables importantes

### `API_BASE`
URL de base de l'API Symfony. Utilisée par tous les composables via `useRuntimeConfig().public.apiBase`.

### `MATOMO_*`
Configuration pour le tracking analytique avec Matomo.

### `STRIPE_PUBLISHABLE_KEY`
Clé publique Stripe (visible côté client). Utilisez :
- `pk_live_*` pour la production
- `pk_test_*` pour les tests

## 🚀 Utilisation

### Développement
```bash
cp .env.example .env.development
# Éditez .env.development avec vos valeurs locales
npm run dev
```

### Production
```bash
# Les variables sont déjà configurées dans .env
npm run build
npm run preview
```

## ⚠️ Sécurité

- ✅ Les fichiers `.env*` sont dans `.gitignore`
- ✅ Seules les clés publiques (non sensibles) sont dans le frontend
- ✅ Les clés secrètes restent dans le backend uniquement
