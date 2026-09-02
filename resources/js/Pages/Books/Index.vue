<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PencilIcon, TrashIcon, ChevronUpDownIcon, ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    books: Object,
    filters: Object,
});

const sortBy = (field) => {
    let order = 'asc';
    if (props.filters.sort_by === field && props.filters.sort_order === 'asc') {
        order = 'desc';
    }
    router.get(route('books.index'), { sort_by: field, sort_order: order }, { preserveState: true });
};

const getSortIcon = (field) => {
    if (props.filters.sort_by !== field) return '↕';
    return props.filters.sort_order === 'asc' ? '↑' : '↓';
};

const deleteBook = (id) => {
    if (confirm('Bist du sicher, dass du dieses Buch löschen möchtest?')) {
        router.delete(route('books.destroy', id));
    }
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'Gelesen': return 'badge-success';
        case 'Am lesen': return 'badge-info';
        case 'Ungelesen': return 'badge-ghost';
        case 'Pausiert': return 'badge-warning';
        case 'Abgebrochen': return 'badge-error';
        default: return 'badge-ghost';
    }
};
</script>

<template>

    <Head title="Bücherliste" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-serif leading-tight">
                    Meine Bücher
                </h2>
                <Link :href="route('books.create')" class="btn btn-primary btn-sm">
                    Buch hinzufügen
                </Link>
            </div>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class= "overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-1">
                        <div class="overflow-x-auto">
                            <table class="table w-full text-base-content">
                                <thead>
                                    <tr >
                                        <th>Cover</th>
                                        <th @click="sortBy('title')"
                                            class="p-2 min-w-55 cursor-pointer hover:text-primary transition-colors group">
                                            <div class="flex items-center gap-1">
                                                Titel / Autor
                                                <component
                                                    :is="filters.sort_by === 'title' ? (filters.sort_order === 'asc' ? ChevronUpIcon : ChevronDownIcon) : ChevronUpDownIcon"
                                                    class="w-4 h-4 opacity-50 group-hover:opacity-100" />
                                            </div>
                                        </th>

                                        <th class="p-2">Aktionen</th>
                                        <th @click="sortBy('finished_at')"
                                            class="p-2 cursor-pointer hover:text-primary transition-colors group">
                                            <div class="flex items-center gap-1">
                                                Gelesen am
                                                <component
                                                    :is="filters.sort_by === 'finished_at' ? (filters.sort_order === 'asc' ? ChevronUpIcon : ChevronDownIcon) : ChevronUpDownIcon"
                                                    class="w-4 h-4 opacity-50 group-hover:opacity-100" />
                                            </div>
                                        </th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="book in books.data" :key="book.id"
                                        class="hover:bg-base-100">
                                        <td class="p-2">
                                            <div class="w-14  shadow-sm">
                                                <img v-if="book.cover_image" :src="book.cover_image" :alt="book.title"
                                                    class="w-full h-full object-cover rounded" />
                                                <div v-else
                                                    class="bg-base-200 flex items-center justify-center h-full rounded aspect-2/3">
                                                    <span class="text-[10px] text-center p-1 opacity-50">No Cover</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-2">

                                            <div>
                                                <span class="badge badge-xs mr-2"
                                                    :class="getStatusBadgeClass(book.status)">
                                                    {{ book.status }}
                                                </span><span class="text-xs opacity-50">
                                                    {{ book.author }}</span>
                                            </div>
                                            <div class="py-2">
                                                {{ book.title }}</div>

                                        </td>
                                        <td class="p-2">
                                            <div class="flex gap-1">
                                                <Link :href="route('books.edit', book.id)"
                                                    class="btn btn-xs btn-square btn-outline btn-info"
                                                    title="Bearbeiten">
                                                    <PencilIcon class="w-4 h-4" />
                                                </Link>
                                                <button @click="deleteBook(book.id)"
                                                    class="btn btn-xs btn-square btn-outline btn-error" title="Löschen">
                                                    <TrashIcon class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>

                                        <td class="p-2">
                                            <div v-if="book.finished_at" class="text-sm">
                                                {{ new Date(book.finished_at).toLocaleDateString('de-DE') }}
                                            </div>
                                            <div v-else class="text-xs italic opacity-50">Offen</div>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Simple Pagination -->
                        <div v-if="books.links.length > 3" class="mt-6 flex justify-center">
                            <div class="join">
                                <Link v-for="(link, k) in books.links" :key="k" :href="link.url || '#'"
                                    class="join-item btn btn-sm"
                                    :class="{ 'btn-active': link.active, 'btn-disabled': !link.url }"
                                    v-html="link.label" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
