<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <AppHeader />
    
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700">
      <div class="absolute inset-0 bg-black opacity-10"></div>
      <div class="relative container mx-auto px-4 py-20">
        <div class="max-w-4xl mx-auto text-center">
          <h1 class="text-5xl md:text-6xl font-bold text-white mb-6" data-aos="fade-up">
            Contactez-nous
          </h1>
          <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
            Une question ? Une suggestion ? Nous sommes là pour vous accompagner dans votre recherche d'emploi.
          </p>
        </div>
      </div>
    </div>

    <section class="py-20 px-4">
      <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          
          <div class="space-y-8" data-aos="fade-right">
            <div>
              <h2 class="text-3xl font-bold text-gray-900 mb-6">Restons en contact</h2>
              <p class="text-gray-600 text-lg">
                Notre équipe est disponible pour répondre à toutes vos questions et vous aider à optimiser votre recherche d'emploi.
              </p>
            </div>

            <div class="space-y-6">
              <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                  <Mail class="w-6 h-6 text-blue-600" />
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-1">Email</h3>
                  <p class="text-gray-600">contact@tandem.fr</p>
                  <p class="text-sm text-gray-500">Réponse sous 24h</p>
                </div>
              </div>

              <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                  <Phone class="w-6 h-6 text-green-600" />
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-1">Téléphone</h3>
                  <p class="text-gray-600">+33 1 23 45 67 89</p>
                  <p class="text-sm text-gray-500">Du lundi au vendredi, 9h-18h</p>
                </div>
              </div>

              <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                  <MapPin class="w-6 h-6 text-purple-600" />
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-1">Adresse</h3>
                  <p class="text-gray-600">242 Rue du Faubourg Saint-Matthieu<br>75010 Paris, France</p>
                </div>
              </div>
            </div>

            <div>
              <h3 class="font-semibold text-gray-900 mb-4">Suivez-nous</h3>
              <div class="flex gap-4">
                <a href="#" class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white hover:bg-blue-700 transition-colors">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                </a>
                <a href="#" class="w-10 h-10 bg-blue-800 rounded-lg flex items-center justify-center text-white hover:bg-blue-900 transition-colors">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                </a>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-xl p-8" data-aos="fade-left">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Envoyez-nous un message</h3>
            
            <form @submit.prevent="submitForm" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Prénom</label>
                  <input 
                    v-model="form.firstName"
                    type="text" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                    placeholder="Votre prénom"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                  <input 
                    v-model="form.lastName"
                    type="text" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                    placeholder="Votre nom"
                  >
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input 
                  v-model="form.email"
                  type="email" 
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                  placeholder="votre.email@exemple.com"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sujet</label>
                <select 
                  v-model="form.subject"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                >
                  <option value="">Choisissez un sujet</option>
                  <option value="question">Question générale</option>
                  <option value="support">Support technique</option>
                  <option value="feature">Suggestion de fonctionnalité</option>
                  <option value="partnership">Partenariat</option>
                  <option value="other">Autre</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                <textarea 
                  v-model="form.message"
                  required
                  rows="6"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors resize-none"
                  placeholder="Décrivez votre demande..."
                ></textarea>
              </div>

              <Button 
                type="submit" 
                :disabled="isLoading"
                class="w-full bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 py-3 text-lg"
              >
                <span v-if="isLoading" class="flex items-center gap-2">
                  <Loader2 class="w-4 h-4 animate-spin" />
                  Envoi en cours...
                </span>
                <span v-else class="flex items-center gap-2">
                  <Send class="w-4 h-4" />
                  Envoyer le message
                </span>
              </Button>
            </form>

            <div v-if="showSuccess" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
              <div class="flex items-center gap-2 text-green-800">
                <CheckCircle class="w-5 h-5" />
                <span class="font-medium">Message envoyé avec succès !</span>
              </div>
              <p class="text-green-700 text-sm mt-1">Nous vous répondrons dans les plus brefs délais.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 bg-gray-50 px-4">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-12" data-aos="fade-up">
          Questions fréquentes
        </h2>
        
        <div class="space-y-4" data-aos="fade-up" data-aos-delay="100">
          <details class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <summary class="font-semibold text-gray-900 cursor-pointer">
              Comment fonctionne Tandem ?
            </summary>
            <p class="text-gray-600 mt-4">
              Tandem vous aide à organiser et suivre vos candidatures d'emploi. Vous pouvez ajouter vos candidatures, planifier des entretiens et suivre vos démarches en un seul endroit.
            </p>
          </details>

          <details class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <summary class="font-semibold text-gray-900 cursor-pointer">
              L'utilisation est-elle gratuite ?
            </summary>
            <p class="text-gray-600 mt-4">
              Oui, Tandem propose un plan gratuit avec les fonctionnalités essentielles. Des plans premium sont disponibles pour des fonctionnalités avancées.
            </p>
          </details>

          <details class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <summary class="font-semibold text-gray-900 cursor-pointer">
              Mes données sont-elles sécurisées ?
            </summary>
            <p class="text-gray-600 mt-4">
              Absolument. Nous utilisons les dernières technologies de sécurité pour protéger vos données personnelles et professionnelles.
            </p>
          </details>
        </div>
      </div>
    </section>

    <Footer />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Mail, Phone, MapPin, Send, Loader2, CheckCircle } from 'lucide-vue-next'
import AppHeader from '@/components/sections/AppHeader.vue'
import Footer from '~/components/sections/FooterSection.vue'

const form = ref({
  firstName: '',
  lastName: '',
  email: '',
  subject: '',
  message: ''
})

const isLoading = ref(false)
const showSuccess = ref(false)

const submitForm = async () => {
  isLoading.value = true
  
  try {
    // Simulation d'envoi (remplacer par votre logique d'envoi)
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    // Réinitialiser le formulaire
    form.value = {
      firstName: '',
      lastName: '',
      email: '',
      subject: '',
      message: ''
    }
    
    showSuccess.value = true
    setTimeout(() => {
      showSuccess.value = false
    }, 5000)
    
  } catch (error) {
    console.error('Erreur lors de l\'envoi:', error)
  } finally {
    isLoading.value = false
  }
}
</script> 