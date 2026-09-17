

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'outline',
    'size' => null,
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
    'variant' => 'outline',
    'size' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$classes = Flux::classes()
    ->add('border')
    ->add(match ($variant) {
        'soft' => '[:where(&)]:bg-black/[0.02] [:where(&)]:border-black/[0.025] dark:[:where(&)]:bg-white/[0.06] dark:[:where(&)]:border-white/[0.065]',
        default => '[:where(&)]:bg-white [:where(&)]:border-zinc-200 dark:[:where(&)]:bg-white/10 dark:[:where(&)]:border-white/10',
    })
    ->add(match ($size) {
        default => '[:where(&)]:p-6 [:where(&)]:rounded-xl [--flux-bleed:1.5rem]',
        'sm' => '[:where(&)]:p-4 [:where(&)]:rounded-lg [--flux-bleed:1rem]',
    })
    ;
?>

<div <?php echo e($attributes->class($classes)); ?> data-flux-card>
    <?php echo e($slot); ?>

</div>
<?php /**PATH /Users/rifkinasss/Documents/workspace/Startup/NasLabs/internal-projects/Blog/vendor/livewire/flux/src/../stubs/resources/views/flux/card/index.blade.php ENDPATH**/ ?>