import { Page, PageProps as InertiaPageProps } from '@inertiajs/core';
import { AppPageProps } from '@/types/index';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo: Echo;
    }
}

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {
        $page: Page<InertiaPageProps & AppPageProps>;
    }
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $page: Page<InertiaPageProps & AppPageProps>;
    }
}
