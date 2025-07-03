<template>
  <section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">
          Nos Utilisateurs
        </h2>
        <p class="text-lg text-gray-600">
          Découvrez tous les membres de notre communauté
        </p>
      </div>

      <!-- Loading state -->
      <div v-if="pending" class="text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Chargement des utilisateurs...</p>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="text-center">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          <p class="font-bold">Erreur</p>
          <p>{{ error }}</p>
        </div>
      </div>

      <!-- Users list -->
      <div v-else-if="users && users.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="user in users" 
          :key="user.id"
          class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
        >
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
              <span class="text-white font-semibold text-lg">
                {{ user.name ? user.name.charAt(0).toUpperCase() : user.email.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">
                {{ user.name || 'Utilisateur' }}
              </h3>
              <p class="text-gray-600 text-sm">{{ user.email }}</p>
              <p v-if="user.createdAt" class="text-gray-500 text-xs">
                Inscrit le {{ formatDate(user.createdAt) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="text-center">
        <div class="text-gray-500">
          <p class="text-lg">Aucun utilisateur trouvé</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const { getAllUsers } = useUsers();

// État réactif pour gérer les données et les états de chargement
const users = ref([]);
const pending = ref(true);
const error = ref(null);

// Fonction pour formater les dates
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Récupération des utilisateurs au montage du composant
onMounted(async () => {
  try {
    pending.value = true;
    error.value = null;
    users.value = await getAllUsers();
  } catch (err) {
    error.value = "Impossible de charger les utilisateurs";
    console.error('Erreur lors de la récupération des utilisateurs:', err);
  } finally {
    pending.value = false;
  }
});
</script> 