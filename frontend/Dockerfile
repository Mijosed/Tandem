# Étape 1 : build
FROM node:22-slim as builder

WORKDIR /app

# Dépendances système (si nécessaires)
RUN apt-get update && apt-get install -y python3 make g++ && rm -rf /var/lib/apt/lists/*

COPY . .

RUN npm install
RUN npm run build

# Étape 2 : production (image finale)
FROM node:22-slim

WORKDIR /app

COPY --from=builder /app/.output .output
COPY --from=builder /app/node_modules node_modules
COPY --from=builder /app/package.json .

EXPOSE 3000

CMD ["node", ".output/server/index.mjs"]
