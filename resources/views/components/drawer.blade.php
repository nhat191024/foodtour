@props(['listItems'])

<div class="drawer z-10">
    <input id="my-drawer" class="drawer-toggle" type="checkbox" />
    <div class="drawer-content">
        <label class="btn btn-ghost btn-circle" for="my-drawer">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
        </label>
    </div>
    <div class="drawer-side">
        <label class="drawer-overlay" for="my-drawer" aria-label="close sidebar"></label>
        <ul class="menu menu-lg bg-base-200 text-base-content min-h-full w-72 p-4 dark:bg-gray-800">
            <x-utils.application-logo class="h-15 block w-auto fill-current text-gray-800 dark:text-gray-200" />
            <div class="divider font-bold">{{ config('app.name', 'Laravel') }}</div>
            <li>
                <h2 class="my-2 text-lg font-bold text-gray-800 dark:text-gray-400">{{ __('Function') }}</h2>
                <ul>
                    @if (isset($listItems))
                        @foreach ($listItems as $item)
                            <li>
                                <x-actions.sidebar-link :href="route($item['route'])" :active="request()->routeIs($item['route'])">
                                    <x-icons.user class="h-5 w-5" />
                                    {{ __($item['name']) }}
                                </x-actions.sidebar-link>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </li>
        </ul>
    </div>
</div>
