<template>
    <transition name="fade">
        <div
            v-if="open && event"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
            @click.self="close"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-lg">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ t("bookings.modal_title") }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ event.title }}
                        </p>
                    </div>

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

                <div class="mt-4 space-y-3">
                    <div class="text-sm text-gray-700">
                        <span class="font-medium">{{ t("bookings.available") }}:</span>
                        <span class="ml-1">{{ event.available_seats }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ t("bookings.seats_label") }}
                        </label>
                        <input
                            v-model.number="seats"
                            type="number"
                            min="1"
                            :max="event.available_seats"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            {{ t("bookings.seats_help") }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-xl border px-4 py-2 text-sm font-medium hover:bg-gray-50"
                        @click="close"
                    >
                        {{ t("common.cancel") }}
                    </button>

                    <button
                        type="button"
                        class="rounded-xl bg-black px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50"
                        :disabled="isSubmitting || event.available_seats <= 0 || seats < 1"
                        @click="submit"
                    >
                        {{ isSubmitting ? t("common.loading") : t("bookings.submit") }}
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { api } from "@/lib/api";

export type BookableEvent = {
    id: number;
    title: string;
    available_seats: number;
};

const props = defineProps<{
    open: boolean;
    event: BookableEvent | null;
}>();

const emit = defineEmits<{
    (e: "update:open", value: boolean): void;
    (e: "booked"): void;
}>();

const { t } = useI18n();

const seats = ref(1);
const isSubmitting = ref(false);
const errorMessage = ref<string | null>(null);

/**
 * Reset seats when modal opens or event changes.
 */
watch(
    () => props.open,
    (val) => {
        if (val) {
            seats.value = 1;
            errorMessage.value = null;
        }
    }
);

watch(
    () => props.event?.id,
    () => {
        seats.value = 1;
        errorMessage.value = null;
    }
);

/**
 * Close modal.
 */
const close = (): void => {
    emit("update:open", false);
};

/**
 * Parse backend errors to readable string.
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

/**
 * Submit booking to backend.
 */
const submit = async (): Promise<void> => {
    if (!props.event) {
        return;
    }

    errorMessage.value = null;
    isSubmitting.value = true;

    try {
        await api.post(`/events/${props.event.id}/bookings`, {
            seats_booked: seats.value,
        });

        emit("booked");
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
