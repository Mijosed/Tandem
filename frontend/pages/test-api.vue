<template>
  <div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">Test des données API</h1>
    
    <!-- Applications -->
    <section class="mb-8">
      <h2 class="text-2xl font-bold mb-4">Candidatures</h2>
      <div v-if="loadingApplications" class="text-center py-4">
        Chargement des candidatures...
      </div>
      <div v-else-if="applicationsError" class="bg-red-50 border border-red-200 rounded-md p-4">
        <p class="text-red-800">{{ applicationsError }}</p>
      </div>
      <div v-else class="bg-white border rounded-lg p-4">
        <p class="mb-4"><strong>Nombre de candidatures :</strong> {{ applications.length }}</p>
        <div v-if="applications.length > 0" class="space-y-2">
          <div 
            v-for="app in applications.slice(0, 3)" 
            :key="app.id"
            class="border-l-4 border-blue-500 pl-4 py-2"
          >
            <p><strong>Position :</strong> {{ app.position || 'Non spécifiée' }}</p>
            <p><strong>Entreprise :</strong> {{ app.company || 'Non spécifiée' }}</p>
            <p><strong>Status :</strong> {{ app.status }}</p>
            <p><strong>Date :</strong> {{ new Date(app.createdAt).toLocaleDateString('fr-FR') }}</p>
          </div>
        </div>
        <p v-else class="text-gray-500">Aucune candidature trouvée</p>
      </div>
    </section>

    <!-- Notifications -->
    <section class="mb-8">
      <h2 class="text-2xl font-bold mb-4">Notifications</h2>
      <div v-if="loadingNotifications" class="text-center py-4">
        Chargement des notifications...
      </div>
      <div v-else-if="notificationsError" class="bg-red-50 border border-red-200 rounded-md p-4">
        <p class="text-red-800">{{ notificationsError }}</p>
      </div>
      <div v-else class="bg-white border rounded-lg p-4">
        <p class="mb-4"><strong>Nombre de notifications :</strong> {{ notifications.length }}</p>
        <div v-if="notifications.length > 0" class="space-y-2">
          <div 
            v-for="notif in notifications.slice(0, 3)" 
            :key="notif.id"
            class="border-l-4 border-green-500 pl-4 py-2"
          >
            <p><strong>Titre :</strong> {{ notif.title || 'Sans titre' }}</p>
            <p><strong>Message :</strong> {{ notif.message || 'Sans message' }}</p>
            <p><strong>Lu :</strong> {{ notif.isRead ? 'Oui' : 'Non' }}</p>
            <p><strong>Date :</strong> {{ new Date(notif.createdAt).toLocaleDateString('fr-FR') }}</p>
          </div>
        </div>
        <p v-else class="text-gray-500">Aucune notification trouvée</p>
      </div>
    </section>

    <!-- Jobs -->
    <section class="mb-8">
      <h2 class="text-2xl font-bold mb-4">Offres d'emploi</h2>
      <div v-if="loadingJobs" class="text-center py-4">
        Chargement des offres...
      </div>
      <div v-else-if="jobsError" class="bg-red-50 border border-red-200 rounded-md p-4">
        <p class="text-red-800">{{ jobsError }}</p>
      </div>
      <div v-else class="bg-white border rounded-lg p-4">
        <p class="mb-4"><strong>Nombre d'offres :</strong> {{ jobs.length }}</p>
        <div v-if="jobs.length > 0" class="space-y-2">
          <div 
            v-for="job in jobs.slice(0, 3)" 
            :key="job.id"
            class="border-l-4 border-purple-500 pl-4 py-2"
          >
            <p><strong>Titre :</strong> {{ job.title || 'Non spécifié' }}</p>
            <p><strong>Entreprise :</strong> {{ job.company || 'Non spécifiée' }}</p>
            <p><strong>Type :</strong> {{ job.contractType || 'Non spécifié' }}</p>
            <p><strong>Date :</strong> {{ new Date(job.createdAt).toLocaleDateString('fr-FR') }}</p>
          </div>
        </div>
        <p v-else class="text-gray-500">Aucune offre trouvée</p>
      </div>
    </section>

    <!-- Bouton pour rafraîchir -->
    <div class="text-center">
      <button 
        @click="refreshData"
        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md"
      >
        Rafraîchir les données
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
const config = useRuntimeConfig()

const applications = ref([])
const config = useRuntimeConfig()

const notifications = ref([])
const config = useRuntimeConfig()

const jobs = ref([])

const config = useRuntimeConfig()

const loadingApplications = ref(false)
const config = useRuntimeConfig()

const loadingNotifications = ref(false)
const config = useRuntimeConfig()

const loadingJobs = ref(false)

const config = useRuntimeConfig()

const applicationsError = ref('')
const config = useRuntimeConfig()

const notificationsError = ref('')
const config = useRuntimeConfig()

const jobsError = ref('')

const fetchApplications = async () => {
  loadingApplications.value = true
  applicationsError.value = ''
  try {
    const response = await fetch('${config.public.apiBase}/api/candidatures')
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`)
    }
    const data = await response.json()
    applications.value = data.member || []
  } catch (err: any) {
    applicationsError.value = err.message
  } finally {
    loadingApplications.value = false
  }
}

const fetchNotifications = async () => {
  loadingNotifications.value = true
  notificationsError.value = ''
  try {
    const response = await fetch('${config.public.apiBase}/api/notifications')
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`)
    }
    const data = await response.json()
    notifications.value = data.member || []
  } catch (err: any) {
    notificationsError.value = err.message
  } finally {
    loadingNotifications.value = false
  }
}

const fetchJobs = async () => {
  loadingJobs.value = true
  jobsError.value = ''
  try {
    const response = await fetch('${config.public.apiBase}/api/jobs')
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`)
    }
    const data = await response.json()
    jobs.value = data.member || []
  } catch (err: any) {
    jobsError.value = err.message
  } finally {
    loadingJobs.value = false
  }
}

const refreshData = async () => {
  await Promise.all([
    fetchApplications(),
    fetchNotifications(),
    fetchJobs()
  ])
}

onMounted(() => {
  refreshData()
})
</script>
