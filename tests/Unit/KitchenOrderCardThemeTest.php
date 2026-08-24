<?php

it('keeps overdue kitchen cards readable in every theme', function () {
    $component = file_get_contents(dirname(__DIR__, 2).'/resources/js/pages/kitchen/partials/OrderCard.vue');

    expect($component)
        ->toContain('bg-red-100 border-2 border-red-300 text-red-950')
        ->toContain('bg-yellow-100 border-2 border-yellow-300 text-yellow-950')
        ->toContain("return 'text-red-700'")
        ->toContain("return 'text-yellow-800'")
        ->toContain('border border-white/15 bg-black px-6 py-3 text-white');
});

it('uses a darker primary color for dark mode', function () {
    $stylesheet = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');

    expect($stylesheet)->toContain('--color-primary: oklch(0.52 0.18 25);');
});
