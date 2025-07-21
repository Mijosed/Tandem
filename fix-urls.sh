#!/bin/bash

# Script pour corriger toutes les URLs localhost:8888 vers la configuration centralisée

echo "🔄 Correction des URLs dans les composables..."

# Correction des composables qui utilisent encore des URLs codées en dur
files_to_fix=(
  "frontend/composables/useDashboard.ts"
  "frontend/composables/useStripe.ts" 
  "frontend/composables/useApplications.ts"
  "frontend/composables/useCandidatures.ts"
  "frontend/composables/useUsers.ts"
  "frontend/composables/useFranceTravail.ts"
  "frontend/composables/useNotifications.ts"
  "frontend/pages/premium.vue"
  "frontend/pages/dashboard/jobs/index.vue"
  "frontend/pages/test-api.vue"
  "frontend/pages/test-applications.vue"
  "frontend/pages/test-auth-heure.vue"
)

# Remplacements
for file in "${files_to_fix[@]}"; do
  if [ -f "$file" ]; then
    echo "Correction de $file"
    
    # Remplacer les URLs localhost par la configuration
    sed -i 's|http://localhost:8888|${config.public.apiBase}|g' "$file"
    sed -i "s|'http://localhost:8888|\${config.public.apiBase}|g" "$file"
    sed -i 's|"http://localhost:8888"|${config.public.apiBase}|g' "$file"
    
    # Ajouter useRuntimeConfig si pas présent
    if ! grep -q "useRuntimeConfig" "$file"; then
      echo "Ajout de useRuntimeConfig à $file"
      # Pour les composables .ts
      if [[ $file == *.ts ]]; then
        sed -i '/export const use.*= () => {/a\  const config = useRuntimeConfig()' "$file"
      fi
      # Pour les pages .vue avec <script setup>
      if [[ $file == *.vue ]]; then
        sed -i '/const.*= useToast()/a\const config = useRuntimeConfig()' "$file" 2>/dev/null || true
        sed -i '/const.*= ref(/i\const config = useRuntimeConfig()\n' "$file" 2>/dev/null || true
      fi
    fi
  fi
done

echo "✅ Corrections appliquées"
