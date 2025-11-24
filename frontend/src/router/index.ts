import { createRouter, createWebHistory } from "vue-router";
import type { RouteRecordRaw } from "vue-router";
import { useAuthStore } from "@/stores/auth";

// Lazy-loaded views (create these components later)
const LoginView = () => import("../views/LoginView.vue");
const EventsListView = () => import("../views/EventsListView.vue");
const EventShowView = () => import("../views/EventShowView.vue");
/*const MyBookingsView = () => import("../views/MyBookingsView.vue");
const AdminEventEditView = () => import("../views/admin/AdminEventEditView.vue");*/
const NotFoundView = () => import("../views/NotFoundView.vue");

declare module "vue-router" {
    interface RouteMeta {
        requiresAuth?: boolean;
        guestOnly?: boolean;
        requiresAdmin?: boolean;
    }
}

const routes: RouteRecordRaw[] = [
    {
        path: "/login",
        name: "login",
        component: LoginView,
        meta: { guestOnly: true },
    },
    {
        path: "/",
        redirect: { name: "events.index" },
    },
    {
        path: "/events",
        name: "events.index",
        component: EventsListView,
    },
    {
        path: "/events/:id",
        name: "events.show",
        component: EventShowView,
        props: true,
    },
    /*{
        path: "/my-bookings",
        name: "bookings.mine",
        component: MyBookingsView,
        meta: { requiresAuth: true },
    },

    // Admin example routes (we will implement later)
    {
        path: "/admin/events/:id/edit",
        name: "admin.events.edit",
        component: AdminEventEditView,
        props: true,
        meta: { requiresAuth: true, requiresAdmin: true },
    },*/

    // 404 must be last
    {
        path: "/:pathMatch(.*)*",
        name: "notfound",
        component: NotFoundView,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    // Ensure auth store initialized once before guards
    if (!auth.isInitialized) {
        await auth.init();
    }

    const isAuthed = auth.isAuthenticated;

    // Guest-only routes (e.g., login/register)
    if (to.meta.guestOnly && isAuthed) {
        return { name: "events.index" };
    }

    // Auth-required routes
    if (to.meta.requiresAuth && !isAuthed) {
        return {
            name: "login",
            query: { redirect: to.fullPath },
        };
    }

    // Admin-required routes
    if (to.meta.requiresAdmin && !auth.isAdmin) {
        // If user isn't admin, push them to events list
        return { name: "events.index" };
    }

    return true;
});

export default router;
