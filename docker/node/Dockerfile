FROM node:18-alpine

WORKDIR /var/www/frontend

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy local files
COPY . .

EXPOSE 3000

# Start the app in dev mode
CMD ["npm", "run", "dev"]
