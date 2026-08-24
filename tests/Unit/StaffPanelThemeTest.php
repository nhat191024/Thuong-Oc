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
