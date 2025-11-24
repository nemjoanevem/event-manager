<template>
    <transition name="fade">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
            @click.self="close"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ t("profile.title") }}
                    </h2>
                    <button
                        type="button"
                        class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                        @click="close"
                    >
                        ✕
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ t("profile.name") }}
                        </label>
                        <input
                            type="text"
                            :value="user?.name ?? ''"
                            readonly
                            class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ t("profile.email") }}
                        </label>
                        <input
                            type="text"
                            :value="user?.email ?? ''"
                            readonly
                            class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ t("profile.role") }}
                        </label>
                        <input
                            type="text"
                            :value="user?.is_admin ? t('profile.admin') : t('profile.user')"
                            readonly
                            class="mt-1 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm"
                        />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        class="rounded-xl bg-black px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
                        @click="close"
                    >
                        {{ t("common.close") }}
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
import type { User } from "@/stores/auth";

const props = defineProps<{
    open: boolean;
    user: User | null;
}>();

const emit = defineEmits<{
    (e: "update:open", value: boolean): void;
}>();

const { t } = useI18n();

const close = (): void => {
    emit("update:open", false);
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
