<script setup lang="ts">
import type { SidebarProps } from '@/components/ui/sidebar'
import { ref, computed, onMounted } from 'vue'
import { useRoute } from '#app'
import { useMediaQuery } from '@vueuse/core'


import {
  LayoutDashboard,
  Briefcase,
  Calendar,
  Bell,
  Users,
  User,
  LogOut,
  Crown,
} from 'lucide-vue-next'
import NavMain from '~/components/dashboard/NavMain.vue'
import NavUser from '~/components/dashboard/NavUser.vue'

import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarRail,
} from '@/components/ui/sidebar'

const isMobile = useMediaQuery('(max-width: 768px)')
const collapsibleMode = computed(() => isMobile.value ? 'offcanvas' : 'none')

const props = withDefaults(defineProps<SidebarProps>(), {
  collapsible: 'offcanvas',
})

const route = useRoute()

const { currentUser, userFullName, isAuthenticated, isAdmin } = useAuth()

// État pour gérer l'hydratation côté client
const isHydrated = ref(false)

onMounted(() => {
  isHydrated.value = true
})

const user = computed(() => ({
  name: isHydrated.value ? (userFullName.value || 'Utilisateur') : 'Utilisateur',  
  email: isHydrated.value ? (currentUser.value?.email || 'user@off.com') : 'user@off.com',
  avatar: '/logo.png',
}))

const navigationGroups = computed(() => {
  const groups = [
    {
      label: 'Général',
      items: [
        {
          title: 'Tableau de bord',
          url: '/dashboard',
          icon: LayoutDashboard,
          isActive: route.path === '/dashboard',
        },
        {
          title: 'Candidatures',
          url: '/dashboard/candidatures',
          icon: Briefcase,
          isActive: route.path === '/dashboard/candidatures',
        },
        {
          title: 'Emploi du temps',
          url: '/dashboard/schedule',
          icon: Calendar,
          isActive: route.path === '/dashboard/schedule',
        },
        {
          title: 'Notifications',
          url: '/dashboard/notifications',
          icon: Bell,
          isActive: route.path === '/dashboard/notifications',
        },
      ],
    },
    {
      label: 'Premium',
      items: [
        {
          title: 'Offres d\'emploi',
          url: '/dashboard/jobs',
          icon: Crown,
          isActive: route.path === '/dashboard/jobs',
        },
      ],
    },
  ]

  // N'ajouter la section admin qu'après hydratation côté client
  if (isHydrated.value && isAdmin.value) {
    groups.push({
      label: 'Administration',
      items: [
        {
          title: 'Admin',
          icon: Users,
          isActive: route.path.startsWith('/dashboard/admin'),
          items: [
            {
              title: 'Utilisateurs',
              url: '/dashboard/admin/users',
            },
          ],
        } as any, // Correction temporaire du type
      ],
    })
  }

  return groups
})


</script>

<template>
  <Sidebar v-bind="{ ...props, collapsible: collapsibleMode }">
    <SidebarHeader>
      <div class="flex h-16 items-center px-4">
        <img src="/logo.png" alt="Tandem" class="h-16" />
      </div>
    </SidebarHeader>
    <SidebarContent>
      <template v-for="group in navigationGroups" :key="group.label">
        <NavMain :label="group.label" :items="group.items" />
      </template>
    </SidebarContent>
    <SidebarFooter>
      <NavUser :user="user" />
    </SidebarFooter>
    <SidebarRail />
  </Sidebar>
</template>