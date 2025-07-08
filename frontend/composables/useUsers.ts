export const useUsers = () => {
  const config = useRuntimeConfig();
  
  // Interface pour typer un utilisateur (mise à jour selon votre API)
  interface User {
    "@id"?: string;
    "@type"?: string;
    id: number;
    email: string;
    roles?: string[];
    name?: string;
    createdAt?: string;
  }

  // Fonction pour récupérer tous les utilisateurs
  const getAllUsers = async (): Promise<User[]> => {
    try {
      // Utiliser le proxy configuré dans nuxt.config.ts au lieu de l'URL complète
      const response = await $fetch('/api/users', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
        server: true, // ✅ Exécuter côté serveur pour SSR (plus rapide)
        key: 'users-list' // ✅ Cache la requête
      });
      
      console.log('Réponse API complète:', response); // 🔍 Debug
      
      // Gérer le format de réponse d'API Platform Symfony
      if (response && response.member && Array.isArray(response.member)) {
        console.log('Utilisateurs trouvés:', response.member.length); // 🔍 Debug
        return response.member;
      } else if (Array.isArray(response)) {
        return response;
      } else if (response && response.data && Array.isArray(response.data)) {
        return response.data;
      } else if (response && response.users && Array.isArray(response.users)) {
        return response.users;
      } else {
        console.warn('Format de réponse inattendu:', response);
        return [];
      }
    } catch (error) {
      console.error('Erreur lors de la récupération des utilisateurs:', error);
      throw error;
    }
  };

  return {
    getAllUsers,
  };
}; 