<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const users = ref([]);
const loading = ref(true);
const error = ref(null);

// URL directe pour l'API
const apiUrl = 'http://localhost/api/users';

// Fonction pour récupérer les utilisateurs
const fetchUsers = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    const response = await axios.get(apiUrl, {
      headers: {
        'Accept': 'application/ld+json',
      },
    });
    
    // Récupérer les utilisateurs de la réponse
    if (response.data && response.data.member) {
      users.value = response.data.member;
    } else {
      users.value = [];
    }
    
  } catch (err) {
    error.value = 'Erreur lors de la récupération des utilisateurs';
  } finally {
    loading.value = false;
  }
};

// Récupérer les utilisateurs au chargement du composant
onMounted(fetchUsers);
</script>

<template>
  <div class="user-list">
    <h2>Liste des Utilisateurs</h2>
    
    <div v-if="loading" class="loading">
      Chargement des utilisateurs...
    </div>
    
    <div v-else-if="error" class="error">
      <p>{{ error }}</p>
      <button @click="fetchUsers">Réessayer</button>
    </div>
    
    <div v-else-if="users.length === 0" class="no-users">
      Aucun utilisateur trouvé.
    </div>
    
    <ul v-else class="users">
      <li v-for="user in users" :key="user.id" class="user-item">
        <div class="user-email">{{ user.email }}</div>
        <div class="user-roles">
          <span v-for="(role, index) in user.roles" :key="index" class="role-badge">
            {{ role }}
          </span>
        </div>
      </li>
    </ul>
  </div>
</template>

<style scoped>
.user-list {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

h2 {
  margin-bottom: 20px;
  color: #333;
}

.loading, .error, .no-users {
  padding: 20px;
  text-align: center;
  border-radius: 4px;
  margin-bottom: 20px;
}

.loading {
  background-color: #f8f9fa;
}

.error {
  background-color: #f8d7da;
  color: #721c24;
}

.no-users {
  background-color: #e2e3e5;
  color: #383d41;
}

button {
  padding: 8px 16px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 10px;
}

button:hover {
  background-color: #0069d9;
}

.users {
  list-style: none;
  padding: 0;
}

.user-item {
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 4px;
  margin-bottom: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: white;
}

.user-email {
  font-weight: bold;
}

.role-badge {
  background-color: #17a2b8;
  color: white;
  padding: 3px 8px;
  border-radius: 12px;
  font-size: 0.8em;
  margin-left: 5px;
}
</style>