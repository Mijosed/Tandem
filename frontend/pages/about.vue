<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <AppHeader />
    
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
            <span class="text-white/90 text-sm font-medium">Notre histoire</span>
          </div>
          
          <h1 class="text-5xl md:text-6xl font-bold text-white mb-6 leading-tight">
            L'histoire de <span class="text-blue-200">Tandem</span>
          </h1>
          
          <p class="text-xl md:text-2xl text-blue-100 leading-relaxed mb-8 max-w-2xl mx-auto">
            Née de l'expérience de trois étudiants qui ont transformé leurs galères en solution innovante
          </p>
        </div>
      </div>
    </div>

    <section class="py-20 bg-white px-4">
      <div class="max-w-6xl mx-auto">
          <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
              <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-red-100 rounded-lg">
                  <AlertTriangle class="h-8 w-8 text-red-600" />
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Le Constat</h2>
              </div>
              <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                Comme beaucoup d'étudiants, nous avons <strong>galéré à trouver notre alternance</strong>. 
                Entre les candidatures dispersées, les relances oubliées et les suivis approximatifs dans un simple Excel...
              </p>
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <X class="h-5 w-5 text-red-500 mt-1 flex-shrink-0" />
                  <span class="text-gray-600">Aucun outil centralisé pour gérer les candidatures</span>
                </div>
                <div class="flex items-start gap-3">
                  <X class="h-5 w-5 text-red-500 mt-1 flex-shrink-0" />
                  <span class="text-gray-600">Difficile de suivre l'état de chaque candidature</span>
                </div>
                <div class="flex items-start gap-3">
                  <X class="h-5 w-5 text-red-500 mt-1 flex-shrink-0" />
                  <span class="text-gray-600">Relances et deadlines manquées</span>
                </div>
                <div class="flex items-start gap-3">
                  <X class="h-5 w-5 text-red-500 mt-1 flex-shrink-0" />
                  <span class="text-gray-600">Stress et désorganisation</span>
                </div>
              </div>
            </div>
            <div class="flex justify-center">
              <div class="relative">
                <div class="w-80 h-60 bg-gradient-to-br from-red-100 to-orange-100 rounded-xl shadow-lg flex items-center justify-center">
                  <FileSpreadsheet class="h-24 w-24 text-red-400" />
                </div>
                <div class="absolute -top-4 -right-4 w-16 h-16 bg-red-500 rounded-full flex items-center justify-center">
                  <X class="h-8 w-8 text-white" />
                </div>
              </div>
            </div>
          </div>
        </div>
    </section>

    <section class="py-20 bg-gray-50 px-4">
      <div class="max-w-6xl mx-auto">
          <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="order-2 lg:order-1 flex justify-center">
              <div class="relative">
                <div class="w-80 h-60 bg-gradient-to-br from-blue-100 to-purple-100 rounded-xl shadow-lg flex items-center justify-center">
                  <Monitor class="h-24 w-24 text-blue-500" />
                </div>
                <div class="absolute -top-4 -right-4 w-16 h-16 bg-green-500 rounded-full flex items-center justify-center">
                  <Check class="h-8 w-8 text-white" />
                </div>
              </div>
            </div>
            <div class="order-1 lg:order-2">
              <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-green-100 rounded-lg">
                  <Lightbulb class="h-8 w-8 text-green-600" />
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Notre Solution</h2>
              </div>
              <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                Nous avons décidé de <strong>transformer ce problème en opportunité</strong> en créant la plateforme 
                que nous aurions aimé avoir : <strong>Tandem</strong>.
              </p>
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <Check class="h-5 w-5 text-green-500 mt-1 flex-shrink-0" />
                  <span class="text-gray-600">Centralisation de toutes vos candidatures</span>
                </div>
                <div class="flex items-start gap-3">
                  <Check class="h-5 w-5 text-green-500 mt-1 flex-shrink-0" />
                  <span class="text-gray-600">Suivi en temps réel de chaque étape</span>
                </div>
                <div class="flex items-start gap-3">
                  <Check class="h-5 w-5 text-green-500 mt-1 flex-shrink-0" />
                  <span class="text-gray-600">Notifications et rappels automatiques</span>
                </div>
                <div class="flex items-start gap-3">
                  <Check class="h-5 w-5 text-green-500 mt-1 flex-shrink-0" />
                  <span class="text-gray-600">Organisation claire et intuitive</span>
                </div>
              </div>
            </div>
          </div>
        </div>
    </section>

    <section class="py-20 bg-white px-4">
      <div class="max-w-4xl mx-auto text-center">
          <div class="flex justify-center mb-6">
            <div class="p-4 bg-purple-100 rounded-xl">
              <Rocket class="h-12 w-12 text-purple-600" />
            </div>
          </div>
          <h2 class="text-4xl font-bold text-gray-900 mb-6">Notre Vision</h2>
          <p class="text-xl text-gray-600 mb-8 leading-relaxed">
            Cette première version n'est que le début. Notre ambition est que <strong>Tandem devienne l'outil de référence</strong> 
            proposé par les écoles partenaires à leurs nouveaux élèves.
          </p>
          <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl p-8">
            <p class="text-lg text-gray-700 italic leading-relaxed">
              "Fini le temps du pauvre Excel pour gérer sa recherche d'alternance ! 
              Nous voulons offrir à chaque étudiant les outils modernes qu'il mérite pour réussir sa recherche."
            </p>
          </div>
        </div>
    </section>

    <section class="py-20 bg-gradient-to-br from-gray-50 to-blue-50 px-4">
      <div class="max-w-4xl mx-auto text-center">
          <h2 class="text-4xl font-bold text-gray-900 mb-12">L'Équipe Fondatrice</h2>
          <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow">
              <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                <User class="h-10 w-10 text-white" />
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">Mijosé</h3>
              <p class="text-gray-600">Co-fondateur</p>
              <p class="text-sm text-gray-500 mt-2">Développement Web</p>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow">
              <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                <User class="h-10 w-10 text-white" />
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">Joshua</h3>
              <p class="text-gray-600">Co-fondateur</p>
              <p class="text-sm text-gray-500 mt-2">Développement Web</p>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow">
              <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                <User class="h-10 w-10 text-white" />
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">Ludovic</h3>
              <p class="text-gray-600">Co-fondateur</p>
              <p class="text-sm text-gray-500 mt-2">Développement Web</p>
            </div>
          </div>
        </div>
    </section>

    <section class="py-20 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 px-4">
      <div class="max-w-3xl mx-auto text-center">
          <h2 class="text-4xl font-bold text-white mb-6">
            Rejoignez l'aventure Tandem
          </h2>
          <p class="text-xl text-blue-100 mb-8">
            Transformez votre recherche d'alternance en expérience organisée et sereine
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <NuxtLink to="/register">
              <Button size="lg" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-3 text-lg font-semibold">
                Commencer gratuitement
              </Button>
            </NuxtLink>
            <NuxtLink to="/contact">
              <Button size="lg" variant="outline" class="border-white text-blue-600 hover:bg-white px-8 py-3 text-lg font-semibold">
                Nous contacter
              </Button>
            </NuxtLink>
          </div>
        </div>
    </section>

    <section class="py-20 bg-white px-4">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
          <div class="inline-flex items-center gap-2 bg-blue-50 rounded-full px-4 py-2 mb-6">
            <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
            <span class="text-blue-700 text-sm font-medium">Témoignages</span>
          </div>
          <h2 class="text-4xl font-bold text-gray-900 mb-6">
            Ce que disent nos utilisateurs
          </h2>
          <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Découvrez comment Tandem a transformé la recherche d'alternance de nos utilisateurs
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-8 relative">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                <span class="text-white font-bold text-lg">M</span>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900">Marie Dubois</h4>
                <p class="text-sm text-gray-600">Étudiante en Marketing</p>
              </div>
            </div>
            <div class="flex gap-1 mb-4">
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
            </div>
            <p class="text-gray-700 leading-relaxed">
              "Tandem m'a sauvé la vie ! Fini le stress de gérer mes candidatures dans un Excel. 
              J'ai trouvé mon alternance en 2 mois grâce à leur système de suivi."
            </p>
            <Quote class="absolute top-4 right-4 h-8 w-8 text-blue-200" />
          </div>

          <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-8 relative">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center">
                <span class="text-white font-bold text-lg">A</span>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900">Antoine Moreau</h4>
                <p class="text-sm text-gray-600">Étudiant en Informatique</p>
              </div>
            </div>
            <div class="flex gap-1 mb-4">
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
            </div>
            <p class="text-gray-700 leading-relaxed">
              "Interface super intuitive ! Je peux enfin suivre toutes mes candidatures 
              en un coup d'œil. Les notifications m'évitent d'oublier mes relances."
            </p>
            <Quote class="absolute top-4 right-4 h-8 w-8 text-purple-200" />
          </div>

          <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-8 relative">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center">
                <span class="text-white font-bold text-lg">L</span>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900">Laura Chen</h4>
                <p class="text-sm text-gray-600">Étudiante en Design</p>
              </div>
            </div>
            <div class="flex gap-1 mb-4">
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
            </div>
            <p class="text-gray-700 leading-relaxed">
              "Tandem a révolutionné ma recherche d'alternance. L'organisation est parfaite 
              et j'ai pu me concentrer sur mes entretiens au lieu de m'inquiéter de ma gestion."
            </p>
            <Quote class="absolute top-4 right-4 h-8 w-8 text-green-200" />
          </div>

          <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-8 relative">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-full flex items-center justify-center">
                <span class="text-white font-bold text-lg">T</span>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900">Thomas Lopez</h4>
                <p class="text-sm text-gray-600">Étudiant en Commerce</p>
              </div>
            </div>
            <div class="flex gap-1 mb-4">
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
            </div>
            <p class="text-gray-700 leading-relaxed">
              "Grâce à Tandem, j'ai pu postuler de manière organisée et efficace. 
              Le tableau de bord est clair et m'aide à rester motivé dans ma recherche."
            </p>
            <Quote class="absolute top-4 right-4 h-8 w-8 text-amber-200" />
          </div>

          <div class="bg-gradient-to-br from-rose-50 to-pink-50 rounded-2xl p-8 relative">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-gradient-to-br from-rose-500 to-pink-600 rounded-full flex items-center justify-center">
                <span class="text-white font-bold text-lg">S</span>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900">Sarah Martin</h4>
                <p class="text-sm text-gray-600">Étudiante en Communication</p>
              </div>
            </div>
            <div class="flex gap-1 mb-4">
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
            </div>
            <p class="text-gray-700 leading-relaxed">
              "Une plateforme géniale ! Tandem m'a permis de rester organisée et de ne jamais 
              perdre le fil de mes candidatures. Je recommande à tous les étudiants !"
            </p>
            <Quote class="absolute top-4 right-4 h-8 w-8 text-rose-200" />
          </div>

          <div class="bg-gradient-to-br from-teal-50 to-cyan-50 rounded-2xl p-8 relative">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-full flex items-center justify-center">
                <span class="text-white font-bold text-lg">R</span>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900">Romain Petit</h4>
                <p class="text-sm text-gray-600">Étudiant en Ingénierie</p>
              </div>
            </div>
            <div class="flex gap-1 mb-4">
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
              <Star class="h-5 w-5 text-yellow-400 fill-current" />
            </div>
            <p class="text-gray-700 leading-relaxed">
              "Excellent outil ! J'ai pu centraliser toutes mes candidatures et suivre 
              mon avancement. Tandem a vraiment simplifié ma recherche d'alternance."
            </p>
            <Quote class="absolute top-4 right-4 h-8 w-8 text-teal-200" />
          </div>
        </div>

        <div class="mt-16 bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl p-12">
          <div class="grid md:grid-cols-3 gap-8 text-center">
            <div>
              <div class="text-4xl font-bold text-blue-600 mb-2">500+</div>
              <p class="text-gray-600 font-medium">Étudiants accompagnés</p>
            </div>
            <div>
              <div class="text-4xl font-bold text-purple-600 mb-2">85%</div>
              <p class="text-gray-600 font-medium">Taux de réussite</p>
            </div>
            <div>
              <div class="text-4xl font-bold text-indigo-600 mb-2">4.8/5</div>
              <p class="text-gray-600 font-medium">Note moyenne</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <FooterSection />
  </div>
</template>

<script setup lang="ts">
import { 
  AlertTriangle, 
  Lightbulb, 
  Rocket, 
  User,
  Check,
  X,
  FileSpreadsheet,
  Monitor,
  Star,
  Quote
} from 'lucide-vue-next'
import { Button } from '~/components/ui/button'
import AppHeader from '~/components/sections/AppHeader.vue'
import FooterSection from '~/components/sections/FooterSection.vue'

definePageMeta({
  layout: false
})

useSeoMeta({
  title: 'À propos - Tandem | L\'histoire de notre plateforme',
  description: 'Découvrez l\'histoire de Tandem, née de l\'expérience de trois étudiants qui ont transformé leurs difficultés à trouver une alternance en solution innovante.',
  ogTitle: 'À propos de Tandem - Notre histoire',
  ogDescription: 'L\'histoire de trois étudiants qui ont créé la plateforme qu\'ils auraient aimé avoir pour leur recherche d\'alternance.',
  ogImage: '/logo.png',
  twitterTitle: 'À propos de Tandem - Notre histoire',
  twitterDescription: 'Découvrez comment Tandem est né de l\'expérience étudiante pour révolutionner la recherche d\'alternance.',
  twitterImage: '/logo.png',
})
</script> 