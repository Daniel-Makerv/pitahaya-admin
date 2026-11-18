<script setup lang="ts">
import RaffleAnswersModal, {
    type AnswerSection,
} from '@/components/RaffleAnswersModal.vue';
import RaffleDrawModal from '@/components/RaffleDrawModal.vue';
import RaffleFormModal from '@/components/RaffleFormModal.vue';
import RaffleInvitePicker from '@/components/RaffleInvitePicker.vue';
import RaffleManualInviteModal from '@/components/RaffleManualInviteModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Winner {
    id: number;
    place: number;
    prize: string | null;
    name: string;
    email: string;
    number: string;
    distance: number;
}

interface Raffle {
    id: number;
    name: string;
    description: string | null;
    prize: string | null;
    prizes: string[];
    digits: number;
    draw_date: string | null;
    draw_date_text: string | null;
    status: 'open' | 'drawn';
    lottery_draw: string | null;
    lottery_number: string | null;
    target_number: string | null;
    drawn_at: string | null;
    winners: Winner[];
    public_url: string | null;
}

interface Participant {
    id: number;
    name: string;
    email: string | null;
    source: 'form' | 'manual' | 'open';
    status: 'invited' | 'confirmed';
    number: string | null;
    place: number | null;
    step: number;
    score: number | null;
    priority: 'A' | 'B' | 'C' | null;
    data: Record<string, unknown>;
    answers: AnswerSection[];
    invited_at: string | null;
    confirmed_at: string | null;
}

const props = defineProps<{
    raffle: Raffle;
    participants: Participant[];
    typeForms: { id: number; name: string }[];
}>();

const page = usePage();
// ---------- Link público (registro sin correo) ----------
const copied = ref(false);
const copyPublicUrl = async () => {
    if (!props.raffle.public_url) return;
    try {
        await navigator.clipboard.writeText(props.raffle.public_url);
    } catch {
        const input = document.getElementById(
            'public-url',
        ) as HTMLInputElement | null;
        input?.select();
        document.execCommand('copy');
    }
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
};

const shareWhatsApp = computed(() =>
    props.raffle.public_url
        ? `https://wa.me/?text=${encodeURIComponent(`Regístrese y participe en la rifa de PITAMEX: ${props.raffle.public_url}`)}`
        : '#',
);

const sourceLabel = (source: Participant['source']) =>
    ({ form: null, manual: 'Agregado a mano', open: 'Registro por link' })[
        source
    ] ?? null;

const flashSuccess = computed(
    () => (page.props as any).flash?.success as string | undefined,
);
const pageErrors = computed(
    () => Object.values((page.props as any).errors ?? {}) as string[],
);

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Rifas', href: '/raffles' },
    { title: props.raffle.name, href: `/raffles/${props.raffle.id}` },
]);

const isOpen = computed(() => props.raffle.status === 'open');

// ---------- Contadores ----------
const confirmed = computed(() =>
    props.participants.filter((p) => p.status === 'confirmed'),
);
const pending = computed(() =>
    props.participants.filter((p) => p.status === 'invited'),
);
const capacity = computed(() => 10 ** props.raffle.digits);

// ---------- Pestañas y búsqueda ----------
type Tab = 'confirmed' | 'invited' | 'all';
const tab = ref<Tab>('confirmed');
const search = ref('');
const priorityFilter = ref<'A' | 'B' | 'C' | null>(null);

const priorityCount = (p: 'A' | 'B' | 'C') =>
    confirmed.value.filter((x) => x.priority === p).length;

const visible = computed(() => {
    let base =
        tab.value === 'confirmed'
            ? confirmed.value
            : tab.value === 'invited'
              ? pending.value
              : props.participants;
    if (priorityFilter.value)
        base = base.filter((p) => p.priority === priorityFilter.value);
    const q = search.value.trim().toLowerCase();
    if (!q) return base;
    return base.filter(
        (p) =>
            p.name.toLowerCase().includes(q) ||
            (p.email ?? '').toLowerCase().includes(q) ||
            (p.number ?? '').includes(q),
    );
});

// ---------- Modales ----------
const showEdit = ref(false);
const showInvite = ref(false);
const showManualInvite = ref(false);
const showDraw = ref(false);

// ---------- Ver respuestas ----------
const viewing = ref<Participant | null>(null);
const hasAnswers = (p: Participant) =>
    p.answers?.some((s) => s.answers.length > 0);

// ---------- Acciones por participante ----------
const busyId = ref<number | null>(null);

const resend = (p: Participant) => {
    busyId.value = p.id;
    router.post(
        `/raffles/${props.raffle.id}/participants/${p.id}/resend`,
        {},
        {
            preserveScroll: true,
            onFinish: () => (busyId.value = null),
        },
    );
};

const remove = (p: Participant) => {
    if (
        !confirm(
            `¿Quitar a ${p.name} de la rifa?${p.number ? ` Su número ${p.number} quedará libre.` : ''}`,
        )
    )
        return;
    busyId.value = p.id;
    router.delete(`/raffles/${props.raffle.id}/participants/${p.id}`, {
        preserveScroll: true,
        onFinish: () => (busyId.value = null),
    });
};

const deleteRaffle = () => {
    if (
        !confirm(
            `¿Eliminar la rifa "${props.raffle.name}" y todos sus participantes? Esta acción no se puede deshacer.`,
        )
    )
        return;
    router.delete(`/raffles/${props.raffle.id}`);
};

// Resumen de lo que llenó: teléfono y municipio/estado
const extraData = (p: Participant) => {
    const d = (p.data ?? {}) as Record<string, any>;
    const place = [d.municipio, d.estado].filter(Boolean).join(', ');
    return [d.telefono, place].filter(Boolean) as string[];
};

const priorityClass = (priority: string | null) =>
    ({
        A: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        B: 'bg-amber-50 text-amber-700 ring-amber-600/20',
        C: 'bg-slate-100 text-slate-600 ring-slate-500/20',
    })[priority ?? ''] ?? '';

const placeLabel = (place: number) =>
    ['1er lugar', '2º lugar', '3er lugar'][place - 1] ?? `${place}º lugar`;
</script>

<template>
    <Head :title="raffle.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex min-h-screen flex-1 flex-col gap-6 bg-[#F8FAFC] p-6">
            <Link
                href="/raffles"
                class="inline-flex w-fit items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="14"
                    height="14"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2.5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
                Volver a rifas
            </Link>

            <!-- Mensajes -->
            <div
                v-if="flashSuccess"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ flashSuccess }}
            </div>
            <div
                v-if="
                    pageErrors.length &&
                    !showDraw &&
                    !showInvite &&
                    !showManualInvite
                "
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
            >
                {{ pageErrors[0] }}
            </div>

            <!-- Encabezado -->
            <div
                class="rounded-xl border border-l-4 border-slate-200 bg-white p-6 shadow-sm"
                :class="isOpen ? 'border-l-blue-600' : 'border-l-emerald-600'"
            >
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
                >
                    <div class="min-w-0">
                        <span
                            class="text-[10px] font-bold tracking-wider uppercase"
                            :class="
                                isOpen ? 'text-blue-600' : 'text-emerald-600'
                            "
                        >
                            {{ isOpen ? 'Rifa abierta' : 'Rifa sorteada' }}
                        </span>
                        <h1 class="mt-0.5 text-xl font-bold text-slate-900">
                            {{ raffle.name }}
                        </h1>
                        <ol
                            v-if="raffle.prizes.length"
                            class="mt-2 flex flex-wrap gap-2"
                        >
                            <li
                                v-for="(prize, i) in raffle.prizes"
                                :key="i"
                                class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700"
                            >
                                <span class="font-bold text-slate-900"
                                    >{{ i + 1 }}º</span
                                >
                                {{ prize }}
                            </li>
                        </ol>
                        <p
                            v-if="raffle.description"
                            class="mt-1 max-w-2xl text-sm text-slate-500"
                        >
                            {{ raffle.description }}
                        </p>
                        <p class="mt-2 text-xs text-slate-400">
                            Números de {{ raffle.digits }} cifras
                            <template v-if="raffle.draw_date_text">
                                · Sorteo el
                                {{ raffle.draw_date_text }}</template
                            >
                        </p>
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-2">
                        <button
                            type="button"
                            @click="showEdit = true"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
                        >
                            Editar
                        </button>
                        <button
                            type="button"
                            @click="deleteRaffle"
                            class="rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-600 shadow-sm transition-colors hover:bg-red-50"
                        >
                            Eliminar
                        </button>
                        <template v-if="isOpen">
                            <button
                                type="button"
                                @click="showManualInvite = true"
                                class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm transition-colors hover:bg-blue-50"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    />
                                </svg>
                                Agregar manualmente
                            </button>
                            <button
                                type="button"
                                @click="showInvite = true"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>
                                Agregar a la rifa
                            </button>
                            <button
                                type="button"
                                @click="showDraw = true"
                                :disabled="!confirmed.length"
                                :title="
                                    confirmed.length
                                        ? ''
                                        : 'Necesitas al menos un participante confirmado'
                                "
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Sortear
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Ganadores -->
            <div
                v-if="raffle.winners.length"
                class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-6 shadow-sm"
            >
                <div
                    class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between"
                >
                    <p
                        class="text-xs font-bold tracking-wider text-emerald-700 uppercase"
                    >
                        Ganadores
                    </p>
                    <p class="text-xs text-slate-500">
                        Premio mayor
                        <span class="font-mono font-semibold text-slate-700">{{
                            raffle.lottery_number
                        }}</span>
                        <template v-if="raffle.lottery_draw">
                            ({{ raffle.lottery_draw }})</template
                        >
                        · número buscado
                        <span class="font-mono font-semibold text-slate-700">{{
                            raffle.target_number
                        }}</span>
                        · sorteada el {{ raffle.drawn_at }}
                    </p>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div
                        v-for="w in raffle.winners"
                        :key="w.id"
                        class="flex items-center gap-4 rounded-xl border border-emerald-100 bg-white p-4"
                    >
                        <div
                            class="flex h-14 w-20 shrink-0 items-center justify-center rounded-lg bg-slate-900 font-mono text-lg font-bold tracking-widest text-white"
                        >
                            {{ w.number }}
                        </div>
                        <div class="min-w-0">
                            <p
                                class="text-[11px] font-bold tracking-wider text-emerald-700 uppercase"
                            >
                                {{ placeLabel(w.place) }}
                            </p>
                            <p class="truncate font-semibold text-slate-900">
                                {{ w.name }}
                            </p>
                            <p
                                v-if="w.prize"
                                class="truncate text-xs text-slate-600"
                            >
                                {{ w.prize }}
                            </p>
                            <p class="text-[11px] text-slate-400">
                                {{
                                    w.distance === 0
                                        ? 'Coincidencia exacta'
                                        : `A ${w.distance.toLocaleString('es-MX')} del número`
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Link público: registro sin correo -->
            <div
                v-if="isOpen && raffle.public_url"
                class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div
                    class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900">
                            Registro sin invitación
                        </p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Para quien no tiene correo o no está en los
                            formularios. Compártelo por WhatsApp o ábrelo en la
                            tablet del stand: llenan el mismo registro y reciben
                            su número al terminar. El correo es opcional.
                        </p>
                    </div>
                    <div
                        class="flex w-full shrink-0 flex-col gap-2 sm:flex-row lg:w-auto"
                    >
                        <input
                            id="public-url"
                            :value="raffle.public_url"
                            readonly
                            @focus="
                                ($event.target as HTMLInputElement).select()
                            "
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-xs text-slate-700 sm:w-72"
                        />
                        <button
                            type="button"
                            @click="copyPublicUrl"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
                        >
                            {{ copied ? '¡Copiado!' : 'Copiar' }}
                        </button>
                        <a
                            :href="shareWhatsApp"
                            target="_blank"
                            rel="noopener"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-center text-sm font-semibold text-emerald-700 shadow-sm transition-colors hover:bg-emerald-50"
                        >
                            WhatsApp
                        </a>
                        <a
                            :href="raffle.public_url"
                            target="_blank"
                            rel="noopener"
                            class="rounded-lg bg-slate-900 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-700"
                        >
                            Abrir
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contadores -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-medium text-slate-500">
                        Confirmados
                    </p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">
                        {{ confirmed.length }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-medium text-slate-500">
                        Pendientes de confirmar
                    </p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">
                        {{ pending.length }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-medium text-slate-500">
                        Invitados en total
                    </p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">
                        {{ participants.length }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-medium text-slate-500">
                        Números disponibles
                    </p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">
                        {{
                            (capacity - confirmed.length).toLocaleString(
                                'es-MX',
                            )
                        }}
                    </p>
                </div>
            </div>

            <!-- Participantes -->
            <div class="flex flex-col gap-4">
                <div
                    class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
                >
                    <div
                        class="inline-flex w-fit rounded-lg bg-slate-100 p-1 text-sm font-semibold"
                    >
                        <button
                            v-for="option in [
                                {
                                    key: 'confirmed',
                                    label: `Confirmados (${confirmed.length})`,
                                },
                                {
                                    key: 'invited',
                                    label: `Pendientes (${pending.length})`,
                                },
                                {
                                    key: 'all',
                                    label: `Todos (${participants.length})`,
                                },
                            ] as const"
                            :key="option.key"
                            type="button"
                            @click="tab = option.key"
                            class="rounded-md px-3 py-1.5 transition-colors"
                            :class="
                                tab === option.key
                                    ? 'bg-white text-slate-900 shadow-sm'
                                    : 'text-slate-500 hover:text-slate-700'
                            "
                        >
                            {{ option.label }}
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <div
                            class="inline-flex rounded-lg bg-slate-100 p-1 text-xs font-semibold"
                        >
                            <button
                                type="button"
                                @click="priorityFilter = null"
                                class="rounded-md px-2.5 py-1 transition-colors"
                                :class="
                                    !priorityFilter
                                        ? 'bg-white text-slate-900 shadow-sm'
                                        : 'text-slate-500 hover:text-slate-700'
                                "
                            >
                                Todas
                            </button>
                            <button
                                v-for="p in ['A', 'B', 'C'] as const"
                                :key="p"
                                type="button"
                                @click="priorityFilter = p"
                                class="rounded-md px-2.5 py-1 transition-colors"
                                :class="
                                    priorityFilter === p
                                        ? 'bg-white text-slate-900 shadow-sm'
                                        : 'text-slate-500 hover:text-slate-700'
                                "
                            >
                                {{ p }} ({{ priorityCount(p) }})
                            </button>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar nombre, correo o número..."
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none sm:w-64"
                        />
                    </div>
                </div>

                <div
                    class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <table class="w-full table-auto border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-slate-200 bg-slate-50/70 text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                <th class="w-[110px] px-6 py-3">Número</th>
                                <th class="px-6 py-3">Participante</th>
                                <th class="px-6 py-3">Prioridad</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3">Fecha</th>
                                <th class="px-6 py-3 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-sm">
                            <tr
                                v-for="p in visible"
                                :key="p.id"
                                class="transition-colors"
                                :class="[
                                    p.place
                                        ? 'bg-emerald-50/70'
                                        : 'hover:bg-slate-50/50',
                                    { 'opacity-50': busyId === p.id },
                                ]"
                            >
                                <td class="px-6 py-3.5">
                                    <span
                                        v-if="p.number"
                                        class="inline-flex rounded-md bg-slate-900 px-2 py-1 font-mono text-xs font-bold tracking-widest text-white"
                                    >
                                        {{ p.number }}
                                    </span>
                                    <span v-else class="text-slate-300">—</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <p class="font-medium text-slate-900">
                                        {{ p.name }}
                                        <span
                                            v-if="p.place"
                                            class="ml-1.5 rounded-full bg-emerald-600 px-2 py-0.5 align-middle text-[10px] font-bold text-white uppercase"
                                            >{{ placeLabel(p.place) }}</span
                                        >
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ p.email || 'Sin correo' }}
                                        <span
                                            v-if="sourceLabel(p.source)"
                                            class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500"
                                            >{{ sourceLabel(p.source) }}</span
                                        >
                                    </p>
                                    <p
                                        v-if="extraData(p).length"
                                        class="mt-0.5 text-xs text-slate-400"
                                    >
                                        {{ extraData(p).join(' · ') }}
                                    </p>
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <span
                                        v-if="p.priority"
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset"
                                        :class="priorityClass(p.priority)"
                                    >
                                        {{ p.priority
                                        }}<span class="font-medium opacity-75"
                                            >· {{ p.score }}%</span
                                        >
                                    </span>
                                    <span v-else class="text-slate-300">—</span>
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <span
                                        v-if="p.status === 'confirmed'"
                                        class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20 ring-inset"
                                        >Confirmado</span
                                    >
                                    <span
                                        v-else
                                        class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-600/20 ring-inset"
                                    >
                                        {{
                                            p.step > 0
                                                ? `Registro paso ${p.step}/6`
                                                : 'Invitado'
                                        }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-3.5 text-xs whitespace-nowrap text-slate-500"
                                >
                                    {{
                                        p.status === 'confirmed'
                                            ? `Confirmó ${p.confirmed_at}`
                                            : `Invitado ${p.invited_at ?? ''}`
                                    }}
                                </td>
                                <td
                                    class="px-6 py-3.5 text-end whitespace-nowrap"
                                >
                                    <button
                                        type="button"
                                        @click="viewing = p"
                                        :disabled="!hasAnswers(p)"
                                        :title="
                                            hasAnswers(p)
                                                ? 'Ver sus respuestas del registro'
                                                : 'Todavía no contesta el registro'
                                        "
                                        class="mr-3 inline-flex items-center gap-1 rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-40"
                                    >
                                        Ver respuestas
                                    </button>
                                    <button
                                        v-if="
                                            isOpen &&
                                            p.status === 'invited' &&
                                            p.email
                                        "
                                        type="button"
                                        @click="resend(p)"
                                        :disabled="busyId === p.id"
                                        class="mr-3 text-xs font-semibold text-blue-600 hover:underline disabled:cursor-wait"
                                    >
                                        Reenviar
                                    </button>
                                    <button
                                        v-if="isOpen"
                                        type="button"
                                        @click="remove(p)"
                                        :disabled="busyId === p.id"
                                        class="text-xs font-semibold text-red-600 hover:underline disabled:cursor-wait"
                                    >
                                        Quitar
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="!visible.length">
                                <td
                                    colspan="6"
                                    class="px-6 py-12 text-center text-sm text-slate-500"
                                >
                                    <template v-if="search"
                                        >Nadie coincide con la
                                        búsqueda.</template
                                    >
                                    <template v-else-if="tab === 'confirmed'"
                                        >Aún nadie ha confirmado su
                                        participación.</template
                                    >
                                    <template v-else-if="tab === 'invited'"
                                        >No hay invitaciones
                                        pendientes.</template
                                    >
                                    <template v-else
                                        >Todavía no invitas a nadie. Usa
                                        «Agregar a la rifa» o «Agregar
                                        manualmente».</template
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <RaffleFormModal
            :show="showEdit"
            :raffle="raffle"
            :lock-digits="confirmed.length > 0"
            @close="showEdit = false"
        />
        <RaffleInvitePicker
            :show="showInvite"
            :raffle-id="raffle.id"
            :type-forms="typeForms"
            @close="showInvite = false"
        />
        <RaffleAnswersModal :participant="viewing" @close="viewing = null" />
        <RaffleManualInviteModal
            :show="showManualInvite"
            :raffle-id="raffle.id"
            @close="showManualInvite = false"
        />
        <RaffleDrawModal
            :show="showDraw"
            :raffle-id="raffle.id"
            :digits="raffle.digits"
            :confirmed-count="confirmed.length"
            @close="showDraw = false"
        />
    </AppLayout>
</template>
