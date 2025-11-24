<template>
    <GlobalLoader />
    <AppNavbar />
    <router-view />
</template>

<script setup lang="ts">
import GlobalLoader from "@/components/GlobalLoader.vue";
import AppNavbar from "@/components/AppNavbar.vue";
</script>