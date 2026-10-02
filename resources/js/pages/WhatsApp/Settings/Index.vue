<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps<{
    metaAppId: string;
    configId: string;
}>();

const status = ref('Sin conectar');
const eventData = ref<any>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'WhatsApp',
        href: '/whatsapp/forms',
    },
    {
        title: 'Configuración',
        href: '/whatsapp/settings',
    },
];

declare global {
    interface Window {
        FB: any;
        fbAsyncInit: () => void;
    }
}

const handleMessage = (event: MessageEvent) => {
    if (
        event.origin !== 'https://www.facebook.com' &&
        event.origin !== 'https://web.facebook.com'
    ) {
        return;
    }

    try {
        const data =
            typeof event.data === 'string'
                ? JSON.parse(event.data)
                : event.data;

        if (data?.type !== 'WA_EMBEDDED_SIGNUP') {
            return;
        }

        console.log('WhatsApp Embedded Signup:', data);

        eventData.value = data;

        if (data.event === 'FINISH') {
            status.value = 'Registro completado';
        }

        if (data.event === 'CANCEL') {
            status.value = 'Registro cancelado';
        }

        if (data.event === 'ERROR') {
            status.value = 'Error en el registro';
        }
    } catch (error) {
        // Ignorar otros mensajes del navegador.
    }
};

const initializeFacebook = () => {
    window.fbAsyncInit = function () {
        window.FB.init({
            appId: props.metaAppId,
            cookie: true,
            xfbml: false,
            version: 'v26.0',
        });

        status.value = 'SDK listo';
    };

    if (document.getElementById('facebook-jssdk')) {
        if (window.FB) {
            window.fbAsyncInit();
        }

        return;
    }

    const script = document.createElement('script');

    script.id = 'facebook-jssdk';
    script.src = 'https://connect.facebook.net/es_LA/sdk.js';

    script.async = true;
    script.defer = true;

    document.head.appendChild(script);
};

const connectWhatsApp = () => {
    if (!window.FB) {
        status.value = 'Facebook SDK no está listo';
        return;
    }

    status.value = 'Abriendo Meta...';

    window.FB.login(
        (response: any) => {
            console.log('Facebook Login response:', response);

            if (response.authResponse?.code) {
                console.log('Authorization code:', response.authResponse.code);

                status.value = 'Autorización recibida';
            }
        },
        {
            config_id: props.configId,
            response_type: 'code',
            override_default_response_type: true,
            extras: {
                setup: {},
                featureType: '',
                sessionInfoVersion: '3',
            },
        },
    );
};

onMounted(() => {
    window.addEventListener('message', handleMessage);

    initializeFacebook();
});

onBeforeUnmount(() => {
    window.removeEventListener('message', handleMessage);
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mx-auto max-w-2xl rounded-xl border bg-white p-6">
                <h1 class="text-xl font-semibold">Configuración de WhatsApp</h1>

                <p class="mt-2 text-sm text-gray-600">
                    Conecta la cuenta de WhatsApp Business con Pitamex.
                </p>

                <div class="mt-6 rounded-lg bg-gray-50 p-4">
                    <p class="text-sm">
                        Estado:
                        <strong>{{ status }}</strong>
                    </p>
                </div>

                <button
                    type="button"
                    class="mt-6 rounded-lg bg-green-600 px-5 py-3 font-medium text-white"
                    @click="connectWhatsApp"
                >
                    Conectar WhatsApp Business
                </button>

                <pre
                    v-if="eventData"
                    class="mt-6 overflow-auto rounded-lg bg-gray-950 p-4 text-xs text-white"
                    >{{ JSON.stringify(eventData, null, 2) }}</pre
                >
            </div>
        </div>
    </AppLayout>
</template>
