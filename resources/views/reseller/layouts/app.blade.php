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

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js --}}
    <script src="//unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" 
            defer></script>

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
    </style>

    @stack('head')
    @stack('styles')
</head>
<body class="bg-[#F3E9D7] text-[#3A2E22]">
    @php
        $sidebarUser = auth()->user();
        $sidebarImage = null;
        $sidebarProfileX = (int) (data_get($sidebarUser, 'profile_focus_x') ?? 50);
        $sidebarProfileY = (int) (data_get($sidebarUser, 'profile_focus_y') ?? 50);

        foreach (['image_url', 'profile_photo', 'profile_photo_path', 'avatar', 'photo', 'image'] as $photoColumn) {
            $candidate = data_get($sidebarUser, $photoColumn);
            if (!empty($candidate)) {
                $sidebarImage = $candidate;
                break;
            }
        }
    @endphp
    <div class="min-h-screen flex" x-data="{ logoutModalOpen: false }" x-init="logoutModalOpen = false" @keydown.escape.window="logoutModalOpen = false">
        <aside class="fixed left-0 top-0 h-screen w-64 min-w-64 max-w-64 bg-[#3A2E22] text-[#F5F0E8] flex flex-col justify-between py-6 px-4 rounded-r-xl shadow-lg overflow-hidden z-20">
            <div>
                <div class="flex items-center mb-8">
                    <svg class="w-6 h-6 mr-3 text-[#F5F0E8]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <span class="text-lg font-display font-bold">BrewHub</span>
                </div>

                <nav class="space-y-1">
                    <a href="{{ route('reseller.dashboard') }}" class="flex items-center {{ request()->routeIs('reseller.dashboard') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('reseller.profile') }}" class="flex items-center {{ request()->routeIs('reseller.profile*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9.003 9.003 0 0112 15a9.003 9.003 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                        </svg>
                        My Profile
                    </a>
                    <a href="{{ route('reseller.marketplace') }}" class="flex items-center {{ request()->routeIs('reseller.marketplace*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Marketplace
                    </a>
                    <a href="{{ route('reseller.map') }}" class="flex items-center {{ request()->routeIs('reseller.map*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        Map
                    </a>
                    <a href="{{ route('reseller.messages') }}" class="flex items-center {{ request()->routeIs('reseller.messages*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Messages
                        @php
                            $authUser = Auth::user();
                            $totalUnread = $authUser->conversations()
                                ->get()
                                ->sum(function ($conv) use ($authUser) {
                                    return $conv->unreadCount($authUser->id);
                                });
                        @endphp
                        @if($totalUnread > 0)
                            <span class="ml-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-[10px] font-bold text-white bg-red-600 rounded-full leading-none">
                                {{ $totalUnread }}
                            </span>
                        @endif
                    </a>
                </nav>
            </div>

            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center min-w-0">
                <div class="w-10 h-10 shrink-0 bg-[#4A6741] rounded-full overflow-hidden flex items-center justify-center text-white font-bold text-sm mr-3">
                    @if($sidebarImage)
                        <img src="{{ $sidebarImage }}" alt="Profile" class="block w-full h-full object-cover" style="object-position: {{ $sidebarProfileX }}% {{ $sidebarProfileY }}%;" />
                    @else
                        {{ strtoupper(substr(auth()->user()->name ?? 'R', 0, 1)) }}
                    @endif
                </div>
                <div>
                    <div class="font-medium text-sm">{{ auth()->user()->name ?? 'Reseller' }}</div>
                    <div class="text-xs text-[#9E8C78]">Reseller</div>
                </div>
                </div>
                <button
                    type="button"
                    @click="logoutModalOpen = true"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-[#F5F0E8] hover:bg-[#4E3D2B] transition-colors"
                    title="Log out"
                    aria-label="Log out"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H9m4 8H7a2 2 0 01-2-2V6a2 2 0 012-2h6"/>
                    </svg>
                </button>
            </div>
        </aside>

        <main id="appScrollContainer" class="ml-64 min-h-screen flex-1 bg-[#F5F0E8] p-8 overflow-y-auto">
            @yield('content')
        </main>

        <div class="fixed inset-0 flex items-center justify-center px-4" x-show="logoutModalOpen" @click="logoutModalOpen = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" style="display: none; z-index: 3000;">
            <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm" style="z-index: 3000;" @click.stop="logoutModalOpen = false"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full" style="z-index: 3001;" @click.stop>
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
    </div>

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
                const scrollContainer = document.getElementById('appScrollContainer');

                if (!scrollToTopButton) {
                    return;
                }

                const getScrollTop = () => Math.max(window.scrollY || 0, scrollContainer ? scrollContainer.scrollTop : 0);

                const toggleScrollToTopButton = () => {
                    if (getScrollTop() > 260) {
                        scrollToTopButton.classList.remove('opacity-0', 'pointer-events-none');
                    } else {
                        scrollToTopButton.classList.add('opacity-0', 'pointer-events-none');
                    }
                };

                window.addEventListener('scroll', toggleScrollToTopButton, { passive: true });
                scrollContainer?.addEventListener('scroll', toggleScrollToTopButton, { passive: true });

                scrollToTopButton.addEventListener('click', () => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    scrollContainer?.scrollTo({ top: 0, behavior: 'smooth' });
                });

                toggleScrollToTopButton();
            })();
        </script>
    @endif

    <script>
        (function () {
            const scrollContainer = document.getElementById('appScrollContainer');
            const lockTargets = [document.documentElement, document.body];
            if (scrollContainer) {
                lockTargets.push(scrollContainer);
            }

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
                    return;
                }

                isScrollLocked = shouldLock;
                lockTargets.forEach((target) => {
                    if (!target) {
                        return;
                    }

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
