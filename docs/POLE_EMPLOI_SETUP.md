# Configuration de l'API Pôle Emploi

## 1. Obtenir les identifiants

1. Créez un compte sur: https://pole-emploi.io/data/api
2. Créez une nouvelle application
3. Récupérez votre `client_id` et `client_secret`

## 2. Configuration

1. Copiez `.env.example` vers `.env`
2. Remplacez les valeurs:
   ```
   POLE_EMPLOI_CLIENT_ID=votre_client_id
   POLE_EMPLOI_CLIENT_SECRET=votre_client_secret
   ```

## 3. Test de l'API

Une fois configuré, vous pouvez tester les endpoints:

- `GET /api/jobs/search?keywords=développeur&location=Paris`
- `GET /api/jobs/{id}`
- `GET /api/jobs/suggestions/{userId}`
- `GET /api/jobs/sectors`

## 4. Fonctionnalités

- ✅ Recherche d'offres avec filtres
- ✅ Détails des offres
- ✅ Suggestions personnalisées
- ✅ Création de candidatures depuis les offres
- ✅ Interface moderne avec pagination
