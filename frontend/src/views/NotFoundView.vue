<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
        <div class="text-center">
            <h1 class="text-6xl font-bold text-gray-900">404</h1>
            <p class="mt-4 text-gray-600">
                {{ t("common.not_found") }}
            </p>
            <router-link
                :to="{ name: 'events.index' }"
                class="mt-6 inline-block rounded-xl bg-black px-5 py-3 text-white hover:bg-gray-800"
            >
                {{ t("common.back_to_events") }}
            </router-link>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";

const { t } = useI18n();
</script>
