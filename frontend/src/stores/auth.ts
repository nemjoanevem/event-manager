import { defineStore } from "pinia";
import { api } from "@/lib/api";

export type User = {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    created_at: string;
    updated_at: string;
};

type AuthState = {
    user: User | null;
    isInitialized: boolean;
};

export const useAuthStore = defineStore("auth", {
    state: (): AuthState => ({
        user: null,
        isInitialized: false,
    }),

    getters: {
        isAuthenticated: (state) => state.user !== null,
        isAdmin: (state) => !!state.user?.is_admin,
    },

    actions: {
        /**
         * Initialize auth state by calling /me
         * (restores session if cookie is valid).
         */
        async init(): Promise<void> {
            try {
                const res = await api.get("/me");
                this.setUser(res.data.user);
            } catch {
                this.setUser(null);
            } finally {
                this.isInitialized = true;
            }
        },

        setUser(user: User | null): void {
            this.user = user;

            if (user) {
                localStorage.setItem("user", JSON.stringify(user));
            } else {
                localStorage.removeItem("user");
            }
        },

        /**
         * Register new user (backend auto logs in).
         */
        async register(payload: { name: string; email: string; password: string; password_confirmation: string }): Promise<User> {
            const res = await api.post("/register", payload);
            this.setUser(res.data.user);
            return res.data.user;
        },

        /**
         * Login and set user.
         */
        async login(payload: { email: string; password: string }): Promise<User> {
            const res = await api.post("/login", payload);
            this.setUser(res.data.user);
            return res.data.user;
        },

        /**
         * Logout and clear user.
         */
        async logout(): Promise<void> {
            await api.post("/logout");
            this.setUser(null);
        },
    },
});
