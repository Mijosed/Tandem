<template>
  <div style="padding: 20px;">
    <h1 style="color: purple; font-size: 32px;">🔍 TEST DASHBOARD SIMPLE</h1>
    <p>Si vous voyez ceci, le dossier /dashboard fonctionne</p>
    <button @click="count++" style="background: purple; color: white; padding: 10px; border: none; border-radius: 5px;">
      Compteur: {{ count }}
    </button>
  </div>
</template>

<script setup>
// PAS DE LAYOUT pour éviter les problèmes
const count = ref(0)
</script>
