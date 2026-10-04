# Flowchart Rancangan Sistem SIMTAMAN

**Sistem Informasi Manajemen Pertamanan**  
Disperakimtan Kota Batam · Rancangan alur sistem · September 2026

Dokumen ini melengkapi [`ARSITEKTUR-SISTEM-SIMTAMAN.md`](ARSITEKTUR-SISTEM-SIMTAMAN.md) dengan diagram alur (flowchart) untuk kebutuhan KAK, desain sistem, dan sosialisasi pengguna.

**Lampiran KAK 1 halaman:** [`LAMPIRAN-KAK-FLOWCHART-SIMTAMAN-1-HALAMAN.md`](LAMPIRAN-KAK-FLOWCHART-SIMTAMAN-1-HALAMAN.md) · Word: `php scripts/generate-lampiran-flowchart-kak-docx.php`

> Diagram menggunakan [Mermaid](https://mermaid.js.org/). Pratinjau: VS Code/Cursor (Markdown Preview), GitHub, atau ekspor ke PNG via Mermaid Live Editor.

---

## 1. Konteks sistem (aktör & batas sistem)

```mermaid
flowchart TB
    subgraph actors [Aktör]
        M[Masyarakat]
        AD[Admin / Operator Disperakimtan]
        PG[Petugas lapangan]
        PPK[Pejabat / Viewer]
    end

    subgraph simtaman [SIMTAMAN — Laravel 12 Monolith]
        PUB[Kanal Publik]
        ADM[Kanal Admin /admin]
        LAP[Kanal Input Lapangan /lapangan]
    end

    subgraph external [Layanan eksternal]
        OSM[OpenStreetMap / Leaflet]
        NOM[Nominatim geocoder]
        SMTP[SMTP email opsional]
    end

    subgraph infra [Infrastruktur]
        DB[(MySQL / MariaDB)]
        FS[Storage foto & PDF]
    end

    M --> PUB
    AD --> ADM
    PG --> LAP
    PG --> ADM
    PPK --> ADM

    PUB --> OSM
    ADM --> OSM
    ADM --> NOM
    ADM --> SMTP

    PUB --> DB
    ADM --> DB
    LAP --> DB
    ADM --> FS
    LAP --> FS
    PUB --> FS
```

---

## 2. Pembagian kanal akses (routing)

```mermaid
flowchart TD
    REQ[Permintaan HTTP] --> NGINX[Nginx → public/index.php]
    NGINX --> LAR[Laravel Router]

    LAR --> R1{Prefix URL?}

    R1 -->|/ login logout| AUTH[Auth LoginController]
    R1 -->|/ admin/*| MW_A[Middleware: auth + admin.access + viewer.scope + audit]
    R1 -->|/ lapangan/*| MW_L[Middleware: lapangan.access]
    R1 -->|/ taman, /rth, /aduan, ...| PUB[Controller Publik]

    MW_A --> ADM_C[Admin Controllers]
    MW_L --> LAP_C[Lapangan OperasionalController]

    ADM_C --> BL[Support / Policies / Models]
    LAP_C --> BL
    PUB --> BL
    AUTH --> BL

    BL --> DB[(Database)]
    BL --> DISK[storage/app/public]
```

---

## 3. Alur autentikasi & otorisasi

```mermaid
flowchart TD
    START([Pengguna membuka aplikasi]) --> CH{Kanal?}

    CH -->|Publik| POK[Portal tanpa login]
    CH -->|/login| LOGIN[Form email + password]
    CH -->|/lapangan| LPIN[Halaman buka PIN]

    LOGIN --> LT{Valid + throttle OK?}
    LT -->|Tidak| LF[Gagal / rate limit]
    LT -->|Ya| SESS[Session auth]
    SESS --> ROLE{users.role}

    ROLE -->|administrator| FULL[Akses penuh + kelola user]
    ROLE -->|admin| OPS[CRUD operasional + evaluasi]
    ROLE -->|operator| WIL[CRUD ter-scope tim/wilayah]
    ROLE -->|viewer| VIEW[Baca terbatas + evaluasi read]

    FULL --> ADM[/admin dashboard/]
    OPS --> ADM
    WIL --> ADM
    VIEW --> ADM

    LPIN --> PIN{PIN benar?}
    PIN -->|Tidak| LPIN
    PIN -->|Ya| LSES[Session lapangan TTL]
    LSES --> LAPIDX[/lapangan menu tim/]

    SESS --> CANWRITE{canWrite?}
    CANWRITE -->|Ya| LAPIDX

    ADM --> POL[Policy + OperatorWilayahScope]
    POL --> ACT[Aksi CRUD / laporan]
```

---

## 4. Modul fungsional (peta menu)

```mermaid
flowchart LR
    subgraph public_mod [Portal Publik]
        H[Beranda]
        T[Taman & peta]
        RTH[RTH Kota Batam]
        AR[Scan QR WebAR]
        ENK[Ensiklopedia]
        ADU[Aduan masyarakat]
        SUR[Survey kepuasan]
    end

    subgraph admin_mod [Back-Office Admin]
        DSH[Dashboard]
        TM[Kelola taman + CSV]
        OP[Operasional pertamanan]
        PM[Pemeliharaan rutin]
        JK[Jadwal / permohonan layanan]
        BB[Bibit masuk/keluar]
        ADM2[Tindak lanjut aduan & survey]
        EV[Evaluasi RAP 8 modul]
        RPT[Laporan PDF]
        REF[Tim, petugas, ensiklopedia]
        USR[User — Administrator saja]
    end

    subgraph lap_mod [Input Lapangan]
        PR[Pemeliharaan rutin per tim]
        PG2[Progres permohonan harian]
    end

    public_mod --> DB[(Data master & transaksi)]
    admin_mod --> DB
    lap_mod --> DB
    EV --> RPT
```

---

## 5. Alur data profil taman (kelengkapan & CSV)

```mermaid
flowchart TD
    A([Administrator / Admin]) --> B{Metode input?}

    B -->|Form web| C[Admin TamanController CRUD]
    B -->|Massal| D[Export CSV data existing]
    D --> E[Lengkapi kolom di spreadsheet]
    E --> F[Import CSV update by id/nama]
    F --> G[TamanCsvImporter validasi]

    C --> H[TamanCompleteness — 13 kriteria]
    G --> H

    H --> I{Status data}
    I -->|Lengkap| J[Portal publik + evaluasi kelengkapan]
    I -->|Belum lengkap| K[Flag kolom_belum_lengkap + prioritas input]

    C --> GEO{Koordinat GPS?}
    GEO -->|Ya| NOM[Nominatim → kelurahan/kecamatan]
    GEO -->|Tidak| MAN[Input manual wilayah]
    NOM --> C
    MAN --> C

    C --> IMG[Upload galeri → storage/public]
    IMG --> J
```

---

## 6. Alur pemeliharaan rutin operasional

```mermaid
flowchart TD
    S([Mulai kegiatan harian]) --> SRC{Sumber input?}

    SRC -->|Admin /admin| F1[Form pemeliharaan-tamans]
    SRC -->|Lapangan /lapangan/pemeliharaan| F2[Form mobile tim]

    F1 --> VAL[Validasi tim, taman, petugas, foto, armada]
    F2 --> VAL

    VAL -->|Gagal| ERR[Pesan error / redirect form]
    VAL -->|OK| REC[PemeliharaanTamanRecorder]

    REC --> PET[PetugasAssignment → pivot petugas]
    REC --> SAVE[(pemeliharaan_tamans + foto disk)]
    REC --> ARM[(pemeliharaan_taman_armadas)]

    SAVE --> AUD{Admin write?}
    AUD -->|Ya| LOG[activity_logs]

    SAVE --> EV1[Evaluasi RTH terpelihara]
    SAVE --> EV2[Evaluasi kinerja tim]
    SAVE --> EV3[Evaluasi pemeliharaan per taman]
    SAVE --> DSH[Dashboard & laporan operasional PDF]

    SAVE --> PUB[Indikator kesehatan taman di portal / WebAR]
```

---

## 7. Alur permohonan layanan (pemangkasan / jadwal)

```mermaid
flowchart TD
    A([Permohonan masuk]) --> B[Admin: create pemangkasan status Rencana/Dijadwalkan]
    B --> C[(pemangkasans)]

    C --> D{Pelaksanaan}
    D -->|Progres harian| E[Lapangan atau Admin: update progres]
    E --> F[PermohonanProgressRecorder]
    F --> G[(pemangkasan_progres + foto + petugas)]
    G --> H[Update persentase pemangkasans]

    D -->|Selesai| I[Patch status Selesai]
    H --> J[Evaluasi operasional permohonan]
    H --> K[Evaluasi kinerja tim]
    I --> J

    G --> PDF[Export PDF progres lapangan/admin]
    J --> RPT[Laporan operasional pertamanan]
```

---

## 8. Alur partisipasi masyarakat

```mermaid
flowchart LR
    subgraph input [Input tanpa akun]
        AD1[Form aduan]
        AD2[Cek status aduan]
        SV[Form survey kepuasan]
        MS[Masukan umum]
    end

    AD1 --> TH1[Throttle 5/menit]
    SV --> TH2[Throttle 10/menit]
    TH1 --> DB1[(aduan_masyarakats)]
    TH2 --> DB2[(survey_kepuasan)]

    DB1 --> ADM[Admin: review & update status]
    DB2 --> ADM
    ADM --> EV[Evaluasi masukan masyarakat]
    EV --> DPA[Dashboard & PDF evaluasi]
```

---

## 9. Alur evaluasi & monitoring kinerja (RAP)

```mermaid
flowchart TD
    subgraph sumber [Sumber data operasional]
        T1[tamans + kelengkapan]
        T2[pemeliharaan_tamans]
        T3[pemangkasans + progres]
        T4[aduan & survey]
        T5[armada operasional]
    end

    sumber --> RB[Report Builder per modul evaluasi]

    RB --> M1[Pemeliharaan]
    RB --> M2[Armada]
    RB --> M3[Kinerja tim]
    RB --> M4[Operasional permohonan]
    RB --> M5[RTH terpelihara]
    RB --> M6[Masukan masyarakat]
    RB --> M7[Kelengkapan data]
    RB --> M8[RAP konsolidasi opsional]

    M1 --> VIEW[Halaman evaluasi + filter periode]
    M2 --> VIEW
    M3 --> VIEW
    M4 --> VIEW
    M5 --> VIEW
    M6 --> VIEW
    M7 --> VIEW
    M8 --> VIEW

    VIEW --> PDF[Export PDF DomPDF]
    VIEW --> DSH[Dashboard admin agregat]

    DSH --> DEC([Keputusan manajerial Disperakimtan])
```

---

## 10. Alur input lapangan (PIN & sesi)

```mermaid
flowchart TD
    P([Petugas buka /lapangan]) --> U{Sudah unlock?}

    U -->|Tidak| F[Form PIN SIMTAMAN_LAPANGAN_PIN]
    F --> T{PIN valid?}
    T -->|Tidak| F
    T -->|Ya| S[Session lapangan_guest_unlocked_at]

    U -->|Ya| S
    U -->|User login operator/admin| S2[Middleware: canWrite]

    S --> MENU[Menu tim: pemeliharaan / permohonan]
    S2 --> MENU

    MENU --> OP[Submit form operasional]
    OP --> DB[(Database + upload foto)]

    MENU --> K[/lapangan/kunci]
    K --> END([Sesi PIN dihapus])
```

---

## 11. Alur deploy & operasional infrastruktur (ringkas)

```mermaid
flowchart LR
    DEV[Develop lokal] --> GIT[Git push GitHub]
    GIT --> VPS[SSH VPS git pull / deploy script]
    VPS --> ART[migrate, config:cache, view:cache]
    VPS --> LINK[storage:link]
    VPS --> CRON[Scheduler cron + backup harian]
    CRON --> BAK[(Backup DB + storage)]
    VPS --> USR([Pengguna produksi])
```

---

## 12. Legenda peran (RBAC)

| Peran | Kanal utama | Kemampuan ringkas |
|-------|-------------|-------------------|
| **Masyarakat** | Publik | Baca informasi, aduan, survey |
| **Viewer** | Admin (terbatas) | Baca dashboard & evaluasi |
| **Operator (Pengawas)** | Admin + Lapangan | Input operasional scope tim |
| **Admin** | Admin | Kelola data operasional & evaluasi |
| **Administrator** | Admin | + user, tim, import CSV, log |
| **Petugas (tanpa akun)** | Lapangan (PIN) | Input pemeliharaan & progres |

---

## 13. Dokumen terkait

| Dokumen | Isi |
|---------|-----|
| `docs/ARSITEKTUR-SISTEM-SIMTAMAN.md` | Layer, middleware, keamanan |
| `docs/DESAIN-BASIS-DATA-SIMTAMAN.md` | ERD & tabel |
| `docs/KAK-SIMTAMAN.md` | Kerangka acuan kerja |
| `docs/deploy/VPS-BATAMGARDEN.md` | Referensi server produksi |

---

*Disperakimtan Kota Batam — SIMTAMAN*
