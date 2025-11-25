import axios from "axios";
import type { AxiosError, AxiosInstance, AxiosResponse, InternalAxiosRequestConfig } from "axios";
import { useUiStore } from "@/stores/ui";
import { useAuthStore } from "@/stores/auth";

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000";

export const api: AxiosInstance = axios.create({
    baseURL: `${apiBaseUrl}/api`,
    withCredentials: true,
    xsrfCookieName: "XSRF-TOKEN",
    xsrfHeaderName: "X-XSRF-TOKEN",
    headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
    },
});

/**
 * Fetch Sanctum CSRF cookie once per app session.
 */
async function ensureCsrfCookie(): Promise<void> {
    await axios.get(`${apiBaseUrl}/sanctum/csrf-cookie`, {
        withCredentials: true,
    });
}

/**
 * Request interceptor:
 * - start global loader
 * - ensure CSRF cookie for mutating requests
 */
api.interceptors.request.use(
    async (config: InternalAxiosRequestConfig) => {
        const ui = useUiStore();
        ui.start();

        const method = (config.method ?? "get").toLowerCase();
        const isMutating = ["post", "put", "patch", "delete"].includes(method);

        if (isMutating) {
            await ensureCsrfCookie();
            const token = getCookie("XSRF-TOKEN");
            if (token) {
                (config.headers as any)["X-XSRF-TOKEN"] = token;
            }
        }

        return config;
    },
    (error: AxiosError) => {
        const ui = useUiStore();
        ui.done();
        return Promise.reject(error);
    }
);

/**
 * Response interceptor:
 * - stop loader
 * - auto clear auth on 401
 */
api.interceptors.response.use(
    (response: AxiosResponse) => {
        const ui = useUiStore();
        ui.done();
        return response;
    },
    (error: AxiosError) => {
        const ui = useUiStore();
        ui.done();

        if (error.response?.status === 401) {
            const auth = useAuthStore();
            auth.setUser(null);
        }

        return Promise.reject(error);
    }
);

function getCookie(name: string) {
    const m = document.cookie.match(
        new RegExp(
            "(?:^|; )" +
                name.replace(/([.$?*|{}()[\]\\/+^])/g, "\\$1") +
                "=([^;]*)"
        )
    );
    return m?.[1] ? decodeURIComponent(m[1]) : null;
}