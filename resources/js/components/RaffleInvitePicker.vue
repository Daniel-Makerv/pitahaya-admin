<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";

interface Candidate {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    type: string | null;
    date: string | null;
    invited: boolean;
}

const props = defineProps<{
    show: boolean;
    raffleId: number;
}>();

const emit = defineEmits<{ (e: "close"): void }>();

const search = ref("");
const candidates = ref<Candidate[]>([]);
const selected = ref<number[]>([]);
const loading = ref(false);
const sending = ref(false);
const error = ref<string | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);

let requestId = 0;
const load = async () => {
    const current = ++requestId;
    loading.value = true;
    error.value = null;

    try {
        const params = new URLSearchParams({ search: search.value.trim() });
        const response = await fetch(`/raffles/${props.raffleId}/candidates?${params}`, {
            headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
            credentials: "same-origin",
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const json = await response.json();
        if (current === requestId) candidates.value = json.data;
    } catch (e) {
        if (current === requestId) error.value = "No se pudieron cargar los registros.";
    } finally {
        if (current === requestId) loading.value = false;
    }
};

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(load, 300);
});

watch(
    () => props.show,
    async (open) => {
        if (!open) return;
        search.value = "";
        selected.value = [];
        candidates.value = [];
        await load();
        await nextTick();
        searchInput.value?.focus();
    },
);

const available = computed(() => candidates.value.filter((c) => !c.invited));
const allSelected = computed(() => available.value.length > 0 && available.value.every((c) => selected.value.includes(c.id)));

const toggle = (candidate: Candidate) => {
    if (candidate.invited) return;
    selected.value = selected.value.includes(candidate.id)
        ? selected.value.filter((id) => id !== candidate.id)
        : [...selected.value, candidate.id];
};

const toggleAll = () => {
    selected.value = allSelected.value ? [] : available.value.map((c) => c.id);
};

const close = () => {
    if (!sending.value) emit("close");
};

const send = () => {
    if (!selected.value.length || sending.value) return;

    router.post(
        `/raffles/${props.raffleId}/invite`,
        { form_ids: selected.value },
        {
            preserveScroll: true,
            onStart: () => (sending.value = true),
            onFinish: () => (sending.value = false),
            onSuccess: () => emit("close"),
            onError: (errors) => (error.value = Object.values(errors)[0] ?? "No se pudieron enviar las invitaciones."),
        },
    );
};

const onKeydown = (e: KeyboardEvent) => {
    if (e.key === "Escape" && props.show) close();
};
onMounted(() => window.addEventListener("keydown", onKeydown));
onBeforeUnmount(() => window.removeEventListener("keydown", onKeydown));
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]" @click="close" />

                <div role="dialog" aria-modal="true" aria-labelledby="invite-title"
                    class="relative flex max-h-[85vh] w-full max-w-2xl flex-col rounded-xl bg-white shadow-xl ring-1 ring-slate-200">
                    <div class="px-6 pt-6">
                        <h2 id="invite-title" class="text-lg font-semibold text-slate-900">Agregar a la rifa</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Registros de los formularios que tienen correo. Se les enviará la invitación por email.
                        </p>

                        <div class="relative mt-4">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                                    stroke="#64748B" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input ref="searchInput" v-model="search" type="text"
                                placeholder="Buscar por nombre, correo o área de interés..."
                                class="w-full rounded-lg border border-slate-200 py-2 pr-4 pl-9 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none" />
                        </div>

                        <div v-if="available.length" class="mt-3 flex items-center justify-between text-xs">
                            <button type="button" @click="toggleAll" class="font-semibold text-blue-600 hover:text-blue-700">
                                {{ allSelected ? "Quitar selección" : `Seleccionar ${available.length} disponibles` }}
                            </button>
                            <span class="text-slate-400">Se muestran hasta 100 · usa el buscador para encontrar más</span>
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto px-6 py-4">
                        <p v-if="loading && !candidates.length" class="py-10 text-center text-sm text-slate-500">Cargando registros...</p>
                        <p v-else-if="!candidates.length" class="py-10 text-center text-sm text-slate-500">
                            {{ search ? "Ningún registro con correo coincide con la búsqueda." : "No hay registros con correo en los formularios." }}
                        </p>

                        <ul v-else class="space-y-2" :class="{ 'opacity-60': loading }">
                            <li v-for="candidate in candidates" :key="candidate.id">
                                <button type="button" @click="toggle(candidate)" :disabled="candidate.invited"
                                    class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left transition-colors"
                                    :class="candidate.invited
                                        ? 'cursor-not-allowed border-slate-100 bg-slate-50 opacity-60'
                                        : selected.includes(candidate.id)
                                            ? 'border-blue-500 bg-blue-50/60 ring-1 ring-blue-500'
                                            : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'">
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded border"
                                        :class="selected.includes(candidate.id) ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white'">
                                        <svg v-if="selected.includes(candidate.id)" width="12" height="12" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium text-slate-900">{{ candidate.name }}</span>
                                        <span class="block truncate text-xs text-slate-500">
                                            {{ candidate.email }}<template v-if="candidate.phone"> · {{ candidate.phone }}</template>
                                        </span>
                                    </span>
                                    <span class="hidden shrink-0 text-right sm:block">
                                        <span class="block text-xs font-medium text-slate-600">{{ candidate.type || "Sin área" }}</span>
                                        <span class="block text-[11px] text-slate-400">{{ candidate.date }}</span>
                                    </span>
                                    <span v-if="candidate.invited"
                                        class="shrink-0 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-600/20 ring-inset">
                                        Ya invitado
                                    </span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="flex items-center justify-between gap-3 rounded-b-xl border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <p class="text-xs" :class="error ? 'text-red-600' : 'text-slate-500'">
                            {{ error ?? `${selected.length} seleccionado${selected.length === 1 ? "" : "s"}` }}
                        </p>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="close" :disabled="sending"
                                class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100">
                                Cancelar
                            </button>
                            <button type="button" @click="send" :disabled="!selected.length || sending"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                                {{ sending ? "Enviando..." : `Enviar invitación${selected.length === 1 ? "" : "es"}${selected.length ? ` (${selected.length})` : ""}` }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
