<?php

function kirpi_navigation_group_meta(string $groupKey): array
{
    $groupKey = strtolower(trim($groupKey));

    $meta = [
        'title' => '',
        'icon' => 'ti ti-point',
        'weight' => 500,
    ];

    if ($groupKey === 'monitoring') {
        $meta['title'] = 'Monitoring / Izleme';
        $meta['icon'] = 'ti ti-radar';
        $meta['weight'] = 900;
    }

    return $meta;
}

function kirpi_collect_module_menu_items(): array
{
    $items = [];

    foreach (kirpi_list_modules() as $module) {
        if (($module['enabled'] ?? true) !== true) {
            continue;
        }

        $moduleKey = (string) ($module['key'] ?? '');
        $moduleMenus = (array) ($module['menu'] ?? []);
        foreach ($moduleMenus as $menuItem) {
            if (!is_array($menuItem)) {
                continue;
            }

            $title = trim((string) ($menuItem['title'] ?? ''));
            $url = trim((string) ($menuItem['url'] ?? ''));
            if ($title === '' || $url === '') {
                continue;
            }

            $items[] = [
                'module' => $moduleKey,
                'title' => $title,
                'icon' => trim((string) ($menuItem['icon'] ?? 'ti ti-point')),
                'url' => $url,
                'permission' => isset($menuItem['permission']) && trim((string) $menuItem['permission']) !== ''
                    ? trim((string) $menuItem['permission'])
                    : null,
                'placement' => strtolower(trim((string) ($menuItem['placement'] ?? 'management'))),
                'group' => strtolower(trim((string) ($menuItem['group'] ?? 'default'))),
                'weight' => (int) ($menuItem['weight'] ?? 500),
            ];
        }
    }

    usort($items, static function (array $a, array $b): int {
        $weightCompare = ((int) ($a['weight'] ?? 500)) <=> ((int) ($b['weight'] ?? 500));
        if ($weightCompare !== 0) {
            return $weightCompare;
        }

        return strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
    });

    return $items;
}

function kirpi_navigation_menu_tree(): array
{
    $topItems = [];
    $managementDirectItems = [];
    $managementGroups = [];

    foreach (kirpi_collect_module_menu_items() as $item) {
        $placement = (string) ($item['placement'] ?? 'management');
        if ($placement === 'top') {
            $topItems[] = [
                'title' => (string) ($item['title'] ?? ''),
                'icon' => (string) ($item['icon'] ?? 'ti ti-point'),
                'url' => (string) ($item['url'] ?? ''),
                'permission' => $item['permission'] ?? null,
                'weight' => (int) ($item['weight'] ?? 500),
            ];
            continue;
        }

        $groupKey = (string) ($item['group'] ?? 'default');
        if ($groupKey === '' || $groupKey === 'default') {
            $managementDirectItems[] = [
                'title' => (string) ($item['title'] ?? ''),
                'icon' => (string) ($item['icon'] ?? 'ti ti-point'),
                'url' => (string) ($item['url'] ?? ''),
                'permission' => $item['permission'] ?? null,
                'weight' => (int) ($item['weight'] ?? 500),
            ];
            continue;
        }

        if (!isset($managementGroups[$groupKey])) {
            $groupMeta = kirpi_navigation_group_meta($groupKey);
            $managementGroups[$groupKey] = [
                'title' => $groupMeta['title'] !== '' ? $groupMeta['title'] : ucfirst($groupKey),
                'icon' => $groupMeta['icon'],
                'weight' => (int) ($groupMeta['weight'] ?? 500),
                'children' => [],
            ];
        }

        $managementGroups[$groupKey]['children'][] = [
            'title' => (string) ($item['title'] ?? ''),
            'icon' => (string) ($item['icon'] ?? 'ti ti-point'),
            'url' => (string) ($item['url'] ?? ''),
            'permission' => $item['permission'] ?? null,
            'weight' => (int) ($item['weight'] ?? 500),
        ];
    }

    usort($topItems, static fn(array $a, array $b): int => (($a['weight'] ?? 500) <=> ($b['weight'] ?? 500)) ?: strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? '')));
    usort($managementDirectItems, static fn(array $a, array $b): int => (($a['weight'] ?? 500) <=> ($b['weight'] ?? 500)) ?: strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? '')));

    foreach ($managementGroups as &$groupItem) {
        usort($groupItem['children'], static fn(array $a, array $b): int => (($a['weight'] ?? 500) <=> ($b['weight'] ?? 500)) ?: strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? '')));
    }
    unset($groupItem);

    uasort($managementGroups, static fn(array $a, array $b): int => (($a['weight'] ?? 500) <=> ($b['weight'] ?? 500)) ?: strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? '')));

    $managementChildren = $managementDirectItems;
    foreach ($managementGroups as $group) {
        $managementChildren[] = [
            'title' => $group['title'],
            'icon' => $group['icon'],
            'children' => $group['children'],
            'weight' => (int) ($group['weight'] ?? 500),
        ];
    }

    usort($managementChildren, static fn(array $a, array $b): int => (($a['weight'] ?? 500) <=> ($b['weight'] ?? 500)) ?: strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? '')));

    $menu = [
        [
            'title' => 'Dashboard',
            'icon' => 'ti ti-home',
            'url' => 'dashboard/view',
            'permission' => null,
            'weight' => 1,
        ],
    ];

    foreach ($topItems as $item) {
        $menu[] = $item;
    }

    $menu[] = [
        'title' => 'Yonetim',
        'icon' => 'ti ti-settings',
        'children' => $managementChildren,
        'weight' => 999,
    ];

    return $menu;
}
