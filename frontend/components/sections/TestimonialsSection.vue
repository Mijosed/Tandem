<template>
  <section class="py-20 bg-white px-4">
    <div class="max-w-6xl mx-auto">
      <div class="text-center mb-16" data-aos="fade-up">
        <div class="inline-flex items-center gap-2 bg-blue-50 rounded-full px-4 py-2 mb-6">
          <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
          <span class="text-blue-700 text-sm font-medium">Témoignages</span>
        </div>
        <h2 class="text-4xl font-bold text-gray-900 mb-6">
          Nos utilisateurs témoignent
        </h2>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
          Découvrez comment Tandem transforme la recherche d'alternance des étudiants
        </p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-8 relative transform hover:scale-105 transition-transform duration-300" data-aos="fade-up" data-aos-delay="100">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
              <span class="text-white font-bold text-lg">M</span>
            </div>
            <div>
              <h4 class="font-semibold text-gray-900">Marie L.</h4>
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
            J'ai trouvé mon alternance en 2 mois."
          </p>
          <Quote class="absolute top-4 right-4 h-8 w-8 text-blue-200" />
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-8 relative transform hover:scale-105 transition-transform duration-300" data-aos="fade-up" data-aos-delay="200">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center">
              <span class="text-white font-bold text-lg">A</span>
            </div>
            <div>
              <h4 class="font-semibold text-gray-900">Antoine M.</h4>
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
            "Interface super intuitive ! Les notifications m'évitent d'oublier mes relances. 
            Un vrai game-changer !"
          </p>
          <Quote class="absolute top-4 right-4 h-8 w-8 text-purple-200" />
        </div>

        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-8 relative transform hover:scale-105 transition-transform duration-300" data-aos="fade-up" data-aos-delay="300">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center">
              <span class="text-white font-bold text-lg">L</span>
            </div>
            <div>
              <h4 class="font-semibold text-gray-900">Laura C.</h4>
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
            "Tandem a révolutionné ma recherche. Je peux me concentrer sur mes entretiens 
            au lieu de m'inquiéter de ma gestion."
          </p>
          <Quote class="absolute top-4 right-4 h-8 w-8 text-green-200" />
        </div>
      </div>

      <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl p-12" data-aos="fade-up">
        <div class="grid md:grid-cols-3 gap-8 text-center">
          <div>
            <div class="text-4xl md:text-5xl font-bold text-blue-600 mb-2">500+</div>
            <p class="text-gray-600 font-medium text-lg">Étudiants accompagnés</p>
          </div>
          <div>
            <div class="text-4xl md:text-5xl font-bold text-purple-600 mb-2">85%</div>
            <p class="text-gray-600 font-medium text-lg">Taux de réussite</p>
          </div>
          <div>
            <div class="text-4xl md:text-5xl font-bold text-indigo-600 mb-2">4.8/5</div>
            <p class="text-gray-600 font-medium text-lg">Note moyenne</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { Star, Quote } from 'lucide-vue-next'
</script> 