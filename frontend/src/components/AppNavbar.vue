<template>
    <header v-if="showNavbar" class="w-full border-b bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
            <!-- Brand -->
            <router-link
                :to="{ name: 'events.index' }"
                class="text-lg font-semibold text-gray-900"
            >
                {{ t("common.title") }}
            </router-link>

            <!-- Main navigation -->
            <nav class="flex items-center gap-4">
                <router-link
                    :to="{ name: 'events.index' }"
                    class="text-sm font-medium text-gray-700 hover:text-gray-900 hover:underline"
                >
                    {{ t("nav.events") }}
                </router-link>

                <router-link
                    v-if="auth.isAuthenticated"
                    :to="{ name: 'bookings.mine' }"
                    class="text-sm font-medium text-gray-700 hover:text-gray-900 hover:underline"
                >
                    {{ t("nav.my_bookings") }}
                </router-link>
            </nav>

            <!-- Right side actions -->
            <div class="flex items-center gap-3">
                <span
                    v-if="auth.isAuthenticated"
                    class="hidden text-sm text-gray-600 sm:inline"
                >
                    {{ t("nav.welcome", { name: displayName }) }}
                </span>

                <button
                    v-if="auth.isAuthenticated"
                    type="button"
                    class="rounded-xl border px-3 py-1.5 text-sm font-medium hover:bg-gray-50"
                    @click="isProfileOpen = true"
                >
                    {{ t("nav.profile") }}
                </button>

                <button
                    v-if="auth.isAuthenticated"
                    type="button"
                    class="rounded-xl border px-3 py-1.5 text-sm font-medium hover:bg-gray-50"
                    @click="onLogout"
                >
                    {{ t("nav.logout") }}
                </button>

                <router-link
                    v-else
                    :to="{ name: 'login' }"
                    class="rounded-xl border px-3 py-1.5 text-sm font-medium hover:bg-gray-50"
                >
                    {{ t("nav.login") }}
                </router-link>
            </div>
        </div>

        <!-- Profile modal -->
        <ProfileModal
            v-model:open="isProfileOpen"
            :user="auth.user"
        />
    </header>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { useAuthStore } from "@/stores/auth";
import ProfileModal from "./ProfileModal.vue";

const route = useRoute();
const router = useRouter();
const { t } = useI18n();
const auth = useAuthStore();

const isProfileOpen = ref(false);

/**
 * Hide navbar on login route.
 */
const showNavbar = computed(() => route.name !== "login");

/**
 * Prefer name, fallback to email.
 */
const displayName = computed(() => auth.user?.name || auth.user?.email || "");

/**
 * Logout and redirect to login.
 */
const onLogout = async (): Promise<void> => {
    await auth.logout();
    router.push({ name: "login" });
};
</script>
