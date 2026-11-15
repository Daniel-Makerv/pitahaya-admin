<script setup lang="ts">
import RaffleFormModal from "@/components/RaffleFormModal.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import type { BreadcrumbItem } from "@/types";
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";

interface Raffle {
    id: number;
    name: string;
    prize: string | null;
    digits: number;
    draw_date_text: string | null;
    status: "open" | "drawn";
    invited_count: number;
    confirmed_count: number;
    target_number: string | null;
    winners: { name: string; number: string; place: number }[];
    created_at: string | null;
}

const props = defineProps<{
    raffles: Raffle[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: "Rifas", href: "/raffles" }];

const showCreate = ref(false);
const search = ref("");

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.raffles;
    return props.raffles.filter(
        (r) => r.name.toLowerCase().includes(q) || (r.prize ?? "").toLowerCase().includes(q),
    );
});
</script>

<template>
    <Head title="Rifas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex min-h-screen flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Rifas</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Invita a las personas de los formularios y sortea con la Lotería Nacional.
                    </p>
                </div>
                <button type="button" @click="showCreate = true"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva rifa
                </button>
            </div>

            <div v-if="raffles.length" class="relative w-full sm:max-w-md">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
                        stroke="#64748B" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input v-model="search" type="text" placeholder="Buscar rifa o premio..."
                    class="w-full rounded-lg border border-slate-200 py-2 pr-4 pl-10 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none" />
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
                <table class="w-full table-auto border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/70 text-xs font-semibold tracking-wider text-slate-500 uppercase">
                            <th class="px-6 py-3.5">Rifa</th>
                            <th class="px-6 py-3.5">Estado</th>
                            <th class="px-6 py-3.5 text-center">Confirmados</th>
                            <th class="px-6 py-3.5">Sorteo</th>
                            <th class="px-6 py-3.5">1er lugar</th>
                            <th class="px-6 py-3.5 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-sm">
                        <tr v-for="raffle in filtered" :key="raffle.id" class="transition-colors hover:bg-slate-50/50">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-900">{{ raffle.name }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ raffle.prize ? raffle.prize.split(/\r?\n/)[0] : "Sin premio definido" }}
                                </p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="raffle.status === 'open'"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-700/10 ring-inset">
                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-600" /> Abierta
                                </span>
                                <span v-else
                                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20 ring-inset">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600" /> Sorteada
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="font-semibold text-slate-900">{{ raffle.confirmed_count }}</span>
                                <span class="text-slate-400"> / {{ raffle.invited_count }} invitados</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500">{{ raffle.draw_date_text ?? "—" }}</td>
                            <td class="px-6 py-4">
                                <template v-if="raffle.winners.length">
                                    <p class="font-medium text-slate-900">{{ raffle.winners[0].name }}</p>
                                    <p class="font-mono text-xs text-slate-500">
                                        Núm. {{ raffle.winners[0].number }}
                                        <template v-if="raffle.winners.length > 1"> · +{{ raffle.winners.length - 1 }} lugares</template>
                                    </p>
                                </template>
                                <span v-else class="text-slate-400">—</span>
                            </td>
                            <td class="px-6 py-4 text-end">
                                <Link :href="`/raffles/${raffle.id}`"
                                    class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-blue-600 transition-colors hover:bg-slate-200">
                                    Ver rifa
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </td>
                        </tr>

                        <tr v-if="!filtered.length">
                            <td colspan="6" class="px-6 py-14 text-center text-sm text-slate-500">
                                {{ search ? "Ninguna rifa coincide con la búsqueda." : "Aún no hay rifas. Crea la primera con «Nueva rifa»." }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <RaffleFormModal :show="showCreate" @close="showCreate = false" />
    </AppLayout>
</template>
