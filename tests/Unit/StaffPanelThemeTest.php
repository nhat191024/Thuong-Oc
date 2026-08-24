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
