<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    quotes: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const allQuotes = ref(props.quotes.data);
const nextUrl = ref(props.quotes.next_page_url);
const loading = ref(false);
const observerTarget = ref(null);

const loadMore = async () => {
    if (loading.value || !nextUrl.value) return;

    loading.value = true;
    try {
        const response = await axios.get(nextUrl.value, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        allQuotes.value = [...allQuotes.value, ...response.data.data];
        nextUrl.value = response.data.next_page_url;
    } catch (error) {
        console.error('Fehler beim Laden weiterer Zitate:', error);
    } finally {
        loading.value = false;
    }
};

let debounceTimer;
const handleSearch = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(route('quotes.index'), { search: search.value }, {
            preserveState: true,
            replace: true,
            onSuccess: (page) => {
                allQuotes.value = page.props.quotes.data;
                nextUrl.value = page.props.quotes.next_page_url;
            }
        });
    }, 300);
};

watch(search, () => {
    handleSearch();
});

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            loadMore();
        }
    }, { threshold: 0.1 });

    if (observerTarget.value) {
        observer.observe(observerTarget.value);
    }
});
</script>

<template>
    <Head title="Zitate" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-serif leading-tight text-base-content">
                Zitate
            </h2>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Search -->
                <div class="mb-6 px-4 sm:px-0">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Zitate, Buchtitel oder Autoren suchen..."
                            class="input input-bordered w-full pl-10"
                        />
                    </div>
                </div>

                <!-- Quotes List -->
                <div v-if="allQuotes.length > 0" class="space-y-6">
                    <div v-for="quote in allQuotes" :key="quote.id" class="card bg-base-100 shadow-sm border border-base-200">
                        <div class="card-body p-6">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                <div class="flex-1">
                                    <p class="text-lg italic font-serif text-base-content/90 leading-relaxed">
                                        &bdquo;{{ quote.content }}&ldquo;
                                    </p>
                                    
                                    <div class="mt-4 flex flex-wrap items-center gap-x-2 text-sm text-base-content/60">
                                        <span v-if="quote.page" class="font-medium">Seite {{ quote.page }}</span>
                                        <span v-if="quote.page" class="opacity-30">&bull;</span>
                                        <span class="font-bold text-primary">{{ quote.book.title }}</span>
                                        <span class="opacity-30">&bull;</span>
                                        <span>{{ quote.book.author }}</span>
                                    </div>
                                </div>
                                
                                <div v-if="quote.book.cover_image" class="hidden md:block shrink-0">
                                    <img :src="quote.book.cover_image" class="h-20 w-14 object-cover rounded shadow-sm" alt="Cover" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else-if="!loading" class="text-center py-12">
                    <div class="text-5xl mb-4">📖</div>
                    <h3 class="text-xl font-bold">Keine Zitate gefunden</h3>
                    <p class="text-base-content/60 mt-2">Füge Zitate hinzu, indem du ein Buch bearbeitest.</p>
                </div>

                <!-- Loading Spinner & Observer Target -->
                <div ref="observerTarget" class="py-8 flex justify-center">
                    <span v-if="loading" class="loading loading-spinner loading-lg text-primary"></span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
