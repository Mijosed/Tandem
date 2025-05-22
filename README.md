Pour exécuter une commande toujours mettre avant :
docker compose exec [service](backend, frontend..) + commande

Exemples : 

docker compose exec backend bin/console hautelook:fixtures:load 
docker compose exec frontend npm install framer 


Frontend : http://localhost:3000
Adminer : http://localhost:8080
API Platform : http://localhost/api/docs.html

Connexion à la base données : 
Systeme : PostgreSql
Serveur : database 
User : app
Password :!ChangeMe!