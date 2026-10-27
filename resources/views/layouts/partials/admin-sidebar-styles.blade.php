<style>
    .sidebar-menu-group .sidebar-submenu {
        overflow: hidden;
        max-height: 0;
        opacity: 0;
        pointer-events: none;
        transition: max-height 0.25s ease, opacity 0.2s ease;
    }
    .sidebar-menu-group.is-open .sidebar-submenu {
        max-height: min(75vh, 40rem);
        opacity: 1;
        pointer-events: auto;
    }
    .sidebar-menu-group.is-open .sidebar-submenu {
        margin-top: 0.125rem;
        margin-bottom: 0.25rem;
        margin-left: 0.75rem;
        padding-left: 0.5rem;
        border-left: 2px solid rgb(55 65 81);
    }
    .sidebar-menu-trigger {
        border: 0;
        background: transparent;
        font: inherit;
    }
    .sidebar-menu-group.is-open .sidebar-chevron {
        transform: rotate(180deg);
    }
    .sidebar-chevron {
        transition: transform 0.2s ease;
    }

    .admin-nav-icon {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
        opacity: 0.9;
    }

    /* Menu utama (link tunggal + judul grup) */
    .admin-nav-link,
    .sidebar-menu-trigger.admin-nav-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        border-radius: 0.5rem;
        padding: 0.4375rem 0.625rem;
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.25rem;
        text-decoration: none;
        color: rgb(229 231 235);
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .admin-nav-link:hover,
    .sidebar-menu-trigger.admin-nav-link:hover {
        background-color: rgb(31 41 55);
        color: rgb(255 255 255);
    }

    /* Halaman aktif — menu tingkat atas (Dashboard, Evaluasi) */
    .admin-nav-link--active,
    .admin-nav-link--active:hover {
        background-color: rgb(22 163 74);
        color: rgb(255 255 255);
    }

    /* Grup terbuka / ada halaman anak aktif — bukan hijau penuh */
    .admin-nav-link--section-active,
    .admin-nav-link--section-active:hover {
        background-color: rgb(31 41 55);
        color: rgb(255 255 255);
        box-shadow: inset 3px 0 0 rgb(74 222 128);
    }

    /* Submenu */
    .admin-nav-sublink {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        border-radius: 0.375rem;
        padding: 0.375rem 0.625rem;
        font-size: 0.8125rem;
        font-weight: 400;
        line-height: 1.25rem;
        text-decoration: none;
        color: rgb(156 163 175);
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    .admin-nav-sublink:hover {
        background-color: rgb(31 41 55);
        color: rgb(243 244 246);
    }

    .admin-nav-sublink--active,
    .admin-nav-sublink--active:hover {
        background-color: rgb(6 78 59 / 0.45);
        color: rgb(209 250 229);
        font-weight: 500;
        box-shadow: inset 2px 0 0 rgb(52 211 153);
    }

    .admin-nav-section-label {
        padding: 0.5rem 0.75rem 0.25rem;
        font-size: 0.625rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: rgb(107 114 128);
    }

    .admin-nav-badge {
        display: inline-flex;
        min-width: 1.25rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background-color: rgb(239 68 68);
        padding: 0.125rem 0.375rem;
        font-size: 0.625rem;
        font-weight: 700;
        color: rgb(255 255 255);
    }

    #admin-desktop-sidebar {
        transition: width 0.2s ease, min-width 0.2s ease, opacity 0.2s ease;
    }

    @media (min-width: 768px) {
        body.admin-sidebar-collapsed #admin-desktop-sidebar {
            width: 0 !important;
            min-width: 0 !important;
            overflow: hidden;
            border-right-width: 0;
            opacity: 0;
            pointer-events: none;
        }

        #admin-sidebar-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    }

    @media (max-width: 767px) {
        #admin-sidebar-toggle {
            display: none !important;
        }
    }

    .admin-main-table-wrap {
        -webkit-overflow-scrolling: touch;
    }
</style>
