<template>
    <div class="mx-auto max-w-5xl px-4 py-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">
                {{ t("events.title") }}
            </h1>

            <div class="flex items-center gap-2">
                <button
                    v-if="auth.isAdmin"
                    type="button"
                    class="rounded-xl bg-black px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
                    @click="isAdminCreateOpen = true"
                >
                    {{ t("admin_events.add_button") }}
                </button>

                <!-- Search -->
                <div class="flex w-full gap-2 sm:w-auto">
                    <input
                        v-model.trim="filters.search"
                        type="text"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black sm:w-72"
                        :placeholder="t('events.search_placeholder')"
                        @keyup.enter="applyFilters"
                    />

                    <button
                        type="button"
                        class="rounded-xl bg-black px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
                        @click="applyFilters"
                    >
                        {{ t("common.search") }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters row -->
        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">
                    {{ t("events.sort_by") }}
                </label>
                <select
                    v-model="filters.sort_by"
                    class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm"
                    @change="applyFilters"
                >
                    <option value="starts_at">{{ t("events.sort.starts_at") }}</option>
                    <option value="title">{{ t("events.sort.title") }}</option>
                    <option value="created_at">{{ t("events.sort.created_at") }}</option>
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

            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">
                    {{ t("events.per_page") }}
                </label>
                <select
                    v-model.number="filters.per_page"
                    class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm"
                    @change="applyFilters"
                >
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                </select>
            </div>
        </div>

        <!-- Empty / Error states -->
        <div v-if="errorMessage" class="mt-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ errorMessage }}
        </div>

        <div v-else-if="isLoading" class="mt-6 text-sm text-gray-600">
            {{ t("common.loading") }}
        </div>

        <div v-else-if="events.length === 0" class="mt-6 text-sm text-gray-600">
            {{ t("events.empty") }}
        </div>

        <!-- Events grid -->
        <div v-else class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <article
                v-for="event in events"
                :key="event.id"
                class="rounded-2xl border bg-white p-4 shadow-sm transition hover:shadow"
            >
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ event.title }}
                    </h2>

                    <span
                        class="shrink-0 rounded-full px-2 py-1 text-xs font-medium"
                        :class="event.available_seats > 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'"
                    >
                        {{ seatsLabel(event) }}
                    </span>
                </div>

                <p v-if="event.description" class="mt-2 line-clamp-3 text-sm text-gray-700">
                    {{ event.description }}
                </p>

                <div class="mt-3 space-y-1 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-800">{{ t("events.starts_at") }}:</span>
                        <span>{{ formatEventDate(event.starts_at) }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-800">{{ t("events.ends_at") }}:</span>
                        <span>{{ formatEventDate(event.ends_at) }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-800">{{ t("events.location") }}:</span>
                        <span>{{ event.location }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-800">{{ t("events.price") }}:</span>
                        <span>
                            {{ event.price > 0 ? formatEventPrice(event.price) : t("events.free") }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button
                        v-if="auth.isAuthenticated"
                        type="button"
                        class="rounded-xl border px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:opacity-50"
                        :disabled="event.available_seats <= 0"
                        @click="openBookingModal(event)"
                    >
                        {{ t("bookings.book_button") }}
                    </button>

                    <router-link
                        :to="`/events/${event.id}`"
                        class="rounded-xl bg-black px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
                    >
                        {{ t("events.details") }}
                    </router-link>
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
    </div>

    <BookingModal
        v-model:open="isBookingOpen"
        :event="selectedEvent"
        @booked="onBooked"
    />

    <AdminEventCreateModal
        v-model:open="isAdminCreateOpen"
        @created="onAdminCreated"
    />
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from "vue";
import { useI18n } from "vue-i18n";
import { api } from "@/lib/api";
import { useAuthStore } from "@/stores/auth";
import BookingModal from "@/components/BookingModal.vue";
import type { BookableEvent } from "@/components/BookingModal.vue";
import AdminEventCreateModal from "@/components/AdminEventCreateModal.vue";
import { useEventHelpers } from "@/composables/useEventHelpers";

const auth = useAuthStore();

const isBookingOpen = ref(false);
const selectedEvent = ref<BookableEvent | null>(null);

const isAdminCreateOpen = ref(false);

function openBookingModal(event: EventListItem): void {
    selectedEvent.value = {
        id: event.id,
        title: event.title,
        available_seats: event.available_seats,
    };
    isBookingOpen.value = true;
}

function onBooked(): void {
    // Refresh list to update available seats
    fetchEvents(meta.current_page);
}

function onAdminCreated(): void {
    fetchEvents(1);
}


type EventListItem = {
    id: number;
    title: string;
    description: string | null;
    location: string;
    starts_at: string;
    ends_at: string;
    price: number;
    capacity: number;
    available_seats: number;
};

type PaginationMeta = {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
};

const { t, locale } = useI18n();

const {
    formatEventDate,
    formatEventPrice,
    seatsLabel,
} = useEventHelpers();

const events = ref<EventListItem[]>([]);
const meta = reactive<PaginationMeta>({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
});

const filters = reactive({
    search: "",
    sort_by: "starts_at",
    sort_dir: "asc" as "asc" | "desc",
    per_page: 10,
});

const isLoading = ref(false);
const errorMessage = ref<string | null>(null);

/**
 * Fetch events list from backend.
 */
async function fetchEvents(page = 1): Promise<void> {
    isLoading.value = true;
    errorMessage.value = null;

    try {
        const res = await api.get("/events", {
            params: {
                page,
                per_page: filters.per_page,
                search: filters.search || undefined,
                sort_by: filters.sort_by,
                sort_dir: filters.sort_dir,
            },
        });

        // Handle both wrapped and non-wrapped collections
        const payloadEvents = res.data.events?.data ?? res.data.events ?? [];
        events.value = payloadEvents;

        const m = res.data.meta ?? {};
        meta.current_page = m.current_page ?? page;
        meta.per_page = m.per_page ?? filters.per_page;
        meta.total = m.total ?? payloadEvents.length;
        meta.last_page = m.last_page ?? 1;
    } catch (e: any) {
        errorMessage.value = e?.response?.data?.message ?? t("common.unknown_error");
    } finally {
        isLoading.value = false;
    }
}

/**
 * Apply filters and reset to first page.
 */
function applyFilters(): void {
    fetchEvents(1);
}

/**
 * Page navigation.
 */
function goToPage(page: number): void {
    if (page === meta.current_page) {
        return;
    }

    fetchEvents(page);
}

/**
 * Toggle sort direction and re-fetch.
 */
function toggleSortDir(): void {
    filters.sort_dir = filters.sort_dir === "asc" ? "desc" : "asc";
    applyFilters();
}

/**
 * Simple visible pages window around current page.
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
    fetchEvents();
});
</script>
