<script setup lang="ts">
import type { SidebarProps } from '@/components/ui/sidebar'
import { ref, computed } from 'vue'
import { useRoute } from '#app'

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

import { useMercure } from '~/composables/useMercure'

const props = withDefaults(defineProps<SidebarProps>(), {
  collapsible: 'icon',
})

const route = useRoute()

const user = ref({
  name: 'John Doe',
  email: 'john.doe@mail.com',
  avatar: '/avatars/shadcn.jpg',
})

const { unreadCount } = useMercure()

const navigationGroups = ref([
  {
    label: 'Général',
    items: [
      {
        title: 'Tableau de bord',
        url: '/dashboard',
        icon: LayoutDashboard,
        isActive: computed(() => route.path === '/dashboard'),
      },
      {
        title: 'Candidatures',
        url: '/dashboard/applications',
        icon: Briefcase,
        isActive: computed(() => route.path === '/dashboard/applications'),
      },
      {
        title: 'Emploi du temps',
        url: '/dashboard/schedule',
        icon: Calendar,
        isActive: computed(() => route.path === '/dashboard/schedule'),
      },
      {
        title: 'Notifications',
        url: '/dashboard/notifications',
        icon: Bell,
        isActive: computed(() => route.path === '/dashboard/notifications'),
        badge: computed(() => unreadCount.value > 0 ? unreadCount.value.toString() : undefined),
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
        isActive: computed(() => route.path === '/dashboard/jobs'),
      },
    ],
  },
  {
    label: 'Administration',
    items: [
      {
        title: 'Admin',
        icon: Users,
        isActive: computed(() => route.path.startsWith('/dashboard/admin')),
        items: [
          {
            title: 'Utilisateurs',
            url: '/dashboard/admin/users',
          },
        ],
      },
    ],
  },
])

const userActions = ref([
  {
    title: 'Profil',
    url: '/dashboard/profile',
    icon: User,
  },
  {
    title: 'Déconnexion',
    url: '/logout',
    icon: LogOut,
  },
])
</script>

<template>
  <Sidebar v-bind="props">
    <SidebarHeader>
      <div class="flex h-16 items-center px-4">
        <img src="/public/logo.png" alt="Tandem" class="h-16" />
      </div>
    </SidebarHeader>
    <SidebarContent>
      <template v-for="group in navigationGroups" :key="group.label">
        <NavMain :label="group.label" :items="group.items" />
      </template>
    </SidebarContent>
    <SidebarFooter>
      <NavUser :user="user" :actions="userActions" />
    </SidebarFooter>
    <SidebarRail />
  </Sidebar>
</template>
