<template>
    <div class="mx-auto max-w-5xl px-4 py-6">
        <!-- Loading / Error -->
        <div v-if="isLoading" class="text-sm text-gray-600">
            {{ t("common.loading") }}
        </div>

        <div v-else-if="loadErrorMessage" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ loadErrorMessage }}
        </div>

        <div v-else-if="event">
            <!-- Event header -->
            <div class="rounded-2xl border bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex-1">
                        <!-- Title -->
                        <div v-if="!isEditing">
                            <h1 class="text-2xl font-semibold text-gray-900">
                                {{ event.title }}
                            </h1>
                        </div>
                        <div v-else>
                            <label class="block text-xs font-medium text-gray-600">
                                {{ t("admin_events.title") }}
                            </label>
                            <input
                                v-model.trim="editForm.title"
                                type="text"
                                class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black"
                            />
                        </div>

                        <!-- Description -->
                        <div v-if="!isEditing">
                            <p v-if="event.description" class="mt-2 text-gray-700">
                                {{ event.description }}
                            </p>
                        </div>
                        <div v-else class="mt-3">
                            <label class="block text-xs font-medium text-gray-600">
                                {{ t("admin_events.description") }}
                            </label>
                            <textarea
                                v-model.trim="editForm.description"
                                rows="3"
                                class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black"
                            />
                        </div>

                        <!-- Meta -->
                        <div class="mt-4 grid grid-cols-1 gap-2 text-sm text-gray-700 sm:grid-cols-2">
                            <div>
                                <span class="font-medium text-gray-900">{{ t("events.starts_at") }}:</span>
                                <span v-if="!isEditing" class="ml-1">{{ formatEventDate(event.starts_at) }}</span>

                                <input
                                    v-else
                                    v-model="editForm.starts_at"
                                    type="datetime-local"
                                    class="ml-0 mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"
                                />
                            </div>

                            <div>
                                <span class="font-medium text-gray-900">{{ t("events.ends_at") }}:</span>
                                <span v-if="!isEditing" class="ml-1">{{ formatEventDate(event.ends_at) }}</span>

                                <input
                                    v-else
                                    v-model="editForm.ends_at"
                                    type="datetime-local"
                                    class="ml-0 mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"
                                />
                            </div>

                            <div>
                                <span class="font-medium text-gray-900">{{ t("events.location") }}:</span>
                                <span v-if="!isEditing" class="ml-1">{{ event.location }}</span>

                                <input
                                    v-else
                                    v-model.trim="editForm.location"
                                    type="text"
                                    class="ml-0 mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"
                                />
                            </div>

                            <div>
                                <span class="font-medium text-gray-900">{{ t("events.price") }}:</span>
                                <span v-if="!isEditing" class="ml-1">
                                    {{ event.price > 0 ? formatEventPrice(event.price) : t("events.free") }}
                                </span>

                                <input
                                    v-else
                                    v-model.number="editForm.price"
                                    type="number"
                                    min="0"
                                    class="ml-0 mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"
                                />
                            </div>

                            <div>
                                <span class="font-medium text-gray-900">{{ t("events.capacity") }}:</span>
                                <span v-if="!isEditing" class="ml-1">{{ event.capacity }}</span>

                                <input
                                    v-else
                                    v-model.number="editForm.capacity"
                                    type="number"
                                    min="1"
                                    class="ml-0 mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"
                                />
                            </div>

                            <div>
                                <span class="font-medium text-gray-900">{{ t("events.available") }}:</span>
                                <span class="ml-1">{{ event.available_seats }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right controls -->
                    <div class="flex shrink-0 flex-col gap-2 sm:items-end">
                        <!-- Seats badge -->
                        <span
                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                            :class="event.available_seats > 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'"
                        >
                            {{ seatsLabel(event) }}
                        </span>

                        <!-- Book button for authenticated users -->
                        <button
                            v-if="auth.isAuthenticated"
                            type="button"
                            class="rounded-xl bg-black px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50"
                            :disabled="event.available_seats <= 0"
                            @click="openBookingModal"
                        >
                            {{ t("bookings.book_button") }}
                        </button>

                        <!-- Admin edit buttons -->
                        <div v-if="auth.isAdmin" class="mt-1 flex gap-2">
                            <button
                                v-if="!isEditing"
                                type="button"
                                class="rounded-xl border px-3 py-1.5 text-sm font-medium hover:bg-gray-50"
                                @click="startEdit"
                            >
                                {{ t("admin_events.edit_button") }}
                            </button>

                            <button
                                v-else
                                type="button"
                                class="rounded-xl border px-3 py-1.5 text-sm font-medium hover:bg-gray-50"
                                @click="cancelEdit"
                            >
                                {{ t("common.cancel") }}
                            </button>

                            <button
                                v-if="isEditing"
                                type="button"
                                class="rounded-xl bg-black px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50"
                                :disabled="isSaving"
                                @click="saveEdit"
                            >
                                {{ isSaving ? t("common.loading") : t("common.save") }}
                            </button>
                        </div>

                        <div
                            v-if="isEditing && editErrorMessage"
                            class="mt-3 rounded-xl bg-red-50 px-4 py-2 text-sm text-red-700"
                        >
                            {{ editErrorMessage }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin participants datatable -->
            <section v-if="auth.isAdmin" class="mt-8 rounded-2xl border bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ t("admin_participants.title") }}
                    </h2>

                    <div class="flex items-center gap-2">
                        <input
                            v-model.trim="participantsFilters.search"
                            type="text"
                            class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black sm:w-64"
                            :placeholder="t('admin_participants.search_placeholder')"
                            @keyup.enter="applyParticipantsFilters"
                        />

                        <button
                            type="button"
                            class="rounded-xl border px-3 py-2 text-sm font-medium hover:bg-gray-50"
                            @click="applyParticipantsFilters"
                        >
                            {{ t("common.search") }}
                        </button>

                        <button
                            type="button"
                            class="rounded-xl bg-black px-3 py-2 text-sm font-medium text-white hover:bg-gray-800"
                            @click="exportParticipants"
                        >
                            {{ t("admin_participants.export") }}
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b bg-gray-50 text-gray-700">
                                <th class="px-3 py-2">
                                    <button
                                        type="button"
                                        class="flex items-center gap-1 font-semibold"
                                        @click="setParticipantsSort('name')"
                                    >
                                        {{ t("admin_participants.name") }}
                                        <SortIcon
                                            :active="participantsFilters.sort_by === 'name'"
                                            :dir="participantsFilters.sort_dir"
                                        />
                                    </button>
                                </th>
                                <th class="px-3 py-2">
                                    <button
                                        type="button"
                                        class="flex items-center gap-1 font-semibold"
                                        @click="setParticipantsSort('email')"
                                    >
                                        {{ t("admin_participants.email") }}
                                        <SortIcon
                                            :active="participantsFilters.sort_by === 'email'"
                                            :dir="participantsFilters.sort_dir"
                                        />
                                    </button>
                                </th>
                                <th class="px-3 py-2 text-right">
                                    <button
                                        type="button"
                                        class="ml-auto flex items-center gap-1 font-semibold"
                                        @click="setParticipantsSort('seats_booked')"
                                    >
                                        {{ t("admin_participants.seats_booked") }}
                                        <SortIcon
                                            :active="participantsFilters.sort_by === 'seats_booked'"
                                            :dir="participantsFilters.sort_dir"
                                        />
                                    </button>
                                </th>
                                <th class="px-3 py-2">
                                    <button
                                        type="button"
                                        class="flex items-center gap-1 font-semibold"
                                        @click="setParticipantsSort('created_at')"
                                    >
                                        {{ t("admin_participants.booked_at") }}
                                        <SortIcon
                                            :active="participantsFilters.sort_by === 'created_at'"
                                            :dir="participantsFilters.sort_dir"
                                        />
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="participantsLoading">
                                <td colspan="4" class="px-3 py-4 text-center text-gray-600">
                                    {{ t("common.loading") }}
                                </td>
                            </tr>

                            <tr v-else-if="participants.length === 0">
                                <td colspan="4" class="px-3 py-4 text-center text-gray-600">
                                    {{ t("admin_participants.empty") }}
                                </td>
                            </tr>

                            <tr
                                v-else
                                v-for="p in participants"
                                :key="p.booking_id"
                                class="border-b last:border-b-0"
                            >
                                <td class="px-3 py-2">{{ p.name }}</td>
                                <td class="px-3 py-2">{{ p.email }}</td>
                                <td class="px-3 py-2 text-right">{{ p.seats_booked }}</td>
                                <td class="px-3 py-2">{{ formatEventDate(p.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Participants pagination -->
                <div v-if="participantsMeta.last_page > 1" class="mt-4 flex items-center justify-center gap-2">
                    <button
                        type="button"
                        class="rounded-xl border px-3 py-1.5 text-sm hover:bg-gray-50 disabled:opacity-50"
                        :disabled="participantsMeta.current_page <= 1"
                        @click="goToParticipantsPage(participantsMeta.current_page - 1)"
                    >
                        {{ t("common.prev") }}
                    </button>

                    <button
                        v-for="page in participantsVisiblePages"
                        :key="page"
                        type="button"
                        class="rounded-xl border px-3 py-1.5 text-sm hover:bg-gray-50"
                        :class="page === participantsMeta.current_page ? 'bg-black text-white border-black hover:bg-black' : ''"
                        @click="goToParticipantsPage(page)"
                    >
                        {{ page }}
                    </button>

                    <button
                        type="button"
                        class="rounded-xl border px-3 py-1.5 text-sm hover:bg-gray-50 disabled:opacity-50"
                        :disabled="participantsMeta.current_page >= participantsMeta.last_page"
                        @click="goToParticipantsPage(participantsMeta.current_page + 1)"
                    >
                        {{ t("common.next") }}
                    </button>
                </div>
            </section>
        </div>

        <!-- Booking modal -->
        <BookingModal
            v-model:open="isBookingOpen"
            :event="bookableEvent"
            @booked="onBooked"
        />
    </div>
</template>

<script setup lang="ts">
import { computed, defineComponent, onMounted, reactive, ref } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";
import { api } from "@/lib/api";
import { useAuthStore } from "@/stores/auth";
import BookingModal, { type BookableEvent } from "@/components/BookingModal.vue";
import { useEventHelpers } from "@/composables/useEventHelpers";

/**
 * Tiny helper component for sort arrows.
 */
const SortIcon = defineComponent({
    props: {
        active: { type: Boolean, default: false },
        dir: { type: String as () => "asc" | "desc", default: "asc" },
    },
    template: `
        <span v-if="active" class="text-xs">
            <span v-if="dir === 'asc'">↑</span>
            <span v-else>↓</span>
        </span>
    `,
});

type EventShow = {
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

type ParticipantRow = {
    booking_id: number;
    user_id: number;
    name: string;
    email: string;
    seats_booked: number;
    status: string;
    created_at: string;
};

type PaginationMeta = {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
};

const { t, locale } = useI18n();
const route = useRoute();
const auth = useAuthStore();

const {
    formatEventDate,
    formatEventPrice,
    seatsLabel,
} = useEventHelpers();

const event = ref<EventShow | null>(null);
const isLoading = ref(false);

const loadErrorMessage = ref<string | null>(null);
const editErrorMessage = ref<string | null>(null);

const isBookingOpen = ref(false);

/**
 * Computed minimal event data for BookingModal.
 */
const bookableEvent = computed<BookableEvent | null>(() => {
    if (!event.value) {
        return null;
    }

    return {
        id: event.value.id,
        title: event.value.title,
        available_seats: event.value.available_seats,
    };
});

/**
 * Admin edit state.
 */
const isEditing = ref(false);
const isSaving = ref(false);

const editForm = reactive({
    title: "",
    description: "",
    location: "",
    starts_at: "",
    ends_at: "",
    price: 0,
    capacity: 1,
});

/**
 * Participants datatable state (admin only).
 */
const participants = ref<ParticipantRow[]>([]);
const participantsLoading = ref(false);

const participantsFilters = reactive({
    search: "",
    sort_by: "created_at",
    sort_dir: "desc" as "asc" | "desc",
    per_page: 10,
});

const participantsMeta = reactive<PaginationMeta>({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
});

/**
 * Load event details.
 */
async function fetchEvent(): Promise<void> {
    const id = route.params.id;
    if (!id) {
        return;
    }

    isLoading.value = true;
    loadErrorMessage.value = null;

    try {
        const res = await api.get(`/events/${id}`);
        event.value = res.data.event;

        // Initialize edit form from loaded event
        if (event.value) {
            fillEditForm(event.value);
        }

        // Load participants only for admins
        if (auth.isAdmin && event.value) {
            await fetchParticipants(1);
        }
    } catch (e: any) {
        loadErrorMessage.value = e?.response?.data?.message ?? t("common.unknown_error");
    } finally {
        isLoading.value = false;
    }
}

function fillEditForm(e: EventShow): void {
    editForm.title = e.title;
    editForm.description = e.description ?? "";
    editForm.location = e.location;
    editForm.price = e.price;
    editForm.capacity = e.capacity;

    // datetime-local expects "YYYY-MM-DDTHH:mm"
    editForm.starts_at = toDatetimeLocal(e.starts_at);
    editForm.ends_at = toDatetimeLocal(e.ends_at);
}

function toDatetimeLocal(value: string): string {
    const d = new Date(value);
    const pad = (n: number) => String(n).padStart(2, "0");

    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

/**
 * Admin edit handlers.
 */
function startEdit(): void {
    if (!event.value) {
        return;
    }

    fillEditForm(event.value);
    isEditing.value = true;
}

function cancelEdit(): void {
    if (!event.value) {
        return;
    }

    fillEditForm(event.value);
    isEditing.value = false;
}

async function saveEdit(): Promise<void> {
    if (!event.value) {
        return;
    }

    isSaving.value = true;

    try {
        await api.put(`/admin/events/${event.value.id}`, {
            title: editForm.title,
            description: editForm.description || null,
            location: editForm.location,
            starts_at: editForm.starts_at,
            ends_at: editForm.ends_at,
            price: editForm.price,
            capacity: editForm.capacity,
        });

        isEditing.value = false;
        await fetchEvent();
    } catch (e: any) {
        editErrorMessage.value = e?.response?.data?.message ?? t("common.unknown_error");
    } finally {
        isSaving.value = false;
    }
}

/**
 * Booking handlers.
 */
function openBookingModal(): void {
    isBookingOpen.value = true;
}

async function onBooked(): Promise<void> {
    // Refresh event to update available seats
    await fetchEvent();
}

/**
 * Participants datatable fetch.
 */
async function fetchParticipants(page = 1): Promise<void> {
    if (!event.value) {
        return;
    }

    participantsLoading.value = true;

    try {
        const res = await api.get(`/admin/events/${event.value.id}/participants`, {
            params: {
                page,
                per_page: participantsFilters.per_page,
                search: participantsFilters.search || undefined,
                sort_by: participantsFilters.sort_by,
                sort_dir: participantsFilters.sort_dir,
            },
        });

        const rows = res.data.participants?.data ?? res.data.participants ?? [];
        participants.value = rows;

        const m = res.data.meta ?? {};
        participantsMeta.current_page = m.current_page ?? page;
        participantsMeta.per_page = m.per_page ?? participantsFilters.per_page;
        participantsMeta.total = m.total ?? rows.length;
        participantsMeta.last_page = m.last_page ?? 1;
    } finally {
        participantsLoading.value = false;
    }
}

function applyParticipantsFilters(): void {
    fetchParticipants(1);
}

function goToParticipantsPage(page: number): void {
    if (page === participantsMeta.current_page) {
        return;
    }

    fetchParticipants(page);
}

function setParticipantsSort(sortBy: string): void {
    if (participantsFilters.sort_by === sortBy) {
        participantsFilters.sort_dir = participantsFilters.sort_dir === "asc" ? "desc" : "asc";
    } else {
        participantsFilters.sort_by = sortBy;
        participantsFilters.sort_dir = "asc";
    }

    applyParticipantsFilters();
}

/**
 * Export participants as CSV using backend export endpoint.
 */
async function exportParticipants(): Promise<void> {
    if (!event.value) {
        return;
    }

    const res = await api.get(`/admin/events/${event.value.id}/participants/export`, {
        params: {
            search: participantsFilters.search || undefined,
            sort_by: participantsFilters.sort_by,
            sort_dir: participantsFilters.sort_dir,
        },
        responseType: "blob",
    });

    const blob = new Blob([res.data], { type: "text/csv;charset=utf-8" });
    const url = window.URL.createObjectURL(blob);

    const a = document.createElement("a");
    a.href = url;
    a.download = `event-${event.value.id}-participants.csv`;
    document.body.appendChild(a);
    a.click();
    a.remove();

    window.URL.revokeObjectURL(url);
}

/**
 * Participants pagination window.
 */
const participantsVisiblePages = computed(() => {
    const pages: number[] = [];
    const start = Math.max(1, participantsMeta.current_page - 2);
    const end = Math.min(participantsMeta.last_page, participantsMeta.current_page + 2);

    for (let p = start; p <= end; p++) {
        pages.push(p);
    }

    return pages;
});

onMounted(() => {
    fetchEvent();
});
</script>
