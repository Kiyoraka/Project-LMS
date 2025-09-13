<template>
  <component :is="dashboardComponent" />
</template>

<script setup>
import { computed } from 'vue';
import { useDeviceDetection } from '../../composables/useDeviceDetection.js';
import Dashboard from './Dashboard.vue';
import MobileDashboard from './MobileDashboard.vue';

const { isMobile } = useDeviceDetection();

const dashboardComponent = computed(() => {
  return isMobile.value ? MobileDashboard : Dashboard;
});
</script> 