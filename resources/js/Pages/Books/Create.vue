<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onBeforeUnmount } from 'vue';
import axios from 'axios';
import { Html5Qrcode, Html5QrcodeSupportedFormats } from "html5-qrcode";
import InputError from '@/Components/InputError.vue';
import { QrCodeIcon } from '@heroicons/vue/24/outline';

const form = useForm({
    title: '',
    subtitle: '',
    author: '',
    isbn: '',
    pages: '',
    format: 'E-Book',
    published_year: '',
    genre: '',
    status: 'Ungelesen',
    started_at: '',
    finished_at: '',
    notes: '',
    cover_image: '',
    cover_file: null,
});

const searching = ref(false);
const searchResults = ref([]);
const isScannerActive = ref(false);
let html5QrCode = null;

// Verfügbare Rückkameras (auf Multi-Lens-Handys z.B. Weitwinkel + Ultra-Weitwinkel/Makro)
const availableCameras = ref([]);
const selectedCameraId = ref(
    typeof window !== 'undefined' ? (localStorage.getItem('preferredCameraId') || '') : ''
);

// Merkt sich den letzten Treffer, um Fehllesungen durch Mehrfachbestätigung
// (derselbe Code muss zweimal hintereinander erkannt werden) herauszufiltern.
let lastCandidate = null;
let lastCandidateCount = 0;

// Listet alle Kameras des Geräts auf (fragt bei Bedarf Kamera-Berechtigung an).
// Wird nur beim ersten Öffnen des Scanners aufgerufen.
const loadCameras = async () => {
    if (availableCameras.value.length > 0) return;
    try {
        const cameras = await Html5Qrcode.getCameras();
        availableCameras.value = cameras;
        if (cameras.length > 0 && !selectedCameraId.value) {
            // Als Startwert eine Rückkamera bevorzugen, falls das Label das hergibt.
            const backCamera = cameras.find(c => /back|rück|environment/i.test(c.label || ''));
            selectedCameraId.value = (backCamera || cameras[cameras.length - 1]).id;
        }
    } catch (err) {
        console.warn("Kameras konnten nicht aufgelistet werden", err);
    }
};

// Wird aufgerufen, wenn der Nutzer im Dropdown eine andere Kamera wählt,
// während der Scanner bereits läuft: Scanner mit neuer Kamera neu starten.
const switchCamera = async () => {
    if (typeof window !== 'undefined') {
        localStorage.setItem('preferredCameraId', selectedCameraId.value);
    }
    if (!isScannerActive.value) return;
    await stopScanner();
    await startScanner();
};

// Prüfziffer einer ISBN-13 (EAN-13) validieren.
const isValidIsbn13 = (code) => {
    if (code.length !== 13) return false;
    let sum = 0;
    for (let i = 0; i < 12; i++) {
        const digit = Number(code[i]);
        sum += (i % 2 === 0) ? digit : digit * 3;
    }
    const checkDigit = (10 - (sum % 10)) % 10;
    return checkDigit === Number(code[12]);
};

// Prüfziffer einer ISBN-10 validieren (X als Prüfziffer erlaubt).
const isValidIsbn10 = (code) => {
    if (code.length !== 10) return false;
    let sum = 0;
    for (let i = 0; i < 9; i++) {
        if (!/\d/.test(code[i])) return false;
        sum += Number(code[i]) * (10 - i);
    }
    const last = code[9].toUpperCase();
    const checkValue = last === 'X' ? 10 : Number(last);
    if (Number.isNaN(checkValue)) return false;
    sum += checkValue;
    return sum % 11 === 0;
};

const isValidIsbn = (code) => isValidIsbn13(code) || isValidIsbn10(code);

const startScanner = async () => {
    console.log("Scanner V5 starting...");
    await loadCameras();
    isScannerActive.value = true;
    lastCandidate = null;
    lastCandidateCount = 0;
    html5QrCode = new Html5Qrcode("reader", {
        // Nur die Formate zulassen, die auf Buchrückseiten tatsächlich vorkommen.
        // Ohne diese Einschränkung versucht html5-qrcode auch QR/Code39/Codabar/etc.
        // zu decodieren, was bei unscharfen Aufnahmen zu falschen Zahlenfolgen führt.
        formatsToSupport: [
            Html5QrcodeSupportedFormats.EAN_13,
            Html5QrcodeSupportedFormats.EAN_8,
        ],
        verbose: false,
    });
    try {
        const config = {
            fps: 10, // niedriger als 20: jedes Frame wird sorgfältiger analysiert
            qrbox: (viewfinderWidth, viewfinderHeight) => {
                // Breites, flaches Fenster passend zur Form eines EAN-13-Barcodes
                const width = Math.min(viewfinderWidth * 0.9, 350);
                const height = Math.min(viewfinderHeight * 0.4, 140);
                return { width, height };
            },
            aspectRatio: 1.777778,
            disableFlip: true,
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true
            },
            // Höhere Auflösung + kontinuierlicher Autofokus, wo vom Gerät unterstützt.
            // WICHTIG: html5-qrcode nutzt, wenn videoConstraints gesetzt ist, NUR diese
            // für den getUserMedia-Aufruf und ignoriert dabei den ersten start()-Parameter
            // (cameraSource) komplett. Die deviceId muss deshalb HIER rein, sonst wählt
            // der Browser einfach seine eigene Standardkamera (z.B. die Frontkamera).
            videoConstraints: {
                ...(selectedCameraId.value
                    ? { deviceId: { exact: selectedCameraId.value } }
                    : { facingMode: "environment" }),
                width: { ideal: 1920 },
                height: { ideal: 1080 },
                advanced: [{ focusMode: "continuous" }]
            }
        };

        // Wird als Fallback verwendet, falls html5-qrcode videoConstraints intern doch
        // nicht wie erwartet priorisiert (Version/Browser-Unterschiede) – hält dieselbe
        // Kamera-Vorgabe konsistent, statt einer widersprüchlichen zweiten Quelle.
        const cameraSource = selectedCameraId.value
            ? { deviceId: { exact: selectedCameraId.value } }
            : { facingMode: "environment" };

        await html5QrCode.start(
            cameraSource,
            config,
            (decodedText) => {
                const cleanCode = decodedText.replace(/[-\s]/g, "");

                // Nur Kandidaten akzeptieren, die wie eine gültige ISBN aussehen.
                if (!isValidIsbn(cleanCode)) {
                    lastCandidate = null;
                    lastCandidateCount = 0;
                    return;
                }

                // Erst nach zweimaliger identischer Erkennung übernehmen,
                // um einmalige Fehllesungen abzufangen.
                if (cleanCode === lastCandidate) {
                    lastCandidateCount++;
                } else {
                    lastCandidate = cleanCode;
                    lastCandidateCount = 1;
                }

                if (lastCandidateCount >= 2) {
                    if (window.navigator.vibrate) {
                        window.navigator.vibrate(100);
                    }
                    form.isbn = cleanCode;
                    stopScanner();
                    searchByIsbn();
                }
            },
            (errorMessage) => {
                // Ignorieren: wird bei jedem Frame ohne Treffer aufgerufen
            }
        );

        // Falls verfügbar, versuchen den Autofokus/Zoom explizit zu setzen
        // (manche Browser wenden 'advanced' aus videoConstraints nicht zuverlässig an).
        try {
            const capabilities = html5QrCode.getRunningTrackCapabilities?.();
            if (capabilities?.focusMode?.includes("continuous")) {
                await html5QrCode.applyVideoConstraints({
                    advanced: [{ focusMode: "continuous" }]
                });
            }
        } catch (focusErr) {
            console.warn("Autofokus konnte nicht gesetzt werden", focusErr);
        }
    } catch (err) {
        console.error("Scanner failed", err);
        alert("Fehler: " + err);
        isScannerActive.value = false;
    }
};

const stopScanner = async () => {
    if (html5QrCode) {
        await html5QrCode.stop();
        html5QrCode = null;
    }
    isScannerActive.value = false;
};

onBeforeUnmount(() => {
    if (html5QrCode) {
        html5QrCode.stop();
    }
});

const searchByIsbn = async () => {
    if (!form.isbn) return;
    searching.value = true;
    try {
        const response = await axios.get(route('books.searchByIsbn'), {
            params: { isbn: form.isbn }
        });
        const data = response.data[`ISBN:${form.isbn}`];
        if (data) {
            form.title = data.title;
            form.author = data.authors ? data.authors.map(a => a.name).join(', ') : '';
            form.pages = data.number_of_pages || '';
            form.published_year = data.publish_date ? String(data.publish_date) : '';
            form.cover_image = data.cover ? data.cover.large : '';
        }
    } catch (error) {
        console.error('Search failed', error);
    } finally {
        searching.value = false;
    }
};

const searchByTitle = async () => {
    if (!form.title) return;
    searching.value = true;
    try {
        const response = await axios.get(route('books.search'), {
            params: { title: form.title }
        });
        searchResults.value = response.data.docs;
    } catch (error) {
        console.error('Search failed', error);
    } finally {
        searching.value = false;
    }
};

const selectBook = (book) => {
    form.title = book.title;
    form.author = book.author_name ? book.author_name.join(', ') : '';
    form.isbn = book.isbn ? book.isbn[0] : '';
    form.pages = book.number_of_pages_median || '';
    form.published_year = book.first_publish_year ? String(book.first_publish_year) : '';
    form.cover_image = book.cover_i ? `https://covers.openlibrary.org/b/id/${book.cover_i}-L.jpg` : '';
    searchResults.value = [];
};

const submit = () => {
    form.post(route('books.store'), {
        onSuccess: () => {
            console.log('Book saved successfully');
        },
        onError: (errors) => {
            console.error('Failed to save book', errors);
        }
    });
};
</script>

<template>
    <Head title="Buch hinzufügen" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-serif leading-tight text-base-content">
                Neues Buch hinzufügen
            </h2>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-base-100 overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <!-- Search Bar -->
                    <div class="mb-8">
                        <label class="label">Schnellsuche (ISBN oder Titel)</label>
                        <div class="flex gap-2">
                            <input v-model="form.isbn" type="text" placeholder="ISBN..." class="input input-bordered flex-1" @keyup.enter="searchByIsbn" />
                            <button @click="searchByIsbn" class="btn btn-primary" :disabled="searching">ISBN Suche</button>
                            <button v-if="!isScannerActive" @click="startScanner" class="btn btn-accent" title="Scan QR/ISBN">
                                <QrCodeIcon class="w-6 h-6" />
                            </button>
                            <button v-else @click="stopScanner" class="btn btn-error">Stop</button>
                        </div>

                        <!-- Scanner Container -->
                        <div v-show="isScannerActive" class="mt-4">
                            <div v-if="availableCameras.length > 1" class="mb-2">
                                <select v-model="selectedCameraId" @change="switchCamera" class="select select-bordered select-sm w-full">
                                    <option v-for="cam in availableCameras" :key="cam.id" :value="cam.id">
                                        {{ cam.label || ('Kamera ' + cam.id) }}
                                    </option>
                                </select>
                                <p class="text-xs text-base-content/50 mt-1">
                                    Stellt eine Kamera nicht scharf, probier eine andere aus der Liste (z.B. "Ultra Wide" oder "Macro").
                                </p>
                            </div>
                            <div id="reader" class="border-2 border-accent rounded overflow-hidden"></div>
                        </div>

                        <div class="flex gap-2 mt-2">
                            <input v-model="form.title" type="text" placeholder="Titel suchen..." class="input input-bordered flex-1" @keyup.enter="searchByTitle" />
                            <button @click="searchByTitle" class="btn btn-secondary" :disabled="searching">Titel Suche</button>
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div v-if="searchResults.length > 0" class="mb-8 p-4 bg-base-200 rounded">
                        <h4 class="font-bold mb-2">Suchergebnisse:</h4>
                        <ul class="space-y-2">
                            <li v-for="book in searchResults" :key="book.key" @click="selectBook(book)" class="cursor-pointer hover:bg-base-300 p-2 rounded text-sm">
                                {{ book.title }} ({{ book.author_name ? book.author_name[0] : 'Unbekannt' }})
                            </li>
                        </ul>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div v-if="form.cover_image" class="flex justify-center mb-4">
                            <img :src="form.cover_image" alt="Cover Preview" class="h-48 shadow-lg" />
                        </div>

                        <div>
                            <label class="label">Titel</label>
                            <input v-model="form.title" type="text" class="input input-bordered w-full" required />
                            <InputError :message="form.errors.title" class="mt-2" />
                        </div>

                        <div>
                            <label class="label">Untertitel</label>
                            <input v-model="form.subtitle" type="text" class="input input-bordered w-full" />
                            <InputError :message="form.errors.subtitle" class="mt-2" />
                        </div>

                        <div>
                            <label class="label">Autor</label>
                            <input v-model="form.author" type="text" class="input input-bordered w-full" />
                            <InputError :message="form.errors.author" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Seiten</label>
                                <input v-model="form.pages" type="number" class="input input-bordered w-full" />
                                <InputError :message="form.errors.pages" class="mt-2" />
                            </div>
                            <div>
                                <label class="label">Status</label>
                                <select v-model="form.status" class="select select-bordered w-full">
                                    <option>Ungelesen</option>
                                    <option>Am lesen</option>
                                    <option>Gelesen</option>
                                    <option>Pausiert</option>
                                    <option>Abgebrochen</option>
                                </select>
                                <InputError :message="form.errors.status" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Format</label>
                                <select v-model="form.format" class="select select-bordered w-full">
                                    <option>Hardcover</option>
                                    <option>Taschenbuch</option>
                                    <option>E-Book</option>
                                    <option>Hörbuch</option>
                                </select>
                                <InputError :message="form.errors.format" class="mt-2" />
                            </div>
                            <div>
                                <label class="label">Erscheinungsjahr</label>
                                <input v-model="form.published_year" type="text" class="input input-bordered w-full" placeholder="z.B. 2023" />
                                <InputError :message="form.errors.published_year" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Gestartet am</label>
                                <input v-model="form.started_at" type="date" class="input input-bordered w-full" />
                                <InputError :message="form.errors.started_at" class="mt-2" />
                            </div>
                            <div>
                                <label class="label">Beendet am</label>
                                <input v-model="form.finished_at" type="date" class="input input-bordered w-full" />
                                <InputError :message="form.errors.finished_at" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <label class="label">Notizen</label>
                            <textarea v-model="form.notes" class="textarea textarea-bordered w-full" rows="3"></textarea>
                            <InputError :message="form.errors.notes" class="mt-2" />
                        </div>

                        <div>
                            <label class="label">Cover</label>
                            <div class="space-y-2">
                                <input v-model="form.cover_image" type="text" placeholder="Cover URL..." class="input input-bordered w-full" />
                                <div class="flex items-center gap-2">
                                    <div class="h-px bg-base-content/10 flex-1"></div>
                                    <span class="text-xs text-base-content/40 uppercase font-bold">oder</span>
                                    <div class="h-px bg-base-content/10 flex-1"></div>
                                </div>
                                <input type="file" @input="form.cover_file = $event.target.files[0]" class="file-input file-input-bordered w-full" accept="image/*" />
                            </div>
                            <InputError :message="form.errors.cover_image" class="mt-2" />
                            <InputError :message="form.errors.cover_file" class="mt-2" />
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="btn btn-primary w-full" :disabled="form.processing">Speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
