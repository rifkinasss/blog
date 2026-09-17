

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'country' => null,
    'src' => null,
    'alt' => '',
    'size' => 'sm',
    'circle' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'country' => null,
    'src' => null,
    'alt' => '',
    'size' => 'sm',
    'circle' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$country = is_string($country) ? strtoupper(trim($country)) : null;
$src ??= $country ? Flux::flagUrl($country) : null;

$classes = Flux::classes()
    ->add(match ($size) {
        'xl' => '[:where(&)]:w-12',
        'lg' => '[:where(&)]:w-10',
        'md' => '[:where(&)]:w-8',
        default => '[:where(&)]:w-6',
        'xs' => '[:where(&)]:w-5',
    })
    ->add($circle ? 'aspect-square rounded-full' : 'aspect-[3/2] rounded-[2px]')
    ->add('relative isolate block flex-none overflow-hidden bg-zinc-100 dark:bg-zinc-800')
    ->add([
        'after:absolute after:inset-0 after:inset-ring-[1px] after:inset-ring-black/7 dark:after:inset-ring-white/10',
        $circle ? 'after:rounded-full' : 'after:rounded-[2px]',
    ]);
?>

<?php if ($src): ?>
    <span
        <?php echo e($attributes->class($classes)->merge([
            'data-flux-flag' => '',
            'data-country' => $country,
        ])); ?>

    >
        <img
            src="<?php echo e($src); ?>"
            alt="<?php echo e($alt); ?>"
            loading="lazy"
            decoding="async"
            class="size-full object-cover"
        >
    </span>
<?php else: ?>
    <span
        role="img"
        <?php if($alt): ?> aria-label="<?php echo e($alt); ?>" <?php else: ?> aria-hidden="true" <?php endif; ?>
        <?php echo e($attributes->class($classes)->merge([
            'data-flux-flag' => '',
            'data-country' => $country,
        ])); ?>

    >
        <span class="flex size-full items-center justify-center">
            <?php if (isset($component)) { $__componentOriginale02ab0f625e6b2501fa40e35388d0046 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale02ab0f625e6b2501fa40e35388d0046 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.globe-alt','data' => ['variant' => 'micro','class' => 'size-2/3 text-zinc-400 dark:text-zinc-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.globe-alt'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'micro','class' => 'size-2/3 text-zinc-400 dark:text-zinc-500']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale02ab0f625e6b2501fa40e35388d0046)): ?>
<?php $attributes = $__attributesOriginale02ab0f625e6b2501fa40e35388d0046; ?>
<?php unset($__attributesOriginale02ab0f625e6b2501fa40e35388d0046); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale02ab0f625e6b2501fa40e35388d0046)): ?>
<?php $component = $__componentOriginale02ab0f625e6b2501fa40e35388d0046; ?>
<?php unset($__componentOriginale02ab0f625e6b2501fa40e35388d0046); ?>
<?php endif; ?>
        </span>
    </span>
<?php endif; ?>
<?php /**PATH /Users/rifkinasss/Documents/workspace/Startup/NasLabs/internal-projects/Blog/vendor/livewire/flux/stubs/resources/views/flux/flag.blade.php ENDPATH**/ ?>