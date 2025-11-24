<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="w-full max-w-md rounded-2xl bg-white shadow p-6">
            <h1 class="text-2xl font-semibold text-gray-900 text-center">
                {{ t("auth.title") }}
            </h1>

            <!-- Tabs -->
            <div class="mt-6 grid grid-cols-2 rounded-xl bg-gray-100 p-1">
                <button
                    type="button"
                    class="rounded-lg py-2 text-sm font-medium transition"
                    :class="tab === 'login' ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900'"
                    @click="tab = 'login'"
                >
                    {{ t("auth.login_tab") }}
                </button>
                <button
                    type="button"
                    class="rounded-lg py-2 text-sm font-medium transition"
                    :class="tab === 'register' ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900'"
                    @click="tab = 'register'"
                >
                    {{ t("auth.register_tab") }}
                </button>
            </div>

            <!-- Error message -->
            <div v-if="errorMessage" class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ errorMessage }}
            </div>

            <!-- Login form -->
            <form v-if="tab === 'login'" class="mt-6 space-y-4" @submit.prevent="submitLogin">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ t("auth.email") }}
                    </label>
                    <input
                        v-model.trim="loginForm.email"
                        type="email"
                        autocomplete="email"
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black"
                        :placeholder="t('auth.email_placeholder')"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ t("auth.password") }}
                    </label>
                    <input
                        v-model="loginForm.password"
                        type="password"
                        autocomplete="current-password"
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black"
                        :placeholder="t('auth.password_placeholder')"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-black px-4 py-2.5 text-white font-medium hover:bg-gray-800 transition"
                    :disabled="isSubmitting"
                >
                    {{ isSubmitting ? t("common.loading") : t("auth.login_button") }}
                </button>
            </form>

            <!-- Register form -->
            <form v-else class="mt-6 space-y-4" @submit.prevent="submitRegister">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ t("auth.name") }}
                    </label>
                    <input
                        v-model.trim="registerForm.name"
                        type="text"
                        autocomplete="name"
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black"
                        :placeholder="t('auth.name_placeholder')"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ t("auth.email") }}
                    </label>
                    <input
                        v-model.trim="registerForm.email"
                        type="email"
                        autocomplete="email"
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black"
                        :placeholder="t('auth.email_placeholder')"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ t("auth.password") }}
                    </label>
                    <input
                        v-model="registerForm.password"
                        type="password"
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black"
                        :placeholder="t('auth.password_placeholder')"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ t("auth.password_confirmation") }}
                    </label>
                    <input
                        v-model="registerForm.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black"
                        :placeholder="t('auth.password_confirmation_placeholder')"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-black px-4 py-2.5 text-white font-medium hover:bg-gray-800 transition"
                    :disabled="isSubmitting"
                >
                    {{ isSubmitting ? t("common.loading") : t("auth.register_button") }}
                </button>

                <p class="text-xs text-gray-500 text-center">
                    {{ t("auth.register_note") }}
                </p>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, ref } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useI18n } from "vue-i18n";
import { useAuthStore } from "@/stores/auth";

type Tab = "login" | "register";

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const auth = useAuthStore();

const tab = ref<Tab>("login");
const isSubmitting = ref(false);
const errorMessage = ref<string | null>(null);

const loginForm = reactive({
    email: "",
    password: "",
});

const registerForm = reactive({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

/**
 * Normalize backend validation errors to a single string for now.
 * Later we can show field-level errors.
 */
function parseError(e: any): string {
    const msg = e?.response?.data?.message;
    if (msg) {
        return msg;
    }

    const errors = e?.response?.data?.errors;
    if (errors && typeof errors === "object") {
        const firstKey = Object.keys(errors)[0];
        if (firstKey && errors[firstKey]?.length) {
            return errors[firstKey][0];
        }
    }

    return t("common.unknown_error");
}

async function submitLogin(): Promise<void> {
    errorMessage.value = null;
    isSubmitting.value = true;

    try {
        await auth.login({
            email: loginForm.email,
            password: loginForm.password,
        });

        const redirect = (route.query.redirect as string) || "/events";
        await router.replace(redirect);
    } catch (e) {
        errorMessage.value = parseError(e);
    } finally {
        isSubmitting.value = false;
    }
}

async function submitRegister(): Promise<void> {
    errorMessage.value = null;
    isSubmitting.value = true;

    try {
        await auth.register({
            name: registerForm.name,
            email: registerForm.email,
            password: registerForm.password,
            password_confirmation: registerForm.password_confirmation,
        });

        await router.replace("/events");
    } catch (e) {
        errorMessage.value = parseError(e);
    } finally {
        isSubmitting.value = false;
    }
}
</script>
