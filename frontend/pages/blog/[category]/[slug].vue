<script lang="ts" setup>

const route = useRoute();
console.log(route.params.category, route.params.slug);
const { data: post } = await useAsyncData(route.path, () => {
  return queryCollection('blog').path(`${route.params.category}/${route.params.slug}`).first()
});

</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header avec navigation -->
    <header class="bg-white shadow-sm border-b">
      <div class="max-w-4xl mx-auto px-4 py-4">
        <nav class="flex items-center justify-between">
          <NuxtLink to="/" class="text-xl font-bold text-gray-900 hover:text-blue-600 transition-colors">
            Tandem
          </NuxtLink>
          <NuxtLink to="/blog" class="text-gray-600 hover:text-blue-600 transition-colors">
            ← Retour au blog
          </NuxtLink>
        </nav>
      </div>
    </header>

    <!-- Contenu principal -->
    <main class="max-w-4xl mx-auto px-4 py-8">
      <div v-if="post" class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- En-tête de l'article -->
        <div class="p-8 border-b border-gray-100">
          <div class="flex items-center gap-2 mb-4">
            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
              {{ post.category }}
            </span>
          </div>
          
          <h1 class="text-4xl font-bold text-gray-900 mb-4 leading-tight">
            {{ post.title }}
          </h1>
          
          <div v-if="post.description" class="text-xl text-gray-600 leading-relaxed">
            {{ post.description }}
          </div>
        </div>

        <!-- Contenu de l'article -->
        <div class="p-8 bg-gray-50 border-t border-gray-100">
          <ContentRenderer :value="post" />
        </div>

        <!-- Pied de page de l'article -->
        <div class="p-8 bg-gray-50 border-t border-gray-100">
          <div class="flex items-center justify-between">
            
            
            <NuxtLink 
              to="/blog" 
              class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Retour au blog
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- État de chargement -->
      <div v-else class="bg-white rounded-lg shadow-lg p-8">
        <div class="animate-pulse">
          <div class="h-8 bg-gray-200 rounded w-3/4 mb-4"></div>
          <div class="h-4 bg-gray-200 rounded w-1/2 mb-6"></div>
          <div class="space-y-3">
            <div class="h-4 bg-gray-200 rounded"></div>
            <div class="h-4 bg-gray-200 rounded w-5/6"></div>
            <div class="h-4 bg-gray-200 rounded w-4/6"></div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
  
  
