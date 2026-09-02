<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
});
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>


        <div class="">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">


                <!-- Currently Reading -->
                <div v-if="stats.currentBooks.length > 0" class="mb-12">

                    <div v-for="book in stats.currentBooks" :key="book.id"
                        class="relative overflow-hidden shadow-2xl bg-base-300 text-base-content flex flex-col items-center">

                        <!-- Background Blur -->
                        <div class="absolute inset-0 z-0 opacity-40">
                            <img v-if="book.cover_image" :src="book.cover_image"
                                class="w-full h-full object-cover blur scale-110" />
                        </div>
                        <h3 class=" pt-3 text-xs font-bold uppercase tracking-widest text-base-content/60 mb-4 px-2">
                        Am Lesen</h3>
                        <Link :href="route('books.edit', book.id)"
                            class="relative z-10 w-full flex flex-col items-center py-10">
                            <!-- Cover Image -->
                            <div
                                class="w-44 md:w-56 shadow-[0_20px_50px_rgba(0,0,0,0.5)] mb-8 transform transition duration-500 hover:scale-105">
                                <img v-if="book.cover_image" :src="book.cover_image" :alt="book.title"
                                    class="w-full h-auto rounded" />
                                <div v-else
                                    class="w-full aspect-2/3 bg-neutral rounded flex items-center justify-center text-4xl">
                                    📖</div>
                            </div>

                            <!-- Main Info -->
                            <div class="text-center px-4 mb-8">
                                <h4
                                    class="text-sm md:text-base font-medium tracking-[0.2em] uppercase text-base-content/80 mb-2">
                                    {{ book.author }}
                                </h4>
                                <h3 class="text-3xl md:text-5xl font-light leading-tight font-serif">
                                    {{ book.title }}
                                </h3>
                            </div>

                            <!-- Details Table -->
                            <div class="w-full max-w-2xl px-6">
                                <div class="bg-base-100/30 backdrop-blur-md rounded-lg divide-y divide-base-content/10 text-sm md:text-base">
                                    <div class="flex justify-between py-3 px-4">
                                        <span class="text-base-content/50 uppercase tracking-wider text-xs font-bold">Titel</span>
                                        <span class="text-right ml-4">{{ book.title }}</span>
                                    </div>
                                    <div class="flex justify-between py-3 px-4">
                                        <span class="text-base-content/50 uppercase tracking-wider text-xs font-bold">Autor</span>
                                        <span class="text-right ml-4">{{ book.author }}</span>
                                    </div>

                                    <div class="flex justify-between py-3 px-4">
                                        <span class="text-base-content/50 uppercase tracking-wider text-xs font-bold">Erscheinungsjahr</span>
                                        <span class="text-right ml-4">{{ book.published_year || 'Unbekannt' }}</span>
                                    </div>
                                    <div class="flex justify-between py-3 px-4">
                                        <span class="text-base-content/50 uppercase tracking-wider text-xs font-bold">Art</span>
                                        <span class="text-right ml-4">{{ book.format || 'Unbekannt' }}</span>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Recently Added -->
                <div v-if="stats.recentBooks.length > 0" class="mb-12">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-base-content/60 mb-4 px-2">
                        Zuletzt hinzugefügt</h3>
                    <div class="grid grid-cols-3 gap-3">
                        <Link v-for="book in stats.recentBooks" :key="book.id" :href="route('books.edit', book.id)"
                            class="aspect-2/3 overflow-auto  shadow-lg transform transition active:scale-95 bg-base-200 border">
                            <img v-if="book.cover_image" :src="book.cover_image" :alt="book.title"
                                class="w-full h-full object-center" />
                            <div v-else class="w-full h-full flex items-center justify-center text-2xl">📖</div>
                        </Link>
                    </div>
                </div>

                <!-- Stat Cards -->
                <div class="grid grid-cols-2  lg:grid-cols-4 gap-4 mb-8">
                    <div class="stat bg-base-100 shadow rounded-lg p-6">
                        <div class="stat-title text-base-content/60">Bücher insgesamt</div>
                        <div class="stat-value text-primary text-3xl font-bold">{{ stats.totalBooks }}</div>
                    </div>

                    <div class="stat bg-base-100 shadow rounded-lg p-6">
                        <div class="stat-title text-base-content/60">Seiten insgesamt</div>
                        <div class="stat-value text-secondary text-3xl font-bold">{{ stats.totalPages }}</div>
                    </div>

                    <div class="stat bg-base-100 shadow rounded-lg p-6">
                        <div class="stat-title text-base-content/60">Ø Tage pro Buch</div>
                        <div class="stat-value text-accent text-3xl font-bold">{{ stats.avgTimePerBook }}</div>
                    </div>

                    <div class="stat bg-base-100 shadow rounded-lg p-6">
                        <div class="stat-title text-base-content/60">Diesen Monat</div>
                        <div class="stat-value text-info text-3xl font-bold">{{ stats.booksThisMonth }}</div>
                    </div>
                </div>

                <!-- Yearly Stats Table -->
                <div class="bg-base-100 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-bold mb-4 text-base-content">Bücher pro Jahr</h3>
                        <div class="overflow-x-auto">
                            <table class="table w-full text-base-content">
                                <thead>
                                    <tr class="text-base-content/60">
                                        <th>Jahr</th>
                                        <th>Bücher</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in stats.booksByYear" :key="item.year">
                                        <td>{{ item.year }}</td>
                                        <td>{{ item.count }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
