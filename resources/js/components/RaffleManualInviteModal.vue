<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    raffleId: number;
}>();

const emit = defineEmits<{ (e: 'close'): void }>();

const emailInput = ref<HTMLInputElement | null>(null);
const lastSent = ref<string | null>(null);

const form = useForm({
    email: '',
    name: '',
});

const focusEmail = async () => {
    await nextTick();
    emailInput.value?.focus();
};

watch(
    () => props.show,
    (open) => {
        if (!open) return;
        form.reset();
        form.clearErrors();
        lastSent.value = null;
        focusEmail();
    },
);

const isValidEmail = (value: string) =>
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());

const close = () => {
    if (!form.processing) emit('close');
};

/** keepOpen: invita y deja el modal listo para el siguiente correo */
const submit = (keepOpen = false) => {
    if (!isValidEmail(form.email) || form.processing) return;

    const sentTo = form.email.trim();

    form.post(`/raffles/${props.raffleId}/invite-manual`, {
        preserveScroll: true,
        onSuccess: () => {
            if (keepOpen) {
                lastSent.value = sentTo;
                form.reset();
                focusEmail();
            } else {
                emit('close');
            }
        },
    });
};

const onKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.show) close();
};
onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));

const inputClass = (error?: string) =>
    [
        'mt-1.5 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-colors focus:outline-none focus:ring-1',
        error
            ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
            : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500',
    ].join(' ');
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <div
                    class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"
                    @click="close"
                />

                <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="manual-invite-title"
                    class="relative w-full max-w-md rounded-xl bg-white shadow-xl ring-1 ring-slate-200"
                >
                    <form @submit.prevent="submit(false)">
                        <div class="px-6 pt-6">
                            <h2
                                id="manual-invite-title"
                                class="text-lg font-semibold text-slate-900"
                            >
                                Agregar manualmente
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Para alguien que no está en los formularios. Se
                                le enviará la misma invitación por correo y
                                completará sus datos en el registro.
                            </p>
                        </div>

                        <div class="space-y-4 px-6 py-5">
                            <div
                                v-if="lastSent"
                                class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800"
                            >
                                Invitación programada para {{ lastSent }}.
                                Puedes agregar otro.
                            </div>

                            <div>
                                <label
                                    for="manual-email"
                                    class="block text-sm font-medium text-slate-700"
                                    >Correo</label
                                >
                                <input
                                    id="manual-email"
                                    ref="emailInput"
                                    v-model="form.email"
                                    type="email"
                                    maxlength="255"
                                    autocomplete="off"
                                    placeholder="productor@correo.com"
                                    :class="inputClass(form.errors.email)"
                                />
                                <p
                                    v-if="form.errors.email"
                                    class="mt-1.5 text-xs text-red-600"
                                >
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="manual-name"
                                    class="block text-sm font-medium text-slate-700"
                                >
                                    Nombre
                                    <span class="font-normal text-slate-400"
                                        >(opcional)</span
                                    >
                                </label>
                                <input
                                    id="manual-name"
                                    v-model="form.name"
                                    type="text"
                                    maxlength="255"
                                    autocomplete="off"
                                    placeholder="Para saludarlo por su nombre en el correo"
                                    :class="inputClass(form.errors.name)"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="mt-1.5 text-xs text-red-600"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex flex-wrap items-center justify-end gap-2 rounded-b-xl border-t border-slate-100 bg-slate-50/60 px-6 py-4"
                        >
                            <button
                                type="button"
                                @click="close"
                                :disabled="form.processing"
                                class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                @click="submit(true)"
                                :disabled="
                                    !isValidEmail(form.email) || form.processing
                                "
                                class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Invitar y agregar otro
                            </button>
                            <button
                                type="submit"
                                :disabled="
                                    !isValidEmail(form.email) || form.processing
                                "
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{
                                    form.processing
                                        ? 'Enviando...'
                                        : 'Enviar invitación'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
