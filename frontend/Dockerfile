FROM node:22-slim

# Définir le répertoire de travail
WORKDIR /app

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
  python3 \
  make \
  g++ \
  && rm -rf /var/lib/apt/lists/*

# Copier les fichiers de dépendances et installer
COPY package*.json ./
RUN npm install

# Copier le reste du code
COPY . .

# Construire l'application Nuxt
RUN npm run build

# Exposer le port utilisé par Nuxt en prod
EXPOSE 3000

# Lancer le serveur Nuxt en mode production
CMD ["npm", "run", "start"]
