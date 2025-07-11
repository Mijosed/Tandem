
<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <AppHeader />
    
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700">
      <div class="absolute inset-0 bg-black opacity-10"></div>
      <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/90 to-purple-600/90"></div>
        <div class="absolute top-0 left-0 w-full h-full">
          <div class="absolute top-20 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
          <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-400/20 rounded-full blur-3xl"></div>
        </div>
      </div>
      
      <div class="relative container mx-auto px-4 py-20">
        <div class="max-w-4xl mx-auto text-center">
          <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2 mb-6">
            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
            <span class="text-white/90 text-sm font-medium">Blog actif</span>
          </div>
          
          <h1 class="text-5xl md:text-6xl font-bold text-white mb-6 leading-tight">
            Blog Tandem
          </h1>
          
          <p class="text-xl md:text-2xl text-blue-100 leading-relaxed mb-8 max-w-2xl mx-auto">
            Découvrez nos conseils, guides et témoignages pour réussir votre alternance
          </p>
          
          <div class="flex flex-wrap justify-center gap-4 text-sm text-blue-200">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <span>Conseils experts</span>
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <span>Mise à jour régulière</span>
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
              <span>Communauté active</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Articles Grid -->
    <div class="container mx-auto px-4 py-16">
      <div class="max-w-7xl mx-auto">
        <!-- Filtres et recherche -->
        <div class="mb-12 flex flex-col lg:flex-row gap-6 items-center justify-between">
          <div class="flex items-center gap-6">
            <div>
              <h2 class="text-3xl font-bold text-gray-900 mb-2">
                Nos articles
              </h2>
              <p class="text-gray-600">Découvrez nos derniers conseils et guides</p>
            </div>
            <div class="hidden sm:flex items-center gap-2 bg-white rounded-full px-4 py-2 shadow-sm border">
              <span class="w-2 h-2 bg-green-500 rounded-full"></span>
              <span class="text-sm font-medium text-gray-700">
                {{ posts?.length || 0 }} articles disponibles
              </span>
            </div>
          </div>
          
          <!-- Barre de recherche -->
          <div class="relative group">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl blur opacity-20 group-hover:opacity-30 transition-opacity"></div>
            <div class="relative bg-white rounded-xl shadow-lg border border-gray-200">
              <input 
                type="text" 
                placeholder="Rechercher un article..." 
                class="pl-12 pr-6 py-3 bg-transparent border-none outline-none w-80 placeholder-gray-500"
              >
              <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- Grille d'articles -->
        <div v-if="posts && posts.length > 0" class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
          <article 
            v-for="(post, index) in posts" 
            :key="post.id" 
            class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden transform hover:-translate-y-2"
            :style="{ animationDelay: `${index * 100}ms` }"
          >
            <!-- Image de couverture -->
            <div class="relative h-56 bg-gradient-to-br from-blue-400 via-purple-500 to-indigo-600 overflow-hidden">
              <div v-if="post.cover" class="w-full h-full">
                <img 
                  :src="post.cover" 
                  :alt="post.title"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
              </div>
              <div v-else class="w-full h-full flex items-center justify-center relative">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-400 via-purple-500 to-indigo-600"></div>
                <svg class="w-16 h-16 text-white/80 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
              
              <!-- Badge de catégorie -->
              <div class="absolute top-4 left-4">
                <span class="bg-white/95 backdrop-blur-sm text-gray-800 px-3 py-1.5 rounded-full text-xs font-semibold shadow-lg">
                  {{ post.tags?.[0] || 'Article' }}
                </span>
              </div>
              
              
            </div>

            <!-- Contenu de l'article -->
            <div class="p-8">
              <!-- Métadonnées -->
              <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                
              </div>

              <!-- Titre -->
              <h3 class="text-xl font-bold text-gray-900 mb-4 line-clamp-2 group-hover:text-blue-600 transition-colors leading-tight">
                <NuxtLink :to="`/blog/${post.path}`">
                  {{ post.title }}
                </NuxtLink>
              </h3>

              <!-- Description -->
              <p class="text-gray-600 mb-6 line-clamp-3 leading-relaxed">
                {{ post.description }}
              </p>

             

              <!-- Bouton Lire plus -->
              <NuxtLink 
                :to="`/blog/${post.path}`"
                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-semibold transition-all duration-300 group-hover:gap-3"
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
        <div v-else class="text-center py-20">
          <div class="max-w-md mx-auto">
            <div class="w-24 h-24 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center mx-auto mb-6">
              <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-3">Aucun article trouvé</h3>
            <p class="text-gray-600 mb-8">Nous travaillons sur de nouveaux contenus passionnants. Revenez bientôt !</p>
            <div class="flex justify-center gap-4">
              <button class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                Actualiser
              </button>
              <button class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                Nous contacter
              </button>
            </div>
          </div>
        </div>

        <!-- Newsletter -->
        <div class="mt-20 bg-gradient-to-r from-blue-600 to-purple-600 rounded-3xl shadow-2xl p-12 relative overflow-hidden">
          <div class="absolute inset-0 bg-black/10"></div>
          <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
          <div class="absolute bottom-0 left-0 w-48 h-48 bg-purple-400/20 rounded-full blur-3xl"></div>
          
          <div class="relative max-w-2xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2 mb-6">
              <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
              <span class="text-white/90 text-sm font-medium">Newsletter</span>
            </div>
            
            <h3 class="text-3xl font-bold text-white mb-4">
              Restez informé !
            </h3>
            <p class="text-xl text-blue-100 mb-8 leading-relaxed">
              Recevez nos derniers articles et conseils pour votre alternance directement dans votre boîte mail.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
              <input 
                type="email" 
                placeholder="Votre adresse email" 
                class="flex-1 px-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50"
              >
              <button class="px-8 py-4 bg-white text-blue-600 rounded-xl hover:bg-gray-100 transition-colors font-semibold shadow-lg">
                S'abonner
              </button>
            </div>
            <p class="text-white/70 text-sm mt-4">
              🔒 Vos données sont protégées. Désabonnement en 1 clic.
            </p>
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


