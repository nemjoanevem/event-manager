<template>
    <transition name="fade">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
            @click.self="close"
        >
            <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ t("admin_events.create_title") }}
                    </h2>
                    <button
                        type="button"
                        class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                        @click="close"
                    >
                        ✕
                    </button>
                </div>

                <div v-if="errorMessage" class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ errorMessage }}
                </div>

                <form class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2" @submit.prevent="submit">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ t("admin_events.title") }}
                        </label>
                        <input
                            v-model.trim="form.title"
                            type="text"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black"
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ t("admin_events.description") }}
                        </label>
                        <textarea
                            v-model.trim="form.description"
                            rows="3"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black"
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ t("admin_events.location") }}
                        </label>
                        <input
                            v-model.trim="form.location"
                            type="text"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ t("admin_events.starts_at") }}
                        </label>
                        <input
                            v-model="form.starts_at"
                            type="datetime-local"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ t("admin_events.ends_at") }}
                        </label>
                        <input
                            v-model="form.ends_at"
                            type="datetime-local"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ t("admin_events.price") }}
                        </label>
                        <input
                            v-model.number="form.price"
                            type="number"
                            min="0"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ t("admin_events.capacity") }}
                        </label>
                        <input
                            v-model.number="form.capacity"
                            type="number"
                            min="1"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"
                        />
                    </div>

                    <div class="sm:col-span-2 mt-2 flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-xl border px-4 py-2 text-sm font-medium hover:bg-gray-50"
                            @click="close"
                        >
                            {{ t("common.cancel") }}
                        </button>

                        <button
                            type="submit"
                            class="rounded-xl bg-black px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50"
                            :disabled="isSubmitting"
                        >
                            {{ isSubmitting ? t("common.loading") : t("admin_events.create_submit") }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { api } from "@/lib/api";

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: "update:open", value: boolean): void;
    (e: "created"): void;
}>();

const { t } = useI18n();

const isSubmitting = ref(false);
const errorMessage = ref<string | null>(null);

const form = reactive({
    title: "",
    description: "",
    location: "",
    starts_at: "",
    ends_at: "",
    price: 0,
    capacity: 10,
});

/**
 * Reset form when modal opens.
 */
watch(
    () => props.open,
    (val) => {
        if (val) {
            resetForm();
            errorMessage.value = null;
        }
    }
);

function resetForm(): void {
    form.title = "";
    form.description = "";
    form.location = "";
    form.starts_at = "";
    form.ends_at = "";
    form.price = 0;
    form.capacity = 10;
}

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

const close = (): void => {
    emit("update:open", false);
};

const submit = async (): Promise<void> => {
    errorMessage.value = null;
    isSubmitting.value = true;

    try {
        await api.post("/admin/events", {
            title: form.title,
            description: form.description || null,
            location: form.location,
            starts_at: form.starts_at,
            ends_at: form.ends_at,
            price: form.price,
            capacity: form.capacity,
        });

        emit("created");
        close();
    } catch (e) {
        errorMessage.value = parseError(e);
    } finally {
        isSubmitting.value = false;
    }
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
