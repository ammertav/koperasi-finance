<nav id="navbar"
    class="font-poppins mx-3 xl:mx-4 rounded-xl bg-white bg-opacity-90 sticky top-0 z-40 transform transition-transform duration-300">
    <div class="flex justify-around md:justify-end p-4 space-x-2 md:space-x-4">
        @if (config('demo.enabled') && $demoUsers->isNotEmpty())
            <!-- Demo role switcher -->
            <div class="flex justify-end">
                <div class="my-auto">
                    <form method="post" action="{{ route('switchUser') }}" data-page-loading>
                        @csrf
                        <div class="md:border-2 border p-1 rounded-xl md:px-4 bg-white flex items-center">
                            <span class="">
                                <i class="material-icons">switch_account</i>
                            </span>
                            <select name="user_id" onchange="this.form.submit()" title="Masuk sebagai..."
                                class="p-1 bg-white text-sm max-w-36 md:max-w-72">
                                <option value="" disabled>Masuk sebagai...</option>
                                @foreach ($demoUsers as $officeName => $officeUsers)
                                    <optgroup label="{{ $officeName }}">
                                        @foreach ($officeUsers as $demoUser)
                                            <option value="{{ $demoUser->id }}" @selected($demoUser->id === auth()->id())>
                                                {{ $demoUser->roles->pluck('code')->implode('/') }} — {{ $demoUser->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if (auth()->check())
            <div class="my-auto">
                <div class="flex space-x-2 md:space-x-4">
                    <div class="hidden md:block my-auto text-right">
                        <h1 class="text-sm font-base">
                            {{ auth()->user()->name }}
                        </h1>
                        <p class="text-xs text-gray-400">
                            {{ auth()->user()->office->name ?? '-' }}
                        </p>
                    </div>
                    <div class="my-auto">
                        <i class="material-icons">person</i>
                    </div>
                </div>
            </div>
        @endif

        <div class="md:hidden flex justify-end items-end my-auto">
            <button id="toggle-button" class="transform transition-transform duration-300">
                <!-- Hamburger icon -->
                <svg id="menu-open" class="block" width="20px" height="30px" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 6H20M4 12H20M4 18H20" stroke="#000000" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
                <!-- X icon -->
                <svg id="menu-close" class="hidden" width="20px" height="30px" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg" fill="none">
                    <path fill="#000000" fill-rule="evenodd"
                        d="M18 5a1 1 0 100-2H2a1 1 0 000 2h16zm0 4a1 1 0 100-2h-8a1 1 0 100 2h8zm1 3a1 1 0 01-1 1H2a1 1 0 110-2h16a1 1 0 011 1zm-1 5a1 1 0 100-2h-8a1 1 0 100 2h8z" />
                </svg>
            </button>
        </div>
    </div>
</nav>

<script>
    document.getElementById('toggle-button').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const menuOpen = document.getElementById('menu-open');
        const menuClose = document.getElementById('menu-close');

        sidebar.classList.toggle('-translate-x-full');

        menuOpen.classList.toggle('hidden');
        menuClose.classList.toggle('hidden');
    });
</script>

@include('layout.loading')
