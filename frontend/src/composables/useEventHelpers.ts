// src/composables/useEventHelpers.ts
import { useI18n } from "vue-i18n";

type EventLike = {
    starts_at: string;
    available_seats: number;
    price?: number;
};

export function useEventHelpers() {
    const { t, locale } = useI18n();

    function isPastEvent(startsAt: string | Date): boolean {
        const dt = typeof startsAt === "string" ? new Date(startsAt) : startsAt;
        const now = new Date();
        return dt.getTime() < now.getTime();
    }

    function formatEventDate(value: string): string {
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

    function formatEventPrice(value: number): string {
        return new Intl.NumberFormat(locale.value, {
            style: "currency",
            currency: "HUF",
            maximumFractionDigits: 0,
        }).format(value);
    }

    function seatsLabel(event: EventLike): string {
        if (isPastEvent(event.starts_at)) {
            // ezt vedd fel a fordítások közé: "events.past_event": "Múltbeli esemény"
            return t("events.past_event");
        }

        if (event.available_seats <= 0) {
            return t("events.sold_out");
        }

        return t("events.available_seats", { count: event.available_seats });
    }

    return {
        isPastEvent,
        formatEventDate,
        formatEventPrice,
        seatsLabel,
    };
}
