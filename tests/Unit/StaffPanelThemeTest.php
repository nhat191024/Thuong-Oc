<?php

it('adds subtle borders to staff menu items in dark mode', function () {
    $component = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/staff/partials/MenuPanel.vue');

    expect(substr_count($component, 'border border-base-300/80 bg-base-100'))->toBe(3)
        ->and(substr_count($component, 'hover:border-primary/50'))->toBe(3);
});

it('adds subtle borders to ordered and cart items in dark mode', function () {
    $component = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/staff/partials/OrderPanel.vue');

    expect(substr_count($component, 'border border-base-300/80 bg-base-100'))->toBe(2);
});

it('groups staff navigation actions into a tablet-friendly management menu', function () {
    $component = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/staff/index.vue');

    expect($component)->toContain('dropdown dropdown-end')
        ->and($component)->toContain('Quản lý')
        ->and($component)->toContain("route('history.index')")
        ->and($component)->toContain("route('staff.stock.index')")
        ->and($component)->toContain("route('staff.inventory.index')");
});

it('uses green yellow and red levels for inventory units', function () {
    $component = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/staff/inventory.vue');

    expect($component)->toContain('food.quantity < food.red_threshold')
        ->and($component)->toContain('food.quantity < food.yellow_threshold')
        ->and($component)->toContain('Cài ngưỡng')
        ->and($component)->toContain("route('staff.inventory.thresholds.update'")
        ->and($component)->toContain('border-success bg-success/5')
        ->and($component)->toContain('border-warning bg-warning/5')
        ->and($component)->toContain('border-error bg-error/5');
});

it('shows dynamic inventory units and restock costs', function () {
    $inventory = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/staff/inventory.vue');
    $history = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/staff/inventoryHistory.vue');

    expect($inventory)->toContain('food.unit_symbol')
        ->and($inventory)->toContain('estimatedTotalCost')
        ->and($inventory)->toContain('purchase_price')
        ->and($history)->toContain('item.unit_price')
        ->and($history)->toContain('item.total_cost')
        ->and($history)->toContain('item.unit_symbol');
});

it('shows inventory alerts on the staff management entry points', function () {
    $component = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/staff/index.vue');

    expect(substr_count($component, 'props.redInventoryCount > 0'))->toBe(2)
        ->and(substr_count($component, 'ExclamationTriangleIcon'))->toBeGreaterThanOrEqual(3)
        ->and($component)->toContain('{{ props.redInventoryCount }}');
});

it('filters and sorts inventory by red yellow and green levels', function () {
    $component = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/staff/inventory.vue');

    expect($component)->toContain('v-model="levelFilter"')
        ->and($component)->toContain('Đỏ — Thiếu hụt')
        ->and($component)->toContain('Vàng — Sắp hết')
        ->and($component)->toContain('Xanh — Ổn định')
        ->and($component)->toContain('red: 0')
        ->and($component)->toContain('yellow: 1')
        ->and($component)->toContain('green: 2')
        ->and($component)->toContain('firstFood.id - secondFood.id');
});
