<template>
  <div style="padding: 20px;">
    <h1 style="color: orange; font-size: 32px;">🛠️ TEST JOBS FOLDER</h1>
    <p>Si vous voyez ceci, le dossier /dashboard/jobs/ fonctionne</p>
    <button @click="count++" style="background: orange; color: white; padding: 10px; border: none; border-radius: 5px;">
      Compteur: {{ count }}
    </button>
    
    <div style="margin-top: 20px; background: lightgray; padding: 15px;">
      <h2>Test API Basic</h2>
      <button @click="callAPI" style="background: green; color: white; padding: 10px; border: none; border-radius: 5px;">
        Appeler API
      </button>
      <p v-if="apiStatus">{{ apiStatus }}</p>
    </div>
  </div>
</template>

<script setup>
// PAS DE LAYOUT pour éviter les problèmes
const count = ref(0)
const apiStatus = ref('')

const callAPI = async () => {
  apiStatus.value = 'Chargement...'
  try {
    const response = await fetch('http://localhost:8888/api/pole-emploi/search?page=1')
    if (response.ok) {
      const data = await response.json()
      apiStatus.value = `✅ API OK - ${data.data?.jobs?.length || 0} jobs trouvés`
    } else {
      apiStatus.value = `❌ API Error: ${response.status}`
    }
  } catch (error) {
    apiStatus.value = `❌ Erreur: ${error.message}`
  }
}
</script>
