<script setup>
const route = useRoute()
const slug = route.path.replace('/blog/', '')

const { data: page } = await useAsyncData(route.path, () => {
  return queryCollection('blog').path(slug).first()
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <!-- Header de l'article -->
    <div class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 overflow-hidden">
      <div class="absolute inset-0 bg-black/20"></div>
      <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
      <div class="absolute bottom-0 left-0 w-64 h-64 bg-purple-400/20 rounded-full blur-3xl"></div>
      
      <div class="relative container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
          <nav class="mb-8">
            <NuxtLink to="/blog" class="inline-flex items-center gap-3 text-white/90 hover:text-white transition-colors group">
              <div class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center group-hover:bg-white/30 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
              </div>
              <span class="font-medium">Retour au blog</span>
            </NuxtLink>
          </nav>
          
          <div v-if="page" class="space-y-6">
            <!-- Métadonnées -->
            <div class="flex flex-wrap items-center gap-4 text-sm text-white/80">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                  <span class="text-white text-sm font-bold">{{ page.author?.charAt(0) || 'A' }}</span>
                </div>
                <span class="font-medium">{{ page.author }}</span>
              </div>
              <span>•</span>
              <div class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>{{ new Date(page.date).toLocaleDateString('fr-FR', { 
                  year: 'numeric', 
                  month: 'long', 
                  day: 'numeric' 
                }) }}</span>
              </div>
              <span>•</span>
              <div class="flex gap-2">
                <span v-for="tag in page.tags" :key="tag" 
                      class="bg-white/20 backdrop-blur-sm text-white px-3 py-1 rounded-full text-xs font-medium hover:bg-white/30 transition-colors">
                  #{{ tag }}
                </span>
              </div>
            </div>
            
            <!-- Titre -->
            <h1 class="text-4xl md:text-5xl font-bold text-white leading-tight">
              {{ page.title }}
            </h1>
            
            <!-- Description -->
            <p class="text-xl text-white/90 leading-relaxed max-w-3xl">
              {{ page.description }}
            </p>
            
          </div>
        </div>
      </div>
    </div>

    <!-- Contenu de l'article -->
    <div class="container mx-auto px-4 py-12">
      <div class="max-w-4xl mx-auto">
        <article v-if="page" class="bg-white rounded-2xl shadow-xl overflow-hidden">
          <!-- Image de couverture si disponible -->
          <div v-if="page.cover" class="relative h-80 bg-gradient-to-br from-blue-400 via-purple-500 to-indigo-600 overflow-hidden">
            <img :src="page.cover" :alt="page.title" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
          </div>
          
          <!-- Contenu Markdown -->
          <div class="p-8 md:p-12">
            <div class="markdown-content">
              <!-- Si le contenu est dans page.body (Nuxt Content) -->
              <ContentRenderer v-if="page.body" :value="page" />
              
              <!-- Si le contenu est dans page.content (queryCollection) -->
              <div v-else-if="page.content" v-html="page.content"></div>
              
              <!-- Fallback : afficher le contenu brut -->
              <div v-else class="whitespace-pre-wrap">{{ page }}</div>
            </div>
            
            <!-- Section de partage -->
            <div class="mt-12 pt-8 border-t border-gray-200">
              <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                  <span class="text-gray-600 font-medium">Partager cet article :</span>
                  <div class="flex gap-2">
                    <button class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                      </svg>
                    </button>
                    <button class="w-10 h-10 bg-blue-800 text-white rounded-full flex items-center justify-center hover:bg-blue-900 transition-colors">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                      </svg>
                    </button>
                    <button class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center hover:bg-green-700 transition-colors">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                      </svg>
                    </button>
                  </div>
                </div>
                
                <NuxtLink to="/blog" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  Voir tous les articles
                </NuxtLink>
              </div>
            </div>
          </div>
        </article>
        
        <!-- Article non trouvé -->
        <div v-else class="text-center py-20">
          <div class="max-w-md mx-auto">
            <div class="w-24 h-24 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center mx-auto mb-6">
              <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Article non trouvé</h1>
            <p class="text-gray-600 mb-8 text-lg">L'article que vous recherchez n'existe pas ou a été déplacé.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
              <NuxtLink to="/blog" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                Retour au blog
              </NuxtLink>
              <button class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                Nous contacter
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


