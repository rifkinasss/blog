

<?php foreach (([ 'variant' ]) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'default',
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
    'variant' => 'default',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$variant = $variant !== 'default' && Flux::componentExists('select.variants.' . $variant)
    ? 'custom'
    : 'default';
?>

<?php if (!Flux::componentExists($name = 'select.group.variants.' . $variant)) throw new \Exception("Flux component [{$name}] does not exist."); ?><?php if (isset($component)) { $__componentOriginal57fefc6d3df0e1ba12c5f6e15dec7a96 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57fefc6d3df0e1ba12c5f6e15dec7a96 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve([
    'view' => (app()->version() >= 12 ? hash('xxh128', 'flux') : md5('flux')) . '::' . 'select.group.variants.' . $variant,
    'data' => $__env->getCurrentComponentData(),
] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::' . 'select.group.variants.' . $variant); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes($attributes->getAttributes()); ?><?php echo e($slot); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal57fefc6d3df0e1ba12c5f6e15dec7a96)): ?>
<?php $attributes = $__attributesOriginal57fefc6d3df0e1ba12c5f6e15dec7a96; ?>
<?php unset($__attributesOriginal57fefc6d3df0e1ba12c5f6e15dec7a96); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal57fefc6d3df0e1ba12c5f6e15dec7a96)): ?>
<?php $component = $__componentOriginal57fefc6d3df0e1ba12c5f6e15dec7a96; ?>
<?php unset($__componentOriginal57fefc6d3df0e1ba12c5f6e15dec7a96); ?>
<?php endif; ?>
<?php /**PATH /Users/rifkinasss/Documents/workspace/Startup/NasLabs/internal-projects/Blog/vendor/livewire/flux/stubs/resources/views/flux/select/group/index.blade.php ENDPATH**/ ?>