
<template>
  <div class="min-h-screen bg-gray-50">
    <AppHeader />
    
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 text-white">
      <div class="container mx-auto px-4 py-16">
        <div class="max-w-4xl mx-auto text-center">
          <h1 class="text-4xl md:text-5xl font-bold mb-6">
            Blog Tandem
          </h1>
          <p class="text-xl md:text-2xl text-blue-100 leading-relaxed">
            Découvrez nos conseils, guides et témoignages pour réussir votre alternance
          </p>
        </div>
      </div>
    </div>

    <!-- Articles Grid -->
    <div class="container mx-auto px-4 py-12">
      <div class="max-w-7xl mx-auto">
        <!-- Filtres et recherche (optionnel) -->
        <div class="mb-8 flex flex-col sm:flex-row gap-4 items-center justify-between">
          <div class="flex items-center gap-4">
            <h2 class="text-2xl font-bold text-gray-900">
              Nos articles
            </h2>
            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
              {{ posts?.length || 0 }} articles
            </span>
          </div>
          
          <!-- Barre de recherche (à implémenter plus tard) -->
          <div class="relative">
            <input 
              type="text" 
              placeholder="Rechercher un article..." 
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64"
            >
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
        </div>

        <!-- Grille d'articles -->
        <div v-if="posts && posts.length > 0" class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
          <article 
            v-for="post in posts" 
            :key="post.id" 
            class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group"
          >
            <!-- Image de couverture -->
            <div class="relative h-48 bg-gradient-to-br from-blue-400 to-purple-500 overflow-hidden">
              <div v-if="post.cover" class="w-full h-full">
                <img 
                  :src="post.cover" 
                  :alt="post.title"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                >
              </div>
              <div v-else class="w-full h-full flex items-center justify-center">
                <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
              
              <!-- Badge de catégorie -->
              <div class="absolute top-4 left-4">
                <span class="bg-white bg-opacity-90 text-gray-800 px-3 py-1 rounded-full text-xs font-medium">
                  {{ post.tags?.[0] || 'Article' }}
                </span>
              </div>
            </div>

            <!-- Contenu de l'article -->
            <div class="p-6">
              <!-- Métadonnées -->
              <div class="flex items-center gap-4 text-sm text-gray-500 mb-3">
                <div class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  {{ post.author }}
                </div>
                <div class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  {{ new Date(post.date).toLocaleDateString('fr-FR', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                  }) }}
                </div>
              </div>

              <!-- Titre -->
              <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
                <NuxtLink :to="`/blog/${post.path}`">
                  {{ post.title }}
                </NuxtLink>
              </h3>

              <!-- Description -->
              <p class="text-gray-600 mb-4 line-clamp-3 leading-relaxed">
                {{ post.description }}
              </p>

              <!-- Tags -->
              <div class="flex flex-wrap gap-2 mb-4">
                <span 
                  v-for="tag in post.tags?.slice(0, 3)" 
                  :key="tag"
                  class="bg-gray-100 text-gray-700 px-2 py-1 rounded-md text-xs font-medium hover:bg-gray-200 transition-colors"
                >
                  {{ tag }}
                </span>
                <span v-if="post.tags && post.tags.length > 3" class="text-gray-500 text-xs">
                  +{{ post.tags.length - 3 }} autres
                </span>
              </div>

              <!-- Bouton Lire plus -->
              <NuxtLink 
                :to="`/blog/${post.path}`"
                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium transition-colors group-hover:gap-3"
              >
                Lire l'article
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </NuxtLink>
            </div>
          </article>
        </div>

        <!-- État vide -->
        <div v-else class="text-center py-16">
          <div class="max-w-md mx-auto">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucun article trouvé</h3>
            <p class="text-gray-600">Nous travaillons sur de nouveaux contenus. Revenez bientôt !</p>
          </div>
        </div>

        <!-- Newsletter (optionnel) -->
        <div class="mt-16 bg-white rounded-xl shadow-lg p-8">
          <div class="max-w-2xl mx-auto text-center">
            <h3 class="text-2xl font-bold text-gray-900 mb-4">
              Restez informé !
            </h3>
            <p class="text-gray-600 mb-6">
              Recevez nos derniers articles et conseils pour votre alternance directement dans votre boîte mail.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
              <input 
                type="email" 
                placeholder="Votre adresse email" 
                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
              <button class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                S'abonner
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <FooterSection />
  </div>
</template>

<script setup lang="ts">
const { data: posts } = await useAsyncData('blog', () => queryCollection('blog').all())
import FooterSection from '~/components/sections/FooterSection.vue';
import AppHeader from '~/components/sections/AppHeader.vue';
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
