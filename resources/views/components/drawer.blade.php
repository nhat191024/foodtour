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
            <x-utils.application-logo class="h-15 block w-auto" />
            <div class="divider font-bold">{{ config('app.name', 'Food Tour') }}</div>
            <li>
                <h2 class="my-2 text-lg font-bold text-gray-800 dark:text-gray-400" onclick="showDetail(-1);">{{ __('Yêu thích') }}</h2>
            </li>
            <li>
                <h2 class="my-2 text-lg font-bold text-gray-800 dark:text-gray-400">{{ __('Lịch sử') }}</h2>
                <ul>
                    @if (isset($listItems))
                        @foreach ($listItems as $item)
                            <li class="flex flex-row items-center gap-2">
                                <x-actions.sidebar-link
                                    :href="Route::has($item['route']??'')?route($item['route']):'#'" :active="false"
                                    onclick="showDetail({{ array_key_exists('id', $item) ? $item['id'] : null }})"
                                    id="tour-name-{{ array_key_exists('id', $item) ? $item['id'] : null }}"
                                >
                                    {{ __($item['name']) }}
                                </x-actions.sidebar-link>

                                <a class="{{ array_key_exists('id', $item) ? '' : 'hidden' }}" href="#" onclick="openEditTourModal({{ array_key_exists('id', $item) ? $item['id'] : null }})" class="mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                    </svg>
                                </a>

                            </li>
                        @endforeach
                    @endif
                </ul>
            </li>
        </ul>
    </div>
</div>
