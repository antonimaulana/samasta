# Dashboard Redesign — Fase A (Wireframe & Mockup)

**SIMTAMAN** · Disperakimtan Kota Batam · September 2026

---

## 1. Tujuan Fase A

Menetapkan **kerangka informasi** dan **pembagian akses visual** dashboard admin sebelum implementasi kode (Fase B–C).

**Deliverables Fase A:**

| Item | Lokasi |
|------|--------|
| Mockup interaktif (3 peran) | [dashboard-redesign-fase-a.canvas.tsx](/Users/USER/.cursor/projects/c-Users-USER-sitaman-batam/canvases/dashboard-redesign-fase-a.canvas.tsx) |
| Spesifikasi wireframe (dokumen ini) | `docs/DASHBOARD-REDESIGN-FASE-A.md` |

---

## 2. Prinsip Desain

### 2.1 Struktur 3 lapis (semua peran)

```
┌─────────────────────────────────────────────────────────┐
│ LAPIS 1 — Status hari ini                               │
│ 1 kalimat + semafor (Baik / Waspada / Perlu tindakan)   │
├─────────────────────────────────────────────────────────┤
│ LAPIS 2 — Prioritas / antrian                           │
│ Maks. 5 item · urgent di atas · klik → aksi/detail      │
├─────────────────────────────────────────────────────────┤
│ LAPIS 3 — Capaian & tren                                │
│ KPI + grafik · drill-down ke evaluasi/modul             │
└─────────────────────────────────────────────────────────┘
```

### 2.2 Aturan UX

- **Satu fokus utama** per peran — jangan campur inbox operasional dengan KPI strategis setara.
- **Warna semafor konsisten:** hijau = OK, kuning = waspada, merah = urgent.
- **Hindari duplikasi metrik** (contoh saat ini: kepuasan muncul di hero + kartu terpisah).
- **Sidebar & header** menampilkan badge peran + scope wilayah (operator).
- **Viewer tidak melihat** tombol create/edit; **operator tidak melihat** modul admin (DPA, users).

### 2.3 Masalah dashboard lama → solusi

| Masalah lama | Solusi Fase A |
|--------------|---------------|
| Terlalu banyak kartu setara | Hierarki 3 lapis |
| Hero + 4 kartu + jadwal + aset + alerts + accordion | Konten per peran dipangkas |
| Admin & viewer layout sama | 3 wireframe terpisah |
| Angka tanpa konteks | Hint: vs bulan lalu, vs target, scope wilayah |
| Detail tersembunyi di `<details>` | Viewer: tidak ada; Admin: opsional collapse |

---

## 3. Wireframe per Peran

### 3.1 Viewer / Pimpinan

**Tujuan:** Snapshot strategis untuk rapat — **tanpa aksi operasional**.

```
┌─ Header ─────────────────────────────────────────────────┐
│ [Viewer]  Dashboard  ·  Seluruh Kota Batam    [Export PDF]│
├──────────────────────────────────────────────────────────┤
│ STATUS: Indeks RAP 79 — Waspada                          │
│ Partisipasi masyarakat di bawah target kuartal.          │
├──────────────────────────────────────────────────────────┤
│ [Indeks RAP 79] [RTH terpelihara 82%] [Data 74%] [4.2★] │
├──────────────────────────┬───────────────────────────────┤
│ Grafik tren 6 bulan      │ Tiga pilar RAP (progress bar) │
│ pemeliharaan vs layanan  │ Basis data · Ops · Partisipasi│
├──────────────────────────┴───────────────────────────────┤
│ PRIORITAS (read-only, max 5)                             │
│ · RTH belum terpelihara >90 hari (14)     → Evaluasi     │
│ · Profil belum lengkap (23)               → Evaluasi     │
│ · Aduan overdue (5)                       → Evaluasi     │
└──────────────────────────────────────────────────────────┘
```

**Data sumber (Fase B):** `RapKonsolidasiReportBuilder`, evaluasi RTH terpelihara, kelengkapan data, masukan masyarakat.

**Nav sidebar:** Dashboard, Evaluasi (read), Laporan taman (read). Sembunyikan: Input lapangan, CRUD, DPA, Users.

---

### 3.2 Operator Lapangan

**Tujuan:** **Apa yang harus dikerjakan hari ini** — antrian + aksi cepat.

```
┌─ Header ─────────────────────────────────────────────────┐
│ [Operator · Tim Wilayah 2]     [Input Lapangan →]        │
├──────────────────────────────────────────────────────────┤
│ STATUS: 3 tugas hari ini · selesaikan sebelum 16:00      │
├──────────────────────────────────────────────────────────┤
│ [Jadwal hari ini 5] [Aduan baru 1] [Pemeliharaan 8/10]   │
├──────────────────────────────────────────────────────────┤
│ ANTRIAN HARI INI (prioritas)                             │
│ 08:00  Pemangkasan Taman Melati    Rencana    [Proses →] │
│ 10:30  Pemeliharaan Taman Kenari   Belum input [Input →] │
│ 13:00  Pohon tumbang Bengkong      Diproses   [Progres →]│
├──────────────────────────┬───────────────────────────────┤
│ Aksi cepat               │ Capaian tim (bulan ini)       │
│ [Input lapangan]         │ Bar chart per jenis layanan   │
│ [+ Pemeliharaan]         │ (scope Wilayah 2 saja)        │
│ [Update progres]         │                               │
└──────────────────────────┴───────────────────────────────┘
```

**Data sumber:** `operatorInbox`, `jadwalCounts`, scope `OperatorWilayahScope`.

**Nav sidebar:** Dashboard, Pemeliharaan, Permohonan, Input lapangan. Sembunyikan/redupkan: DPA, Users, Ensiklopedia CMS.

---

### 3.3 Administrator

**Tujuan:** Kendali lintas tim + alert sistem + pintasan manajemen.

```
┌─ Header ─────────────────────────────────────────────────┐
│ [Admin]  Dashboard  ·  Seluruh Dinas      [Export PDF]   │
├──────────────────────────────────────────────────────────┤
│ STATUS: Operasional Baik · 2 alert sistem                │
├──────────────────────────────────────────────────────────┤
│ [Taman 142] [Selesai 86%] [Aduan 7] [Terlambat 3] [RAP 79]│
├───────────────┬─────────────────┬────────────────────────┤
│ Prioritas     │ Jadwal lapangan │ Pintasan admin         │
│ lintas tim    │ 4 counter       │ Evaluasi, Tim, Users…  │
├───────────────┴─────────────────┴────────────────────────┤
│ Grafik Indeks RAP mingguan + link ke 8 modul evaluasi    │
└──────────────────────────────────────────────────────────┘
```

**Data sumber:** `DashboardSummaryBuilder` (full) + `OperationalAlertService` + agregat RAP.

---

## 4. Matriks Visibilitas Komponen

| Komponen | Viewer | Operator | Admin |
|----------|:------:|:--------:|:-----:|
| Status banner Lapis 1 | RAP | Tugas hari ini | Sistem |
| KPI strip (4–5 angka) | Strategis | Operasional tim | Lintas dinas |
| Antrian / inbox | — | **Utama** | Ringkas |
| Grafik tren | 6 bln kota | Bulan ini tim | RAP mingguan |
| Prioritas (max 5) | Read-only | Klik aksi | Lintas tim |
| Jadwal 4 counter | — | Opsional | Ya |
| Aset taman/bibit | — | — | Ringkas / link |
| Alerts operasional | — | Wilayah | Semua |
| Accordion detail | Tidak | Minimal | Opsional |
| Tombol CRUD | Tidak | Ya | Ya |
| Export PDF | Ya | Opsional | Ya |

---

## 5. Komponen UI (Fase C)

| Komponen Blade | Deskripsi |
|----------------|-----------|
| `x-dashboard.status-banner` | Lapis 1 — tone + label + deskripsi |
| `x-dashboard.kpi-strip` | 4–5 `Stat` dengan hint delta |
| `x-dashboard.priority-queue` | List max 5, severity, CTA |
| `x-dashboard.role-badge` | Admin / Operator + nama tim |
| `x-dashboard.scope-notice` | "Anda melihat data Tim Wilayah 2" |
| `x-dashboard.trend-chart` | Chart.js wrapper |
| `x-dashboard.quick-actions` | Hanya operator |

**Layout:** `admin/dashboard-viewer.blade.php`, `admin/dashboard-operator.blade.php`, `admin/dashboard-admin.blade.php` — dipilih di `DashboardController` berdasarkan `$user->role`.

---

## 6. Rencana Fase Berikutnya

| Fase | Pekerjaan | Estimasi |
|------|-----------|----------|
| **B** | `DashboardSummaryBuilder` → `DashboardViewerBuilder`, `DashboardOperatorBuilder`, `DashboardAdminBuilder` | 2–3 hari |
| **C** | Implementasi Blade + Tailwind + Chart.js per wireframe | 3–4 hari |
| **D** | Integrasi drill-down evaluasi RAP + UAT per peran | 2 hari |

---

## 7. Kriteria Aceita Fase A

- [x] Wireframe 3 peran terdokumentasi
- [x] Mockup interaktif (canvas) dengan data contoh
- [x] Matriks akses komponen jelas
- [x] Mapping ke data source existing
- [ ] Review & sign-off stakeholder Disperakimtan

---

*Dokumen ini melengkapi KAK (`docs/KAK-SIMTAMAN.md`) bagian dashboard operasional.*
