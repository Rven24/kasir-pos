<template>
  <div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <!-- Navbar (Hidden on Login View) -->
    <AppNavbar
      v-if="!isLoginPage"
      @toggle-sidebar="sidebarOpen = !sidebarOpen"
    />

    <div class="flex-1 flex overflow-hidden">
      <!-- Sidebar (Hidden on Login View) -->
      <AppSidebar
        v-if="!isLoginPage"
        :is-open="sidebarOpen"
        @close="sidebarOpen = false"
      />

      <!-- Main View Content Area -->
      <main
        class="flex-1 flex flex-col overflow-y-auto min-w-0 transition-all duration-300"
        :class="!isLoginPage ? 'lg:pl-64' : ''"
      >
        <router-view />
      </main>
    </div>

    <!-- Global Toast Alerts -->
    <ToastContainer />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import AppNavbar from '@/components/layout/AppNavbar.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import ToastContainer from '@/components/pos/ToastContainer.vue'

const route = useRoute()
const sidebarOpen = ref(false)

const isLoginPage = computed(() => route.path === '/login')
</script>
