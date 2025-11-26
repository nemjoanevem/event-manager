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
                    v-if="auth.isAuthenticated && !isLoginPage"
                    :to="{ name: 'bookings.mine' }"
                    class="text-sm font-medium text-gray-700 hover:text-gray-900 hover:underline"
                >
                    {{ t("nav.my_bookings") }}
                </router-link>
            </nav>

            <!-- Right side actions -->
            <div class="flex items-center gap-3">
                <span
                    v-if="auth.isAuthenticated && !isLoginPage"
                    class="hidden text-sm text-gray-600 sm:inline"
                >
                    {{ t("nav.welcome", { name: displayName }) }}
                </span>

                <button
                    v-if="auth.isAuthenticated && !isLoginPage"
                    type="button"
                    class="rounded-xl border px-3 py-1.5 text-sm font-medium hover:bg-gray-50"
                    @click="isProfileOpen = true"
                >
                    {{ t("nav.profile") }}
                </button>

                <button
                    v-if="auth.isAuthenticated && !isLoginPage"
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

                                <!-- Language Switcher -->
                <div class="relative">
                    <button
                        type="button"
                        class="rounded-xl border px-2 py-1 text-sm font-medium hover:bg-gray-50"
                        @click="isLangMenuOpen = !isLangMenuOpen"
                    >
                        {{ currentLangLabel }}
                    </button>

                    <div
                        v-if="isLangMenuOpen"
                        class="absolute right-0 mt-2 w-24 rounded-xl border bg-white shadow-md"
                    >
                        <button
                            class="block w-full px-2 py-1 text-left text-sm hover:bg-gray-100 rounded-t-xl"
                            @click="switchLang('en')"
                        >English</button>

                        <button
                            class="block w-full px-2 py-1 text-left text-sm hover:bg-gray-100 rounded-b-xl"
                            @click="switchLang('hu')"
                        >Magyar</button>
                    </div>
                </div>
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
const showNavbar = computed(() => true);
const isLoginPage = computed(() => route.name === "login");

const { locale } = useI18n();

const isLangMenuOpen = ref(false);

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

const switchLang = (lang: 'en' | 'hu') => {
    locale.value = lang;
    localStorage.setItem('app_lang', lang); // elmentjük hogy login után is maradjon
    isLangMenuOpen.value = false;
};

const currentLangLabel = computed(() => {
    return locale.value === 'hu' ? 'HU 🇭🇺' : 'EN 🇬🇧';
});

</script>
