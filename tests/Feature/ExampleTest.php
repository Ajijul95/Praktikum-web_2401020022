<?php

test('the application environment is testing', function () {
    expect(app()->environment())->toBe('testing');
});
