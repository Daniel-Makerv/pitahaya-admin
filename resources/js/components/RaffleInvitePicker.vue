<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';

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
    typeForms: { id: number; name: string }[];
}>();

const emit = defineEmits<{ (e: 'close'): void }>();

// ---------- Filtros ----------
const search = ref('');
const typeFormId = ref<number | ''>('');
const dateFrom = ref('');
const dateTo = ref('');
const showAll = ref(false); // "Todo" = sin filtro de fechas
const hideInvited = ref(true);

const toInputDate = (d: Date) => {
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
};

type Preset = 'today' | '7d' | 'month' | 'year';
const activePreset = ref<Preset | null>('year');

const setRange = (preset: Preset) => {
    const today = new Date();
    const start = new Date(today);
    if (preset === '7d') start.setDate(today.getDate() - 6);
    if (preset === 'month') start.setDate(1);
    if (preset === 'year') start.setFullYear(today.getFullYear() - 1);
    dateFrom.value = toInputDate(start);
    dateTo.value = toInputDate(today);
    showAll.value = false;
    activePreset.value = preset;
    load();
};

const showAllDates = () => {
    dateFrom.value = '';
    dateTo.value = '';
    showAll.value = true;
    activePreset.value = null;
    load();
};

const onDateChange = () => {
    showAll.value = false;
    activePreset.value = null;
    load();
};

const hasFilters = computed(
    () =>
        !!search.value.trim() ||
        !!typeFormId.value ||
        !hideInvited.value ||
        activePreset.value !== 'year',
);

const resetFilters = () => {
    search.value = '';
    typeFormId.value = '';
    hideInvited.value = true;
    setRange('year'); // igual que la vista de formularios: último año
};

// ---------- Datos ----------
const candidates = ref<Candidate[]>([]);
const total = ref(0);
const availableTotal = ref(0);
const limit = ref(200);
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
        const params = new URLSearchParams();
        if (search.value.trim()) params.append('search', search.value.trim());
        if (typeFormId.value)
            params.append('type_form_id', String(typeFormId.value));
        if (!showAll.value) {
            if (dateFrom.value) params.append('from', dateFrom.value);
            if (dateTo.value) params.append('to', dateTo.value);
        }
        if (hideInvited.value) params.append('hide_invited', '1');

        const response = await fetch(
            `/raffles/${props.raffleId}/candidates?${params}`,
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            },
        );
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const json = await response.json();

        if (current !== requestId) return;
        candidates.value = json.data;
        total.value = json.total ?? json.data.length;
        availableTotal.value = json.available ?? 0;
        limit.value = json.limit ?? 200;

        // La selección se conserva solo para los que siguen en la lista
        const visibleIds = new Set(
            json.data
                .filter((c: Candidate) => !c.invited)
                .map((c: Candidate) => c.id),
        );
        selected.value = selected.value.filter((id) => visibleIds.has(id));
    } catch (e) {
        if (current === requestId)
            error.value = 'No se pudieron cargar los registros.';
    } finally {
        if (current === requestId) loading.value = false;
    }
};

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(load, 300);
});
watch([typeFormId, hideInvited], () => load());

watch(
    () => props.show,
    async (open) => {
        if (!open) return;
        selected.value = [];
        candidates.value = [];
        search.value = '';
        typeFormId.value = '';
        hideInvited.value = true;
        clearTimeout(searchTimer);
        setRange('year');
        await nextTick();
        searchInput.value?.focus();
    },
);

// ---------- Selección ----------
const available = computed(() => candidates.value.filter((c) => !c.invited));
const allSelected = computed(
    () =>
        available.value.length > 0 &&
        available.value.every((c) => selected.value.includes(c.id)),
);
const someSelected = computed(
    () => selected.value.length > 0 && !allSelected.value,
);
const moreThanShown = computed(() => total.value > candidates.value.length);

const toggle = (candidate: Candidate) => {
    if (candidate.invited) return;
    selected.value = selected.value.includes(candidate.id)
        ? selected.value.filter((id) => id !== candidate.id)
        : [...selected.value, candidate.id];
};

const toggleAll = () => {
    selected.value = allSelected.value ? [] : available.value.map((c) => c.id);
};

// ---------- Envío ----------
const close = () => {
    if (!sending.value) emit('close');
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
            onSuccess: () => emit('close'),
            onError: (errors) =>
                (error.value =
                    Object.values(errors)[0] ??
                    'No se pudieron enviar las invitaciones.'),
        },
    );
};

const onKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.show) close();
};
onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));

const presetClass = (active: boolean) =>
    active
        ? 'border-blue-600 bg-blue-600 text-white'
        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50';
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
                    aria-labelledby="invite-title"
                    class="relative flex max-h-[90vh] w-full max-w-3xl flex-col rounded-xl bg-white shadow-xl ring-1 ring-slate-200"
                >
                    <!-- Encabezado + filtros -->
                    <div
                        class="space-y-4 border-b border-slate-100 px-6 pt-6 pb-4"
                    >
                        <div>
                            <h2
                                id="invite-title"
                                class="text-lg font-semibold text-slate-900"
                            >
                                Agregar a la rifa
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Registros de los formularios que tienen correo.
                                Se les enviará la invitación por email.
                            </p>
                        </div>

                        <div
                            class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_220px]"
                        >
                            <div class="relative">
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
                                    placeholder="Buscar por nombre, correo, teléfono..."
                                    class="w-full rounded-lg border border-slate-200 py-2 pr-4 pl-9 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                />
                            </div>
                            <select
                                v-model="typeFormId"
                                aria-label="Área de interés"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                            >
                                <option value="">Todas las áreas</option>
                                <option
                                    v-for="t in typeForms"
                                    :key="t.id"
                                    :value="t.id"
                                >
                                    {{ t.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Rango de fechas (igual que en Formularios) -->
                        <div class="flex flex-wrap items-end gap-2">
                            <div>
                                <label
                                    for="invite-from"
                                    class="mb-1 block text-xs font-medium text-slate-500"
                                    >Desde</label
                                >
                                <input
                                    id="invite-from"
                                    v-model="dateFrom"
                                    type="date"
                                    :max="dateTo || undefined"
                                    @change="onDateChange"
                                    class="rounded-lg border border-slate-200 px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                />
                            </div>
                            <div>
                                <label
                                    for="invite-to"
                                    class="mb-1 block text-xs font-medium text-slate-500"
                                    >Hasta</label
                                >
                                <input
                                    id="invite-to"
                                    v-model="dateTo"
                                    type="date"
                                    :min="dateFrom || undefined"
                                    @change="onDateChange"
                                    class="rounded-lg border border-slate-200 px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                />
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <button
                                    type="button"
                                    class="rounded-lg border px-2.5 py-1.5 text-xs font-medium transition-colors"
                                    :class="
                                        presetClass(activePreset === 'today')
                                    "
                                    @click="setRange('today')"
                                >
                                    Hoy
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg border px-2.5 py-1.5 text-xs font-medium transition-colors"
                                    :class="presetClass(activePreset === '7d')"
                                    @click="setRange('7d')"
                                >
                                    7 días
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg border px-2.5 py-1.5 text-xs font-medium transition-colors"
                                    :class="
                                        presetClass(activePreset === 'month')
                                    "
                                    @click="setRange('month')"
                                >
                                    Este mes
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg border px-2.5 py-1.5 text-xs font-medium transition-colors"
                                    :class="
                                        presetClass(activePreset === 'year')
                                    "
                                    @click="setRange('year')"
                                >
                                    Último año
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg border px-2.5 py-1.5 text-xs font-medium transition-colors"
                                    :class="presetClass(showAll)"
                                    @click="showAllDates"
                                >
                                    Todo
                                </button>
                            </div>
                        </div>

                        <div
                            class="flex flex-wrap items-center justify-between gap-2 text-xs"
                        >
                            <label
                                class="inline-flex cursor-pointer items-center gap-2 text-slate-600"
                            >
                                <input
                                    v-model="hideInvited"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                />
                                Ocultar los que ya están invitados
                            </label>
                            <button
                                v-if="hasFilters"
                                type="button"
                                @click="resetFilters"
                                class="font-semibold text-slate-500 hover:text-slate-700"
                            >
                                Restablecer filtros
                            </button>
                        </div>
                    </div>

                    <!-- Seleccionar todos -->
                    <div
                        class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/60 px-6 py-2.5"
                    >
                        <label
                            class="inline-flex items-center gap-2.5 text-sm font-medium text-slate-700"
                            :class="
                                available.length
                                    ? 'cursor-pointer'
                                    : 'cursor-not-allowed opacity-50'
                            "
                        >
                            <input
                                type="checkbox"
                                :checked="allSelected"
                                :indeterminate="someSelected"
                                :disabled="!available.length"
                                @change="toggleAll"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            Seleccionar todos
                            <span class="font-normal text-slate-400"
                                >({{ available.length }})</span
                            >
                        </label>
                        <span class="text-xs text-slate-500">
                            <template v-if="loading">Buscando...</template>
                            <template v-else>
                                {{ total.toLocaleString('es-MX') }} con correo
                                <template v-if="!hideInvited">
                                    ·
                                    {{
                                        availableTotal.toLocaleString('es-MX')
                                    }}
                                    sin invitar</template
                                >
                            </template>
                        </span>
                    </div>

                    <p
                        v-if="moreThanShown"
                        class="border-b border-amber-100 bg-amber-50 px-6 py-2 text-xs text-amber-800"
                    >
                        Se muestran los {{ limit }} más recientes de
                        {{ total.toLocaleString('es-MX') }}. Envía a estos y
                        vuelve a abrir el modal para seguir con los demás, o
                        acota con los filtros.
                    </p>

                    <!-- Lista -->
                    <div class="min-h-0 flex-1 overflow-y-auto px-6 py-4">
                        <p
                            v-if="loading && !candidates.length"
                            class="py-10 text-center text-sm text-slate-500"
                        >
                            Cargando registros...
                        </p>
                        <p
                            v-else-if="!candidates.length"
                            class="py-10 text-center text-sm text-slate-500"
                        >
                            Ningún registro con correo coincide con los filtros.
                        </p>

                        <ul
                            v-else
                            class="space-y-2"
                            :class="{ 'opacity-60': loading }"
                        >
                            <li
                                v-for="candidate in candidates"
                                :key="candidate.id"
                            >
                                <button
                                    type="button"
                                    @click="toggle(candidate)"
                                    :disabled="candidate.invited"
                                    class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left transition-colors"
                                    :class="
                                        candidate.invited
                                            ? 'cursor-not-allowed border-slate-100 bg-slate-50 opacity-60'
                                            : selected.includes(candidate.id)
                                              ? 'border-blue-500 bg-blue-50/60 ring-1 ring-blue-500'
                                              : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'
                                    "
                                >
                                    <span
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded border"
                                        :class="
                                            selected.includes(candidate.id)
                                                ? 'border-blue-600 bg-blue-600 text-white'
                                                : 'border-slate-300 bg-white'
                                        "
                                    >
                                        <svg
                                            v-if="
                                                selected.includes(candidate.id)
                                            "
                                            width="12"
                                            height="12"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="3"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span
                                            class="block truncate text-sm font-medium text-slate-900"
                                            >{{ candidate.name }}</span
                                        >
                                        <span
                                            class="block truncate text-xs text-slate-500"
                                        >
                                            {{ candidate.email
                                            }}<template v-if="candidate.phone">
                                                ·
                                                {{ candidate.phone }}</template
                                            >
                                        </span>
                                    </span>
                                    <span
                                        class="hidden shrink-0 text-right sm:block"
                                    >
                                        <span
                                            class="block text-xs font-medium text-slate-600"
                                            >{{
                                                candidate.type || 'Sin área'
                                            }}</span
                                        >
                                        <span
                                            class="block text-[11px] text-slate-400"
                                            >{{ candidate.date }}</span
                                        >
                                    </span>
                                    <span
                                        v-if="candidate.invited"
                                        class="shrink-0 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-600/20 ring-inset"
                                    >
                                        Ya invitado
                                    </span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Pie -->
                    <div
                        class="flex items-center justify-between gap-3 rounded-b-xl border-t border-slate-100 bg-slate-50/60 px-6 py-4"
                    >
                        <p
                            class="text-xs"
                            :class="error ? 'text-red-600' : 'text-slate-500'"
                        >
                            {{
                                error ??
                                `${selected.length} seleccionado${selected.length === 1 ? '' : 's'}`
                            }}
                        </p>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="close"
                                :disabled="sending"
                                class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                @click="send"
                                :disabled="!selected.length || sending"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{
                                    sending
                                        ? 'Enviando...'
                                        : `${selected.length === 1 ? 'Enviar invitación' : 'Enviar invitaciones'}${selected.length ? ` (${selected.length})` : ''}`
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
