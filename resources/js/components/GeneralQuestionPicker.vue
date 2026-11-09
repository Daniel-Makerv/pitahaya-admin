<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';

interface PreguntaGeneral {
    id: number;
    text: string;
    required: boolean;
    type: string | null;
    options: string[];
    added: boolean;
}

const props = defineProps<{
    show: boolean;
    preguntas: PreguntaGeneral[];
    url: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const search = ref('');
const selected = ref<number[]>([]);
const processing = ref(false);
const error = ref<string | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.preguntas;
    return props.preguntas.filter((p) => p.text.toLowerCase().includes(q));
});

const availableCount = computed(
    () => props.preguntas.filter((p) => !p.added).length,
);

const isSelected = (id: number) => selected.value.includes(id);

// Se respeta el orden en que se van seleccionando
const toggle = (pregunta: PreguntaGeneral) => {
    if (pregunta.added) return;
    selected.value = isSelected(pregunta.id)
        ? selected.value.filter((id) => id !== pregunta.id)
        : [...selected.value, pregunta.id];
};

const positionOf = (id: number) => selected.value.indexOf(id) + 1;

watch(
    () => props.show,
    async (open) => {
        if (!open) return;
        search.value = '';
        selected.value = [];
        error.value = null;
        await nextTick();
        searchInput.value?.focus();
    },
);

const close = () => {
    if (processing.value) return;
    emit('close');
};

const submit = () => {
    if (!selected.value.length || processing.value) return;

    router.post(
        props.url,
        { questions: selected.value },
        {
            preserveScroll: true,
            onStart: () => (processing.value = true),
            onFinish: () => (processing.value = false),
            onError: (errors) =>
                (error.value =
                    Object.values(errors)[0] ??
                    'No se pudieron agregar las preguntas.'),
            onSuccess: () => emit('close'),
        },
    );
};

const onKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.show) close();
};

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));
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
                    aria-labelledby="picker-title"
                    class="relative flex max-h-[85vh] w-full max-w-xl flex-col rounded-xl bg-white shadow-xl ring-1 ring-slate-200"
                >
                    <div class="px-6 pt-6">
                        <h2
                            id="picker-title"
                            class="text-lg font-semibold text-slate-900"
                        >
                            Agregar desde preguntas generales
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Selecciona las que quieras usar. Se agregan al
                            final, en el orden en que las elijas.
                        </p>

                        <div v-if="preguntas.length" class="relative mt-4">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="#64748B"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                            </span>
                            <input
                                ref="searchInput"
                                v-model="search"
                                type="text"
                                placeholder="Buscar pregunta..."
                                class="w-full rounded-lg border border-slate-200 py-2 pr-4 pl-9 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto px-6 py-4">
                        <!-- Sin preguntas generales -->
                        <div
                            v-if="!preguntas.length"
                            class="flex flex-col items-center gap-2 py-10 text-center"
                        >
                            <p class="text-sm font-medium text-slate-600">
                                Aún no hay preguntas generales.
                            </p>
                            <p class="text-xs text-slate-400">
                                Créalas desde la pantalla de cuestionarios con
                                «Nueva Pregunta General».
                            </p>
                            <Link
                                href="/questions"
                                class="mt-2 text-xs font-semibold text-blue-600 hover:text-blue-700"
                                >Ir a cuestionarios
                            </Link>
                        </div>

                        <p
                            v-else-if="!filtered.length"
                            class="py-10 text-center text-sm text-slate-500"
                        >
                            Ninguna pregunta coincide con la búsqueda.
                        </p>

                        <ul v-else class="space-y-2">
                            <li v-for="pregunta in filtered" :key="pregunta.id">
                                <button
                                    type="button"
                                    @click="toggle(pregunta)"
                                    :disabled="pregunta.added"
                                    class="flex w-full items-start gap-3 rounded-lg border px-4 py-3 text-left transition-colors"
                                    :class="
                                        pregunta.added
                                            ? 'cursor-not-allowed border-slate-100 bg-slate-50 opacity-60'
                                            : isSelected(pregunta.id)
                                              ? 'border-blue-500 bg-blue-50/60 ring-1 ring-blue-500'
                                              : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'
                                    "
                                >
                                    <span
                                        class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded border text-[11px] font-bold"
                                        :class="
                                            isSelected(pregunta.id)
                                                ? 'border-blue-600 bg-blue-600 text-white'
                                                : 'border-slate-300 bg-white text-transparent'
                                        "
                                    >
                                        {{
                                            isSelected(pregunta.id)
                                                ? positionOf(pregunta.id)
                                                : ''
                                        }}
                                    </span>

                                    <span class="min-w-0 flex-1">
                                        <span
                                            class="block text-sm font-medium text-slate-900"
                                            >{{ pregunta.text }}</span
                                        >
                                        <span
                                            class="mt-1 flex flex-wrap items-center gap-1.5"
                                        >
                                            <span
                                                class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600"
                                            >
                                                {{
                                                    pregunta.type || 'Sin tipo'
                                                }}
                                            </span>
                                            <span
                                                v-if="!pregunta.required"
                                                class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 ring-1 ring-amber-600/20 ring-inset"
                                                >Opcional</span
                                            >
                                            <span
                                                v-for="option in pregunta.options"
                                                :key="option"
                                                class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[11px] text-slate-500"
                                            >
                                                {{ option }}
                                            </span>
                                        </span>
                                    </span>

                                    <span
                                        v-if="pregunta.added"
                                        class="shrink-0 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-600/20 ring-inset"
                                    >
                                        Ya agregada
                                    </span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div
                        class="flex items-center justify-between gap-3 rounded-b-xl border-t border-slate-100 bg-slate-50/60 px-6 py-4"
                    >
                        <p class="text-xs text-slate-500">
                            <span v-if="error" class="text-red-600">{{
                                error
                            }}</span>
                            <template v-else-if="preguntas.length">
                                {{ selected.length }} seleccionada{{
                                    selected.length === 1 ? '' : 's'
                                }}
                                · {{ availableCount }} disponible{{
                                    availableCount === 1 ? '' : 's'
                                }}
                            </template>
                        </p>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="close"
                                :disabled="processing"
                                class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                @click="submit"
                                :disabled="!selected.length || processing"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg
                                    v-if="processing"
                                    class="h-4 w-4 animate-spin"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        class="opacity-25"
                                    />
                                    <path
                                        d="M22 12a10 10 0 0 0-10-10"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                    />
                                </svg>
                                {{
                                    processing
                                        ? 'Agregando...'
                                        : `Agregar${selected.length ? ` (${selected.length})` : ''}`
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
