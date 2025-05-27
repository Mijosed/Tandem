<template>
<Card class="hover:shadow-md transition-all duration-200 h-full">
  <CardHeader>
    <CardTitle class="text-lg font-semibold line-clamp-2 mb-2">
      {{ job.title }}
    </CardTitle>
    <div class="space-y-1.5">
      <div class="flex items-center text-sm text-muted-foreground">
        <Building2 class="h-4 w-4 mr-2 shrink-0" />
        <span class="truncate">{{ job.company }}</span>
      </div>
      <div class="flex items-center text-sm text-muted-foreground">
        <MapPin class="h-4 w-4 mr-2 shrink-0" />
        <span class="truncate">{{ job.location }}</span>
      </div>
    </div>
  </CardHeader>

  <CardContent class="space-y-4">
    <div class="flex items-center justify-between gap-4">
      <div class="flex items-center">
        <Briefcase class="h-4 w-4 mr-2 text-muted-foreground" />
        <span class="text-sm font-medium">{{ job.type }}</span>
      </div>
      <span class="text-sm font-semibold text-emerald-600">{{ job.salary }}</span>
    </div>

    <p class="text-sm text-muted-foreground line-clamp-3">
      {{ job.description }}
    </p>

    <div class="pt-4 flex items-center justify-between">
      <span class="text-xs text-muted-foreground">
        Publié le {{ new Date(job.postedDate).toLocaleDateString('fr-FR') }}
      </span>
      <Button 
        variant="default"
        size="sm" 
        :as="job.sourceUrl ? 'a' : 'button'"
        :href="job.sourceUrl"
        target="_blank"
        rel="noopener noreferrer"
      >
        Voir l'offre
      </Button>
    </div>
  </CardContent>
</Card>
</template>

<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Building2, MapPin, Briefcase } from 'lucide-vue-next'

interface Job {
  id: string
  title: string
  company: string
  location: string
  type: string
  salary: string
  description: string
  postedDate: string
  sourceUrl: string
}

defineProps<{
  job: Job
}>()
</script>