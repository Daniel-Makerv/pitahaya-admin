<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { forms as formsRoute } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

// 👇 eliminar registro
const deletingId = ref<number | null>(null);

const deleteForm = (form: any) => {
    if (
        !confirm(
            `¿Eliminar la encuesta de "${form.name}"? Esta acción no se puede deshacer.`,
        )
    )
        return;

    deletingId.value = form.id;
    router.delete(`/forms/${form.id}`, {
        preserveScroll: true,
        onFinish: () => (deletingId.value = null),
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Formularios', href: formsRoute().url },
];
const showDropdown = ref(false);

const props = defineProps<{
    forms: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    counts: number;
    filters?: {
        search?: string | null;
        type_form_id?: number | null;
        from?: string | null;
        to?: string | null;
        range?: string | null;
    };
}>();

// 👇 estado local del buscador
const search = ref(props.filters?.search ?? '');
const typeFilter = ref<number | null>(props.filters?.type_form_id ?? null);
// El servidor manda el rango por default (último año) o el que se eligió
const dateFrom = ref(props.filters?.from ?? '');
const dateTo = ref(props.filters?.to ?? '');
const showAll = ref(props.filters?.range === 'all');

watch(
    () => props.filters,
    (filters) => {
        dateFrom.value = filters?.from ?? '';
        dateTo.value = filters?.to ?? '';
        showAll.value = filters?.range === 'all';
    },
);

// Todos los filtros juntos (lista y exportables usan los mismos)
const currentFilters = () => {
    const filters: Record<string, string | number> = {};
    if (search.value) filters.search = search.value;
    if (typeFilter.value) filters.type_form_id = typeFilter.value;
    if (showAll.value) {
        filters.range = 'all';
    } else {
        if (dateFrom.value) filters.from = dateFrom.value;
        if (dateTo.value) filters.to = dateTo.value;
    }
    return filters;
};

// Al mover una fecha a mano deja de ser "Todo"
const onDateChange = () => {
    showAll.value = false;
    applyFilters();
};

const applyFilters = () => {
    router.get(formsRoute().url, currentFilters(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const hasDateRange = computed(
    () => !showAll.value && (!!dateFrom.value || !!dateTo.value),
);

const showAllDates = () => {
    showAll.value = true;
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

// Atajos de rango
const toInputDate = (d: Date) => {
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
};

const setRange = (preset: 'today' | '7d' | 'month' | 'year') => {
    const today = new Date();
    const start = new Date(today);
    if (preset === '7d') start.setDate(today.getDate() - 6);
    if (preset === 'month') start.setDate(1);
    if (preset === 'year') start.setFullYear(today.getFullYear() - 1);
    showAll.value = false;
    dateFrom.value = toInputDate(start);
    dateTo.value = toInputDate(today);
    applyFilters();
};

// Exportables: mismos filtros de búsqueda y fechas; el área la elige el menú
const exportParams = (typeId: number | null) => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (showAll.value) {
        params.append('range', 'all');
    } else {
        if (dateFrom.value) params.append('from', dateFrom.value);
        if (dateTo.value) params.append('to', dateTo.value);
    }
    if (typeId) params.append('type_form_id', typeId.toString());
    return params.toString();
};

const exportExcel = (typeId: number | null) => {
    showDropdown.value = false;

    // 👇 Abre la descarga directamente (con búsqueda y rango de fechas)
    window.open(`/forms/export?${exportParams(typeId)}`, '_blank');
};

const filterByType = (typeId: number | null) => {
    typeFilter.value = typeId; // ✅ cambia el estado local inmediatamente
    applyFilters();
};

const exportExcelV2 = () => {
    window.location.href = `/forms/export-v2?${exportParams(typeFilter.value)}`;
};

// 👇 cada vez que cambia el input, mandamos la búsqueda (sin perder área ni fechas)
let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 350);
});
</script>

<template>
    <Head title="Forms" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <div
                    class="flex-column flex flex-wrap items-center justify-between space-y-4 bg-white pb-4 md:flex-row md:space-y-0 dark:bg-gray-900"
                >
                    <div class="mb-4 flex flex-wrap gap-2">
                        <button
                            class="rounded-lg border px-3 py-1.5 text-sm transition"
                            :class="[
                                !typeFilter
                                    ? 'border-blue-600 bg-blue-600 text-white'
                                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                            ]"
                            @click="filterByType(null)"
                        >
                            Todos ({{
                                props.counts.reduce(
                                    (sum, c) => sum + c.total,
                                    0,
                                )
                            }})
                        </button>

                        <button
                            v-for="count in props.counts"
                            :key="count.id"
                            class="rounded-lg border px-3 py-1.5 text-sm transition"
                            :class="[
                                typeFilter == count.id
                                    ? 'border-blue-600 bg-blue-600 text-white'
                                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                            ]"
                            @click="filterByType(count.id)"
                        >
                            {{ count.name }} ({{ count.total }})
                        </button>
                    </div>

                    <div class="relative">
                        <button
                            id="dropdownActionButton"
                            @click="showDropdown = !showDropdown"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-500 hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700"
                            type="button"
                        >
                            Exportar Excel
                            <svg
                                class="ms-2.5 h-2.5 w-2.5"
                                aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 10 6"
                            >
                                <path
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m1 1 4 4 4-4"
                                />
                            </svg>
                        </button>

                        <button
                            class="rounded-lg border px-3 py-1.5 text-sm transition"
                            @click="exportExcelV2"
                        >
                            Exportar datos v2
                        </button>

                        <button
                            class="rounded-lg border px-3 py-1.5 text-sm transition"
                            @click="exportExcelV2"
                        >
                            Formularios de whatsapp
                        </button>

                        <div
                            v-if="showDropdown"
                            class="absolute right-0 z-50 mt-2 w-56 divide-y divide-gray-100 rounded-lg bg-white shadow-sm dark:divide-gray-600 dark:bg-gray-700"
                        >
                            <p class="px-4 py-2 text-xs text-gray-500">
                                <template v-if="hasDateRange">
                                    Del {{ dateFrom || 'inicio' }} al
                                    {{ dateTo || 'hoy' }}
                                </template>
                                <template v-else>Todas las fechas</template>
                            </p>
                            <ul
                                class="py-1 text-sm text-gray-700 dark:text-gray-200"
                            >
                                <li>
                                    <button
                                        class="block w-full px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                                        @click="exportExcel(null)"
                                    >
                                        Todos ({{
                                            props.counts.reduce(
                                                (sum, c) => sum + c.total,
                                                0,
                                            )
                                        }})
                                    </button>
                                </li>
                                <li
                                    v-for="count in props.counts"
                                    :key="count.id"
                                >
                                    <button
                                        class="block w-full px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                                        @click="exportExcel(count.id)"
                                    >
                                        {{ count.name }} ({{ count.total }})
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Rango de fechas -->
                    <div class="flex flex-wrap items-end gap-2">
                        <div>
                            <label
                                for="date-from"
                                class="mb-1 block text-xs font-medium text-gray-500"
                                >Desde</label
                            >
                            <input
                                id="date-from"
                                v-model="dateFrom"
                                type="date"
                                :max="dateTo || undefined"
                                @change="onDateChange"
                                class="rounded-lg border border-gray-300 bg-gray-50 px-2 py-1.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label
                                for="date-to"
                                class="mb-1 block text-xs font-medium text-gray-500"
                                >Hasta</label
                            >
                            <input
                                id="date-to"
                                v-model="dateTo"
                                type="date"
                                :min="dateFrom || undefined"
                                @change="onDateChange"
                                class="rounded-lg border border-gray-300 bg-gray-50 px-2 py-1.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div class="flex gap-1">
                            <button
                                type="button"
                                class="rounded-lg border px-2.5 py-1.5 text-xs text-gray-600 hover:bg-gray-100"
                                @click="setRange('today')"
                            >
                                Hoy
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border px-2.5 py-1.5 text-xs text-gray-600 hover:bg-gray-100"
                                @click="setRange('7d')"
                            >
                                7 días
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border px-2.5 py-1.5 text-xs text-gray-600 hover:bg-gray-100"
                                @click="setRange('month')"
                            >
                                Este mes
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border px-2.5 py-1.5 text-xs text-gray-600 hover:bg-gray-100"
                                @click="setRange('year')"
                            >
                                Último año
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border px-2.5 py-1.5 text-xs transition"
                                :class="
                                    showAll
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'text-gray-600 hover:bg-gray-100'
                                "
                                @click="showAllDates"
                            >
                                Todo
                            </button>
                        </div>
                    </div>

                    <label for="table-search" class="sr-only">Search</label>
                    <div class="relative">
                        <div
                            class="rtl:inset-r-0 pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3"
                        >
                            <svg
                                class="h-4 w-4 text-gray-500 dark:text-gray-400"
                                aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"
                                />
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="table-search-users"
                            v-model="search"
                            class="block w-80 rounded-lg border border-gray-300 bg-gray-50 p-2 ps-10 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                            placeholder="buscar por nombre, área de interés"
                        />
                    </div>
                </div>
                <table
                    class="w-full text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400"
                >
                    <thead
                        class="bg-gray-50 text-xs text-gray-700 uppercase dark:bg-gray-700 dark:text-gray-400"
                    >
                        <tr>
                            <th scope="col" class="p-4">
                                <div class="flex items-center">
                                    <input
                                        id="checkbox-all-search"
                                        type="checkbox"
                                        class="h-4 w-4 rounded-sm border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800"
                                    />
                                    <label
                                        for="checkbox-all-search"
                                        class="sr-only"
                                        >checkbox</label
                                    >
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3">Nombre</th>
                            <th scope="col" class="px-6 py-3">Contacto</th>
                            <th scope="col" class="px-6 py-3">Area interes</th>
                            <th scope="col" class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="form in props.forms.data"
                            :key="form.id"
                            class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-600"
                            :class="{ 'opacity-50': deletingId === form.id }"
                        >
                            <td class="w-4 p-4">
                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded-sm border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800"
                                    />
                                </div>
                            </td>

                            <th
                                scope="row"
                                class="px-6 py-4 font-medium whitespace-nowrap text-gray-900 dark:text-white"
                            >
                                {{ form.name }}
                            </th>

                            <td class="px-6 py-4">
                                {{ JSON.parse(form.form)?.phone_contact }}
                            </td>
                            <td class="px-6 py-4">{{ form.type }}</td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <Link
                                        :href="`/forms/${form.id}/view`"
                                        class="font-medium text-blue-600 hover:underline dark:text-blue-500"
                                    >
                                        Ver
                                    </Link>
                                    <Link
                                        :href="`/forms/${form.id}/edit`"
                                        class="font-medium text-blue-600 hover:underline dark:text-blue-500"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        type="button"
                                        @click="deleteForm(form)"
                                        :disabled="deletingId === form.id"
                                        class="font-medium text-red-600 hover:underline disabled:cursor-wait dark:text-red-500"
                                    >
                                        {{
                                            deletingId === form.id
                                                ? 'Eliminando...'
                                                : 'Eliminar'
                                        }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-4 mb-4 flex justify-center space-x-2">
                    <Link
                        v-for="(link, index) in props.forms.links"
                        :key="index"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        class="rounded px-3 py-1 text-sm"
                        :class="[
                            link.active
                                ? 'bg-blue-600 text-white'
                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                            !link.url && 'cursor-not-allowed opacity-50',
                        ]"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
