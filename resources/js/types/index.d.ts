export interface Auth {
    user: User;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    flash: {
        success: string | null;
        error: string | null;
        payload: Record<string, unknown> | null;
    };
};

export interface User {
    id: number;
    username: string;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    points: number;
    created_at: string;
    updated_at: string;
}

export interface AppSettings {
    app_name: string;
    app_logo?: string | null;
    app_favicon?: string | null;
}
