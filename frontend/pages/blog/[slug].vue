<script setup>
const route = useRoute()
const slug = route.path.replace('/blog/', '')

const { data: page } = await useAsyncData(route.path, () => {
  return queryCollection('blog').path(slug).first()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header de l'article -->
    <div class="bg-white border-b">
      <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
          <nav class="mb-6">
            <NuxtLink to="/blog" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
              Retour au blog
            </NuxtLink>
          </nav>
          
          <div v-if="page" class="space-y-4">
            <div class="flex items-center gap-4 text-sm text-gray-600">
              <span>{{ page.author }}</span>
              <span>•</span>
              <span>{{ new Date(page.date).toLocaleDateString('fr-FR', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
              }) }}</span>
              <span>•</span>
              <div class="flex gap-2">
                <span v-for="tag in page.tags" :key="tag" 
                      class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                  {{ tag }}
                </span>
              </div>
            </div>
            
            <h1 class="text-4xl font-bold text-gray-900 leading-tight">
              {{ page.title }}
            </h1>
            
            <p class="text-xl text-gray-600 leading-relaxed">
              {{ page.description }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenu de l'article -->
    <div class="container mx-auto px-4 py-12">
      <div class="max-w-4xl mx-auto">
        <article v-if="page" class="prose prose-lg max-w-none">
          <!-- Image de couverture si disponible -->
          <div v-if="page.cover" class="mb-8">
            <img :src="page.cover" :alt="page.title" class="w-full h-64 object-cover rounded-lg shadow-lg">
          </div>
          
          <!-- Contenu Markdown -->
          <div v-html="page.content" class="prose-headings:text-gray-900 prose-p:text-gray-700 prose-li:text-gray-700 prose-strong:text-gray-900 prose-code:bg-gray-100 prose-code:px-1 prose-code:py-0.5 prose-code:rounded prose-pre:bg-gray-900 prose-pre:text-gray-100"></div>
        </article>
        
        <!-- Article non trouvé -->
        <div v-else class="text-center py-12">
          <div class="max-w-md mx-auto">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Article non trouvé</h1>
            <p class="text-gray-600 mb-6">L'article que vous recherchez n'existe pas ou a été déplacé.</p>
            <NuxtLink to="/blog" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
              Retour au blog
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.prose {
  @apply text-gray-800;
}

.prose h1 {
  @apply text-3xl font-bold text-gray-900 mb-6 mt-8;
}

.prose h2 {
  @apply text-2xl font-semibold text-gray-900 mb-4 mt-8;
}

.prose h3 {
  @apply text-xl font-semibold text-gray-900 mb-3 mt-6;
}

.prose p {
  @apply text-gray-700 leading-relaxed mb-4;
}

.prose ul {
  @apply list-disc list-inside mb-4 space-y-2;
}

.prose ol {
  @apply list-decimal list-inside mb-4 space-y-2;
}

.prose li {
  @apply text-gray-700;
}

.prose strong {
  @apply font-semibold text-gray-900;
}

.prose em {
  @apply italic text-gray-700;
}

.prose code {
  @apply bg-gray-100 text-gray-800 px-1 py-0.5 rounded text-sm font-mono;
}

.prose pre {
  @apply bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto mb-4;
}

.prose blockquote {
  @apply border-l-4 border-blue-500 pl-4 italic text-gray-600 my-6;
}

.prose table {
  @apply w-full border-collapse border border-gray-300 mb-4;
}

.prose th {
  @apply bg-gray-100 border border-gray-300 px-4 py-2 text-left font-semibold;
}

.prose td {
  @apply border border-gray-300 px-4 py-2;
}
</style>
