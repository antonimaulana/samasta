<style>
    body.mobile-nav-open,
    body.admin-nav-open {
        overflow: hidden;
    }

    #public-mobile-menu-toggle,
    #admin-mobile-menu-toggle {
        display: none;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 767px) {
        #public-mobile-menu-toggle,
        #admin-mobile-menu-toggle {
            display: inline-flex !important;
        }

        #public-mobile-drawer.is-open {
            display: block !important;
            position: fixed !important;
            top: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            left: auto !important;
            width: 85vw !important;
            max-width: 320px !important;
            min-width: 260px !important;
            z-index: 100000 !important;
            background: #ffffff !important;
            color: #111827 !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
            box-shadow: -10px 0 40px rgba(0, 0, 0, 0.15) !important;
            transform: none !important;
            -webkit-transform: none !important;
            visibility: visible !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        #admin-mobile-drawer.is-open {
            display: block !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            bottom: 0 !important;
            right: auto !important;
            width: 85vw !important;
            max-width: 280px !important;
            min-width: 240px !important;
            z-index: 100000 !important;
            background: #111827 !important;
            color: #ffffff !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
            box-shadow: 10px 0 40px rgba(0, 0, 0, 0.25) !important;
            transform: none !important;
            -webkit-transform: none !important;
            visibility: visible !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        #public-mobile-backdrop.is-open,
        #admin-mobile-backdrop.is-open {
            display: block !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            z-index: 99999 !important;
            background: rgba(17, 24, 39, 0.45) !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }
    }

    #public-mobile-drawer nav a,
    #public-mobile-drawer nav p {
        display: block;
        color: #374151;
        text-decoration: none;
    }

    #public-mobile-drawer nav a {
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    #public-mobile-drawer nav p {
        margin-top: 0.75rem;
        padding: 0 1rem;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #9ca3af;
    }

    #admin-mobile-drawer nav a {
        display: block;
        padding: 0.5rem 0.75rem;
        color: #d1d5db;
        text-decoration: none;
        font-size: 0.875rem;
        border-radius: 0.5rem;
    }

    #admin-mobile-drawer nav a:hover {
        background: #1f2937;
        color: #ffffff;
    }
</style>
