# Bookstats-Klon Implementierungsplan

Dieses Dokument beschreibt die Schritte zur Erstellung eines persönlichen Bookstats-Klons als Laravel PWA mit Inertia.js und Vue.

## 1. Technisches Setup
- **Stack**: Laravel + Inertia.js + Vue 3
- **CSS**: Tailwind CSS + DaisyUI (für schnelle, kostenlose UI-Komponenten)
- **Authentifizierung**: Laravel Breeze (Inertia-Stack) für einen schnellen Start
- **PWA**: Integration von `vite-plugin-pwa` zur Unterstützung der Offline-Fähigkeit und Installation auf dem S25

## 2. Datenbank-Design (`books` Tabelle)
Basierend auf der `.\Bookstats.csv` werden folgende Felder benötigt:
- `id` (Primary Key)
- `user_id` (Fremdschlüssel für Benutzer)
- `title` (Titel)
- `authors` (Autor(en))
- `isbn` (ISBN)
- `pages` (Seitenanzahl)
- `format` (E-Book, Hardcover, etc.)
- `status` (Gelesen, Aktuell, Wunschliste)
- `started_at` (Lesebeginn)
- `finished_at` (Leseende)
- `cover_url` (Pfad zum Cover-Bild)
- `notes` (Notizen)

## 3. Datenimport
- Erstellung eines Artisan-Commands oder Seeders, der die `.\Bookstats.csv` einliest und in die Datenbank migriert.
- Mapping der deutschen Spaltennamen auf die Datenbankfelder.

## 4. Kernfunktionen
- **Dashboard**: Statistiken (Bücher insgesamt, pro Jahr/Monat, Durchschnittszeit, Gesamtseiten).
- **Buchsuche & Hinzufügen**: Integration der OpenLibrary API (kostenlos) für ISBN-Suche und Metadaten.
- **Barcode-Scanner**: Nutzung von `html5-qrcode` für die Nutzung der Smartphone-Kamera.
- **Bearbeitung**: Manuelle Anpassung von Metadaten und Cover-Upload.
- **Lesefortschritt**: Einfache Eingabe von Start- und Enddatum.

## 5. PWA & Mobile Optimierung
- Konfiguration des Web App Manifests.
- Service Worker für Offline-Caching.
- Touch-optimiertes Design mit DaisyUI.

## 6. Nächste Schritte
1.  Installation von Laravel Breeze (Inertia/Vue).
2.  Konfiguration der Datenbank (SQLite oder MySQL).
3.  Erstellung der Migration für `books`.
4.  Implementierung des CSV-Imports.
