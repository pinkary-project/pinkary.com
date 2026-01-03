<div class="mb-8 flex justify-between space-x-2">
    @foreach($menuItems as $menuItemKey => $menuItem)
        <x-home-menu-link
            :route="$menuItem['route']"
            :label="$menuItem['label']"
            :icon="$menuItem['icon'] ?? null"
            :key="$menuItemKey"
        />
    @endforeach
</div>
