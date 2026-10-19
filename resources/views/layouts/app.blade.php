<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" 
          content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Brewing Connections')</title>

    {{-- Google Fonts: Playfair Display + Poppins --}}
    <link rel="preconnect" 
          href="https://fonts.googleapis.com">
    <link rel="preconnect" 
          href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Poppins:wght@300;400;500;600;700&display=swap" 
          rel="stylesheet">

    {{-- Production frontend bundle (Tailwind + shared app CSS) --}}
    @vite('resources/css/app.css')

    {{-- Alpine.js --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
        }
        h1, h2, h3, h4, h5, h6 { 
            font-family: 'Playfair Display', serif; 
        }
        .font-display { 
            font-family: 'Playfair Display', serif; 
        }
        .font-body { 
            font-family: 'Poppins', sans-serif; 
        }

        .brand-wordmark {
            display: inline-flex;
            align-items: baseline;
            gap: 0;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .brand-wordmark .brand-hub {
            font-style: italic;
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.55) transparent;
        }

        *::-webkit-scrollbar {
            width: 6px;
            height: 6px;
            background: transparent;
        }

        *::-webkit-scrollbar-track {
            background: transparent;
        }

        *::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.55);
            border-radius: 9999px;
            border: 1px solid transparent;
            background-clip: content-box;
        }

        *::-webkit-scrollbar-thumb:hover {
            background-color: rgba(107, 114, 128, 0.7);
        }

        [x-cloak] { display: none !important; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-slide-in-left {
            animation: slideInLeft 0.6s ease-out;
        }

        .animate-slide-in-right {
            animation: slideInRight 0.6s ease-out;
        }

        .animate-fade-in-up-delay-1 {
            animation: fadeInUp 0.6s ease-out 0.2s both;
        }

        .animate-fade-in-up-delay-2 {
            animation: fadeInUp 0.6s ease-out 0.4s both;
        }

        .animate-fade-in-up-delay-3 {
            animation: fadeInUp 0.6s ease-out 0.6s both;
        }

        #navbar .nav-link,
        #navbar .nav-logo,
        #navbar button {
            color: white !important;
        }
        #navbar .nav-link:hover,
        #navbar .nav-logo:hover,
        #navbar button:hover {
            color: #F3E9D7 !important;
        }

        #navbar .login-btn,
        #navbar .login-btn:hover {
            color: unset !important;
        }

        body.login-page {
            overflow: hidden !important;
            height: 100vh !important;
        }

        @media (max-width: 767px) {
            .admin-sidebar {
                padding-top: 1rem;
                padding-bottom: max(1rem, env(safe-area-inset-bottom));
                overflow-y: auto !important;
                overflow-x: hidden !important;
                overscroll-behavior: contain;
                -webkit-overflow-scrolling: touch;
                z-index: 1500 !important;
            }

            .admin-sidebar > div:first-child {
                min-height: 0;
                padding-right: 0.25rem;
            }

            .admin-sidebar-toggle {
                position: fixed;
                top: 0.9rem;
                left: 0.9rem;
                width: 2.75rem;
                height: 2.75rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 0.8rem;
                border: none;
                background: rgba(255, 255, 255, 0.92);
                color: #3A2E22;
                backdrop-filter: blur(7px);
                box-shadow: 0 10px 22px rgba(0, 0, 0, 0.2);
                z-index: 1510;
                transform: translateY(0) scale(1);
                transition: opacity 0.2s ease, transform 0.2s ease;
            }

            .admin-sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(17, 24, 39, 0.45);
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.2s ease;
                z-index: 1490;
            }

            .admin-sidebar-open .admin-sidebar {
                /* Tailwind v4 uses the individual `translate` property for translate-x utilities. */
                translate: 0 !important;
                transform: none !important;
            }

            .admin-sidebar-open .admin-sidebar-overlay {
                opacity: 1;
                pointer-events: auto;
            }

            .admin-sidebar-open .admin-sidebar-toggle {
                opacity: 0;
                pointer-events: none;
                transform: translateY(-8px) scale(0.95);
            }

            .admin-modal-open .admin-sidebar-toggle {
                z-index: 30 !important;
            }

            .logout-modal-open .admin-sidebar {
                z-index: 20 !important;
            }

            .logout-modal-open .admin-sidebar-overlay {
                z-index: 15 !important;
            }

            .logout-modal-open .admin-sidebar-toggle {
                z-index: 25 !important;
            }

            .logout-modal-open [data-global-logout-modal='container'] {
                z-index: 4000 !important;
            }

            .logout-modal-open [data-global-logout-modal='overlay'] {
                z-index: 4000 !important;
            }

            .logout-modal-open [data-global-logout-modal='card'] {
                z-index: 4001 !important;
            }

            .admin-sidebar ~ main {
                padding-top: 4.75rem !important;
                padding-left: 1rem !important;
                padding-right: 1rem !important;
                padding-bottom: 1rem !important;
            }

            .admin-sidebar ~ main .sticky {
                top: 0.75rem !important;
            }

            .admin-sidebar-open body,
            .admin-sidebar-open {
                overflow: hidden;
            }
        }
    </style>

    @stack('head')
    @stack('styles')
    <link rel="icon" type="image/png" href="{{ asset('images/brewhublogo.png') }}">
</head>
<body class="bg-[#F3E9D7] text-[#3A2E22]" x-data="{ logoutModalOpen: false }" :class="{ 'logout-modal-open': logoutModalOpen }" @open-logout-modal.window="logoutModalOpen = true" @keydown.escape.window="logoutModalOpen = false">

    @yield('content')

    @php
        $showLoginPrivacyModal = auth()->check() && session()->pull('show_privacy_policy_modal', false);
    @endphp
    @if($showLoginPrivacyModal)
        <div id="loginPrivacyModal" class="fixed inset-0 z-[5000] flex items-center justify-center bg-[#2E5A3D]/40 px-4 py-4" role="dialog" aria-modal="true" aria-labelledby="loginPrivacyTitle">
            <div class="flex max-h-[min(88svh,720px)] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-[#D9CDBA] bg-[#F3E9D7] shadow-2xl">
                <div class="flex items-center justify-between border-b border-[#D9CDBA] px-5 py-4 sm:px-6">
                    <h3 id="loginPrivacyTitle" class="font-display text-lg font-bold text-[#3A2E22]">Privacy Policy — Data Privacy Act of 2012</h3>
                </div>
        <div id="loginPrivacyModalBody" class="overflow-y-auto px-5 py-5 text-sm leading-relaxed text-[#3A2E22] sm:px-6">
            <p class="mb-4">BrewHub respects your privacy and handles personal information in accordance with Republic Act No. 10173, the <strong>Data Privacy Act of 2012</strong>, its Implementing Rules and Regulations, and applicable National Privacy Commission issuances. This notice explains how information submitted during registration and use of BrewHub may be processed.</p>

            <p class="mb-2"><strong>1. Personal information we collect</strong></p>
            <p class="mb-4">Depending on the account type and features used, BrewHub may collect your name, email address, account credentials, contact number, establishment name and type, barangay, street or business address, website, map location, and information you submit through listings, ratings, reservations, orders, messages, or other platform features. Do not submit information that is not required or relevant to the service.</p>

            <p class="mb-2"><strong>2. Why we use your information</strong></p>
            <p class="mb-4">Information may be used to create and secure your account; verify and manage establishment registrations; display establishment details and locations; provide mapping, coffee-trail, rating, recommendation, promotion, store, reservation, and order features; communicate service or account notices; prevent fraud, abuse, and security incidents; maintain records; and meet legal obligations. BrewHub should use information only for declared, legitimate purposes and in a manner proportionate to those purposes.</p>

            <p class="mb-2"><strong>3. Lawful basis and consent</strong></p>
            <p class="mb-4">Personal information is processed only where a lawful basis under applicable law exists, such as steps needed to provide the service you request, compliance with a legal obligation, legitimate interests where permitted, or your consent where required. Where consent is relied upon, it should be freely given, specific, informed, and evidenced appropriately.</p>

            <p class="mb-2"><strong>4. Sharing and disclosure</strong></p>
            <p class="mb-4">BrewHub may make establishment information you choose to publish visible to platform users and may use service providers that support hosting, storage, communications, security, and platform operation. Information may also be disclosed when required by law or to protect rights and platform security. Personal information should not be sold or disclosed for unrelated purposes without an appropriate lawful basis.</p>

            <p class="mb-2"><strong>5. Storage, security, and retention</strong></p>
            <p class="mb-4">BrewHub applies reasonable organizational, physical, and technical safeguards intended to protect personal information against unauthorized access, loss, misuse, alteration, or disclosure. Information is retained only for as long as reasonably necessary for the stated purposes, account administration, dispute resolution, security, and legal obligations, after which it should be securely deleted or anonymized where appropriate.</p>

            <p class="mb-2"><strong>6. Your data privacy rights</strong></p>
            <p class="mb-4">Subject to applicable law and its conditions, you may have the right to be informed about processing, access your personal information, request correction or erasure/blocking where justified, object to or withdraw consent from certain processing, request data portability where applicable, and lodge a complaint with the National Privacy Commission. These rights are not absolute and may be subject to legal exceptions.</p>

            <p class="mb-2"><strong>7. Contact and concerns</strong></p>
            <p class="mb-4">For questions, access or correction requests, or privacy concerns, contact BrewHub through its <a class="font-semibold text-[#2E5A3D] underline underline-offset-2 hover:text-[#1E3A2A]" href="https://www.facebook.com/profile.php?id=61560676899683" target="_blank" rel="noopener noreferrer">official Facebook page</a> or email <a class="font-semibold text-[#2E5A3D] underline underline-offset-2 hover:text-[#1E3A2A]" href="mailto:brewhub1@gmail.com">brewhub1@gmail.com</a>. Identify the account involved without sending your password. If your concern remains unresolved, you may consult the National Privacy Commission through its official website.</p>

            <p class="mb-2"><strong>8. Legal references</strong></p>
            <p class="mb-2">This notice is a BrewHub-specific summary and is not the text of the law or a claim of government endorsement. Read the official sources:</p>
            <ul class="mb-4 list-disc space-y-2 pl-5">
                <li><a class="font-semibold text-[#2E5A3D] underline underline-offset-2 hover:text-[#1E3A2A]" href="https://officialgazette.gov.ph/2012/08/15/republic-act-no-10173/" target="_blank" rel="noopener noreferrer">Republic Act No. 10173 — Official Gazette of the Philippines</a></li>
                <li><a class="font-semibold text-[#2E5A3D] underline underline-offset-2 hover:text-[#1E3A2A]" href="https://privacy.gov.ph/implementing-rules%20regulations-data-privacy-act-2012/" target="_blank" rel="noopener noreferrer">Implementing Rules and Regulations — National Privacy Commission</a></li>
                <li><a class="font-semibold text-[#2E5A3D] underline underline-offset-2 hover:text-[#1E3A2A]" href="https://privacy.gov.ph/" target="_blank" rel="noopener noreferrer">National Privacy Commission official website</a></li>
            </ul>
            <p class="text-xs text-[#8C7A68]">Please review this notice before creating your account. BrewHub should update this policy if its actual data practices or contact details change.</p>
        </div>

                <div class="flex flex-col gap-3 border-t border-[#D9CDBA] px-5 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <label class="flex items-start gap-2 text-sm leading-5 text-[#3A2E22]">
                        <input id="loginPrivacyAccepted" type="checkbox" class="mt-1 h-4 w-4 shrink-0 rounded border-[#B9A58D] text-[#2E5A3D] focus:ring-[#2E5A3D]">
                        <span>I have read and agree to the Data Privacy Policy</span>
                    </label>
                    <button type="button" id="loginPrivacyAcceptButton" data-redirect="{{ session()->pull('privacy_policy_pending_redirect', route('home')) }}" disabled class="shrink-0 cursor-not-allowed rounded-lg bg-[#8C8C8C] px-5 py-2.5 text-sm font-semibold text-white transition">I Accept</button>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('loginPrivacyModal');
                const checkbox = document.getElementById('loginPrivacyAccepted');
                const button = document.getElementById('loginPrivacyAcceptButton');
                const sync = () => {
                    const accepted = checkbox.checked;
                    button.disabled = !accepted;
                    button.classList.toggle('cursor-not-allowed', !accepted);
                    button.classList.toggle('bg-[#8C8C8C]', !accepted);
                    button.classList.toggle('bg-[#2E5A3D]', accepted);
                    button.classList.toggle('hover:bg-[#1E3A2A]', accepted);
                };
                checkbox.addEventListener('change', sync);
                button.addEventListener('click', () => { if (checkbox.checked) window.location.assign(button.dataset.redirect || '/'); });
                sync();
            });
        </script>
    @endif


    @auth
        <div class="fixed inset-0 flex items-center justify-center px-4" data-global-logout-modal="container" x-show="logoutModalOpen" x-cloak @click="logoutModalOpen = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" style="display: none; z-index: 3000;">
            <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm" data-global-logout-modal="overlay" style="z-index: 3000;" @click.stop="logoutModalOpen = false"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full" data-global-logout-modal="card" style="z-index: 3001;" @click.stop>
                <div class="p-6">
                    <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-4">Log out?</h2>
                    <p class="text-[#3A2E22] mb-6">Are you sure you want to log out of your account?</p>
                    <div class="flex gap-3">
                        <button @click="logoutModalOpen = false" class="flex-1 inline-flex items-center justify-center h-10 px-4 rounded-lg border border-gray-300 text-gray-700 text-base font-semibold hover:bg-gray-50 transition-colors">Cancel</button>
                        <form method="POST" action="{{ route('logout') }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center h-10 px-4 rounded-lg bg-red-600 text-white text-base font-semibold hover:bg-red-700 transition-colors">Log out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endauth

    @php
        $currentRouteName = (string) (request()->route()?->getName() ?? '');
        $isMapPage = str_contains($currentRouteName, '.map');
    @endphp
    @if(!$isMapPage)
        <button
            id="globalScrollToTopButton"
            type="button"
            class="fixed bottom-6 right-6 z-40 w-12 h-12 rounded-full bg-[#4A6741] text-white shadow-lg hover:bg-[#3f5b38] focus:outline-none focus:ring-2 focus:ring-[#4A6741]/50 opacity-0 pointer-events-none transition-opacity duration-300"
            aria-label="Scroll to top"
        >
            <svg class="w-5 h-5 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 19V5"/>
                <path d="m5 12 7-7 7 7"/>
            </svg>
        </button>
        <script>
            (function () {
                const scrollToTopButton = document.getElementById('globalScrollToTopButton');
                if (!scrollToTopButton) {
                    return;
                }

                const toggleScrollToTopButton = () => {
                    if ((window.scrollY || 0) > 260) {
                        scrollToTopButton.classList.remove('opacity-0', 'pointer-events-none');
                    } else {
                        scrollToTopButton.classList.add('opacity-0', 'pointer-events-none');
                    }
                };

                window.addEventListener('scroll', toggleScrollToTopButton, { passive: true });
                scrollToTopButton.addEventListener('click', () => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });

                toggleScrollToTopButton();
            })();
        </script>
    @endif

    <script>
        (function () {
            const root = document.documentElement;
            const sidebar = document.querySelector('.admin-sidebar');
            let closeSidebar = () => {};

            if (sidebar) {
                const toggle = document.createElement('button');
                toggle.type = 'button';
                toggle.className = 'admin-sidebar-toggle md:hidden';
                toggle.setAttribute('aria-label', 'Open navigation menu');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.innerHTML = `
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 6h18"></path>
                        <path d="M3 12h18"></path>
                        <path d="M3 18h18"></path>
                    </svg>
                `;

                const overlay = document.createElement('div');
                overlay.className = 'admin-sidebar-overlay md:hidden';
                overlay.setAttribute('aria-hidden', 'true');

                const syncSidebarState = () => {
                    const isOpen = root.classList.contains('admin-sidebar-open');
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    toggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
                };

                const closeSidebarHandler = () => {
                    root.classList.remove('admin-sidebar-open');
                    syncSidebarState();
                };
                closeSidebar = closeSidebarHandler;

                const toggleSidebar = () => {
                    root.classList.toggle('admin-sidebar-open');
                    syncSidebarState();
                };

                toggle.addEventListener('click', toggleSidebar);
                overlay.addEventListener('click', closeSidebarHandler);

                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 768) {
                        closeSidebarHandler();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeSidebarHandler();
                    }
                });

                sidebar.querySelectorAll('a').forEach((link) => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth < 768) {
                            closeSidebarHandler();
                        }
                    });
                });

                document.body.appendChild(overlay);
                document.body.appendChild(toggle);
                syncSidebarState();
            }

            const lockTargets = [document.documentElement, document.body];
            const modalSelector = [
                '.fixed.inset-0',
                '.modal',
                '[role="dialog"]',
                '[aria-modal="true"]',
                '[data-modal-dialog]'
            ].join(',');

            let isScrollLocked = false;

            const isElementVisible = (element) => {
                if (!element || element.classList.contains('hidden') || element.getAttribute('aria-hidden') === 'true') {
                    return false;
                }

                const style = window.getComputedStyle(element);
                if (style.display === 'none' || style.visibility === 'hidden') {
                    return false;
                }

                return element.offsetWidth > 0 || element.offsetHeight > 0;
            };

            const hasVisibleModal = () => {
                const modalCandidates = document.querySelectorAll(modalSelector);
                return Array.from(modalCandidates).some((element) => {
                    if (element.id === 'globalScrollToTopButton') {
                        return false;
                    }

                    return isElementVisible(element);
                });
            };

            const setScrollLocked = (shouldLock) => {
                if (shouldLock === isScrollLocked) {
                    root.classList.toggle('admin-modal-open', shouldLock);
                    if (shouldLock && window.innerWidth < 768) {
                        closeSidebar();
                    }
                    return;
                }

                isScrollLocked = shouldLock;
                root.classList.toggle('admin-modal-open', shouldLock);
                if (shouldLock && window.innerWidth < 768) {
                    closeSidebar();
                }
                lockTargets.forEach((target) => {
                    if (shouldLock) {
                        target.dataset.prevOverflow = target.style.overflow || '';
                        target.style.overflow = 'hidden';
                    } else {
                        target.style.overflow = target.dataset.prevOverflow || '';
                        delete target.dataset.prevOverflow;
                    }
                });
            };

            const evaluateModalScrollState = () => {
                window.requestAnimationFrame(() => {
                    setScrollLocked(hasVisibleModal());
                });
            };

            const observer = new MutationObserver(evaluateModalScrollState);
            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['class', 'style', 'aria-hidden'],
                childList: true,
                subtree: true,
            });

            window.addEventListener('load', evaluateModalScrollState);
            document.addEventListener('click', evaluateModalScrollState, true);
            document.addEventListener('keydown', evaluateModalScrollState, true);

            evaluateModalScrollState();
        })();
    </script>

    @stack('scripts')
</body>
</html>