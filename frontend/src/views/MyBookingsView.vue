<template>
    <div class="mx-auto max-w-5xl px-4 py-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">
                {{ t("my_bookings.title") }}
            </h1>

            <!-- Sort controls -->
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">
                    {{ t("my_bookings.sort_by") }}
                </label>
                <select
                    v-model="filters.sort_by"
                    class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm"
                    @change="applyFilters"
                >
                    <option value="created_at">{{ t("my_bookings.sort.created_at") }}</option>
                    <option value="starts_at">{{ t("my_bookings.sort.starts_at") }}</option>
                    <option value="title">{{ t("my_bookings.sort.title") }}</option>
                </select>

                <button
                    type="button"
                    class="rounded-xl border px-3 py-2 text-sm hover:bg-gray-50"
                    @click="toggleSortDir"
                >
                    <span v-if="filters.sort_dir === 'asc'">↑</span>
                    <span v-else>↓</span>
                </button>
            </div>
        </div>

        <!-- Error / Empty / Loading -->
        <div v-if="errorMessage" class="mt-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ errorMessage }}
        </div>

        <div v-else-if="isLoading" class="mt-6 text-sm text-gray-600">
            {{ t("common.loading") }}
        </div>

        <div v-else-if="bookings.length === 0" class="mt-6 text-sm text-gray-600">
            {{ t("my_bookings.empty") }}
        </div>

        <!-- Bookings list -->
        <div v-else class="mt-6 space-y-4">
            <article
                v-for="b in bookings"
                :key="b.id"
                class="rounded-2xl border bg-white p-4 shadow-sm"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ b.event.title }}
                        </h2>

                        <div class="mt-2 grid grid-cols-1 gap-1 text-sm text-gray-700 sm:grid-cols-2">
                            <div>
                                <span class="font-medium text-gray-900">{{ t("my_bookings.starts_at") }}:</span>
                                <span class="ml-1">{{ formatDate(b.event.starts_at) }}</span>
                            </div>

                            <div>
                                <span class="font-medium text-gray-900">{{ t("my_bookings.location") }}:</span>
                                <span class="ml-1">{{ b.event.location }}</span>
                            </div>

                            <div>
                                <span class="font-medium text-gray-900">{{ t("my_bookings.seats") }}:</span>
                                <span class="ml-1">{{ b.seats_booked }}</span>
                            </div>

                            <div>
                                <span class="font-medium text-gray-900">{{ t("my_bookings.status") }}:</span>
                                <span
                                    class="ml-1 inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="b.status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-700'"
                                >
                                    {{ b.status === "active" ? t("my_bookings.active") : t("my_bookings.cancelled") }}
                                </span>
                            </div>

                            <div class="sm:col-span-2">
                                <span class="font-medium text-gray-900">{{ t("my_bookings.booked_at") }}:</span>
                                <span class="ml-1">{{ formatDate(b.created_at) }}</span>
                            </div>
                        </div>

                        <p v-if="b.event.description" class="mt-2 line-clamp-2 text-sm text-gray-600">
                            {{ b.event.description }}
                        </p>
                    </div>

                    <!-- Right controls -->
                    <div class="flex shrink-0 items-center gap-2 sm:flex-col sm:items-end">
                        <router-link
                            :to="`/events/${b.event.id}`"
                            class="rounded-xl border px-3 py-1.5 text-sm font-medium hover:bg-gray-50"
                        >
                            {{ t("my_bookings.details") }}
                        </router-link>

                        <button
                            type="button"
                            class="rounded-xl bg-black px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50"
                            :disabled="!canCancel(b)"
                            @click="confirmCancel(b)"
                        >
                            {{ t("my_bookings.cancel") }}
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <!-- Pagination -->
        <div v-if="meta.last_page > 1" class="mt-8 flex items-center justify-center gap-2">
            <button
                type="button"
                class="rounded-xl border px-3 py-1.5 text-sm hover:bg-gray-50 disabled:opacity-50"
                :disabled="meta.current_page <= 1"
                @click="goToPage(meta.current_page - 1)"
            >
                {{ t("common.prev") }}
            </button>

            <button
                v-for="page in visiblePages"
                :key="page"
                type="button"
                class="rounded-xl border px-3 py-1.5 text-sm hover:bg-gray-50"
                :class="page === meta.current_page ? 'bg-black text-white border-black hover:bg-black' : ''"
                @click="goToPage(page)"
            >
                {{ page }}
            </button>

            <button
                type="button"
                class="rounded-xl border px-3 py-1.5 text-sm hover:bg-gray-50 disabled:opacity-50"
                :disabled="meta.current_page >= meta.last_page"
                @click="goToPage(meta.current_page + 1)"
            >
                {{ t("common.next") }}
            </button>
        </div>

        <!-- Simple confirm modal -->
        <transition name="fade">
            <div
                v-if="cancelTarget"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
                @click.self="cancelTarget = null"
            >
                <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-lg">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ t("my_bookings.cancel_confirm_title") }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-700">
                        {{ t("my_bookings.cancel_confirm_text", { title: cancelTarget.event.title }) }}
                    </p>

                    <div class="mt-6 flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-xl border px-4 py-2 text-sm font-medium hover:bg-gray-50"
                            @click="cancelTarget = null"
                        >
                            {{ t("common.cancel") }}
                        </button>

                        <button
                            type="button"
                            class="rounded-xl bg-black px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50"
                            :disabled="isCancelling"
                            @click="doCancel"
                        >
                            {{ isCancelling ? t("common.loading") : t("my_bookings.cancel") }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from "vue";
import { useI18n } from "vue-i18n";
import { api } from "@/lib/api";

type BookingListItem = {
    id: number;
    seats_booked: number;
    status: "active" | "cancelled";
    created_at: string;
    cancelled_at: string | null;
    event: {
        id: number;
        title: string;
        description: string | null;
        location: string;
        starts_at: string;
        ends_at: string;
    };
};

type PaginationMeta = {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
};

const { t, locale } = useI18n();

const bookings = ref<BookingListItem[]>([]);
const isLoading = ref(false);
const errorMessage = ref<string | null>(null);

const meta = reactive<PaginationMeta>({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
});

const filters = reactive({
    sort_by: "created_at",
    sort_dir: "desc" as "asc" | "desc",
    per_page: 10,
});

const cancelTarget = ref<BookingListItem | null>(null);
const isCancelling = ref(false);

/**
 * Fetch current user's bookings.
 */
async function fetchBookings(page = 1): Promise<void> {
    isLoading.value = true;
    errorMessage.value = null;

    try {
        const res = await api.get("/bookings", {
            params: {
                page,
                per_page: filters.per_page,
                sort_by: filters.sort_by,
                sort_dir: filters.sort_dir,
            },
        });

        const rows = res.data.bookings?.data ?? res.data.bookings ?? [];
        bookings.value = rows;

        const m = res.data.meta ?? {};
        meta.current_page = m.current_page ?? page;
        meta.per_page = m.per_page ?? filters.per_page;
        meta.total = m.total ?? rows.length;
        meta.last_page = m.last_page ?? 1;
    } catch (e: any) {
        errorMessage.value = e?.response?.data?.message ?? t("common.unknown_error");
    } finally {
        isLoading.value = false;
    }
}

function applyFilters(): void {
    fetchBookings(1);
}

function goToPage(page: number): void {
    if (page === meta.current_page) {
        return;
    }

    fetchBookings(page);
}

function toggleSortDir(): void {
    filters.sort_dir = filters.sort_dir === "asc" ? "desc" : "asc";
    applyFilters();
}

/**
 * Determine if a booking can be cancelled:
 * - must be active
 * - event must not have started yet
 */
function canCancel(b: BookingListItem): boolean {
    if (b.status !== "active") {
        return false;
    }

    const startsAt = new Date(b.event.starts_at).getTime();
    return startsAt > Date.now();
}

function confirmCancel(b: BookingListItem): void {
    if (!canCancel(b)) {
        return;
    }

    cancelTarget.value = b;
}

/**
 * Call backend cancel endpoint.
 */
async function doCancel(): Promise<void> {
    if (!cancelTarget.value) {
        return;
    }

    isCancelling.value = true;

    try {
        await api.delete(`/bookings/${cancelTarget.value.id}`);
        cancelTarget.value = null;

        // Refresh list after cancel
        await fetchBookings(meta.current_page);
    } catch (e: any) {
        errorMessage.value = e?.response?.data?.message ?? t("common.unknown_error");
    } finally {
        isCancelling.value = false;
    }
}

/**
 * Format date in current locale.
 */
function formatDate(value: string): string {
    try {
        const dt = new Date(value);
        return new Intl.DateTimeFormat(locale.value, {
            dateStyle: "medium",
            timeStyle: "short",
        }).format(dt);
    } catch {
        return value;
    }
}

/**
 * Visible pages around current page.
 */
const visiblePages = computed(() => {
    const pages: number[] = [];
    const start = Math.max(1, meta.current_page - 2);
    const end = Math.min(meta.last_page, meta.current_page + 2);

    for (let p = start; p <= end; p++) {
        pages.push(p);
    }

    return pages;
});

onMounted(() => {
    fetchBookings();
});
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
