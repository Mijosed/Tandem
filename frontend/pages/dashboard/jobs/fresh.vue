<template>
  <div style="padding: 30px; background: purple; border: 5px solid orange;">
    <h1 style="color: white; font-size: 50px;">💜 PAGE FRAICHE SANS CACHE 💜</h1>
    <p style="font-size: 25px; color: yellow;">Cette page a un nom différent, pas de cache possible !</p>
    <button @click="clicks++" style="font-size: 20px; background: orange; color: purple; padding: 15px;">
      NOUVEAU COMPTEUR: {{ clicks }}
    </button>
    
    <div style="background: white; color: black; padding: 15px; margin: 20px 0; border-radius: 10px;">
      <h2>✅ Si vous voyez cette page, alors :</h2>
      <ul style="font-size: 18px;">
        <li>✅ Vue.js fonctionne</li>
        <li>✅ Le dossier /dashboard/jobs/ est accessible</li>
        <li>✅ Le problème était le cache du navigateur</li>
      </ul>
    </div>
    
    <div style="background: lightgreen; color: black; padding: 15px; border-radius: 10px;">
      <h3>🚀 Prêt pour la vraie page avec pagination !</h3>
      <p>Confirmez que vous voyez cette page et je remplace index.vue par la version complète.</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const clicks = ref(0)
</script>
