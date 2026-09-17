

<?php $onLabel ??= $attributes->pluck('on:label'); ?>
<?php $offLabel ??= $attributes->pluck('off:label'); ?>
<?php $onIcon ??= $attributes->pluck('on:icon'); ?>
<?php $offIcon ??= $attributes->pluck('off:icon'); ?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'outline',
    'checked' => null,
    'size' => 'base',
    'name' => null,
    'icon' => null,
    'label' => null,
    'color' => null,
    'inset' => null,
    'onLabel' => null,
    'offLabel' => null,
    'onIcon' => null,
    'offIcon' => null,
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
    'checked' => null,
    'size' => 'base',
    'name' => null,
    'icon' => null,
    'label' => null,
    'color' => null,
    'inset' => null,
    'onLabel' => null,
    'offLabel' => null,
    'onIcon' => null,
    'offIcon' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
// We only want to show the name attribute if it has been set manually,
// but not if it has been inferred from the wire:model attribute...
$showName = isset($name);

if (! isset($name)) {
    $name = $attributes->whereStartsWith('wire:model')->first();
}

$onIcon = is_string($onIcon) && $onIcon !== '' ? $onIcon : null;
$offIcon = is_string($offIcon) && $offIcon !== '' ? $offIcon : null;

$square = $slot->isEmpty() && ! $onLabel && ! $label;
$hasIcon = $icon || $onIcon;

$iconClasses = Flux::classes()
    ->add(match ($variant) {
        'outline' => 'text-zinc-500/85 dark:text-zinc-300/80 in-data-checked:text-(--color-accent-content) dark:in-data-checked:text-(--color-accent-content)',
        'filled' => 'text-zinc-500/85 dark:text-zinc-300/80 in-data-checked:text-(--color-accent-content) dark:in-data-checked:text-(--color-accent-content)',
        'ghost' => 'text-zinc-500/85 dark:text-zinc-300/80 in-data-checked:text-(--color-accent-content) dark:in-data-checked:text-(--color-accent-content)',
        'subtle' => join(' ', [
            'text-zinc-400/90 group-hover:text-zinc-500 in-data-checked:text-zinc-500 in-data-checked:group-hover:text-zinc-800',
            'dark:text-zinc-500/90 dark:group-hover:text-zinc-400 dark:in-data-checked:text-zinc-400 dark:in-data-checked:group-hover:text-white',
        ])
    })
    ->add($square && $size !== 'xs' ? 'size-5' : 'size-4')
    ->add($attributes->pluck('icon:class'))
    ;

$classes = Flux::classes()
    ->add('group relative inline-flex items-center font-medium justify-center whitespace-nowrap outline-offset-2')
    ->add('transition touch-manipulation')
    ->add('[&[disabled]]:opacity-50 dark:[&[disabled]]:opacity-50 [&[disabled]]:shadow-none [&[disabled]]:cursor-default [&[disabled]]:pointer-events-none')
    ->add(match ($size) {
        'base' => 'h-10 text-sm rounded-lg gap-2' . ' ' . ($square ? 'w-10' : ($hasIcon ? 'ps-3 pe-4' : 'px-4')),
        'sm' => 'h-8 text-sm rounded-md gap-2' . ' ' . ($square ? 'w-8' : ($hasIcon ? 'ps-2 pe-3' : 'px-3')),
        'xs' => 'h-6 text-xs rounded-md gap-1' . ' ' . ($square ? 'w-6' : ($hasIcon ? 'ps-1 pe-2' : 'px-2')),
    })
    ->add($inset ? match ($size) {
        'base' => $square
            ? Flux::applyInset($inset, top: '-mt-2.5', right: '-me-2.5', bottom: '-mb-2.5', left: '-ms-2.5')
            : Flux::applyInset($inset, top: '-mt-2.5', right: '-me-4', bottom: '-mb-3', left: ($hasIcon ? '-ms-3' : '-ms-4')),
        'sm' => $square
            ? Flux::applyInset($inset, top: '-mt-1.5', right: '-me-1.5', bottom: '-mb-1.5', left: '-ms-1.5')
            : Flux::applyInset($inset, top: '-mt-1.5', right: '-me-3', bottom: '-mb-1.5', left: ($hasIcon ? '-ms-2' : '-ms-3')),
        'xs' => $square
            ? Flux::applyInset($inset, top: '-mt-1', right: '-me-1', bottom: '-mb-1', left: '-ms-1')
            : Flux::applyInset($inset, top: '-mt-1', right: '-me-2', bottom: '-mb-1', left: ($hasIcon ? '-ms-1' : '-ms-2')),
    } : '')
    ->add(match ($variant) {
        'outline' => 'bg-white hover:bg-zinc-50 dark:bg-zinc-700 dark:hover:bg-zinc-600/75',
        'filled' => 'bg-zinc-800/5 hover:bg-zinc-800/10 dark:bg-white/10 dark:hover:bg-white/20',
        'ghost' => 'bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15',
        'subtle' => 'bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15',
    })
    ->add(match ($variant) { // Text color...
        'outline' => 'text-zinc-600/85 data-checked:text-zinc-800 dark:text-zinc-300/95 dark:data-checked:text-white',
        'filled' => 'text-zinc-600/85 data-checked:text-zinc-800 dark:text-zinc-300/95 dark:data-checked:text-white',
        'ghost' => 'text-zinc-600/85 data-checked:text-zinc-800 dark:text-zinc-300/95 dark:data-checked:text-white',
        'subtle' => join(' ', [
            'text-zinc-500/85 hover:text-zinc-500 data-checked:text-zinc-500 data-checked:hover:text-zinc-800',
            'dark:text-zinc-400/80 dark:hover:text-zinc-300 dark:data-checked:text-zinc-400 dark:data-checked:hover:text-white',
        ])
    })
    ->add(match ($variant) {
        'outline' => 'border border-zinc-200 hover:border-zinc-200 border-b-zinc-300/80 dark:border-zinc-600 dark:hover:border-zinc-600',
        default => '',
    })
    ->add(match ($variant) {
        'outline' => match ($size) {
            'base', 'sm' => 'shadow-xs',
            'xs' => 'shadow-none',
        },
        default => '',
    })
    ;
?>

<?php if (isset($component)) { $__componentOriginal15d7f93649b27a799ff045ec5bd2eed0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal15d7f93649b27a799ff045ec5bd2eed0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::accent','data' => ['color' => $color,'class' => 'contents']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::accent'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($color),'class' => 'contents']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php if (isset($component)) { $__componentOriginal1bac653003a70249c8d1bced240ca490 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1bac653003a70249c8d1bced240ca490 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::with-tooltip','data' => ['attributes' => $attributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::with-tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <ui-switch <?php echo e($attributes->class($classes)); ?> <?php if($showName): ?> name="<?php echo e($name); ?>" <?php endif; ?> <?php if($checked): ?> checked data-checked <?php endif; ?> data-flux-control data-flux-toggle>
            <?php if ((is_string($icon) && $icon !== '') || $onIcon): ?>
                <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['icon' => $onIcon ?? $icon,'variant' => 'solid','class' => $iconClasses->add('hidden group-data-checked:block')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onIcon ?? $icon),'variant' => 'solid','class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconClasses->add('hidden group-data-checked:block'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2)): ?>
<?php $attributes = $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2; ?>
<?php unset($__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2)): ?>
<?php $component = $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2; ?>
<?php unset($__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['icon' => $offIcon ?? $onIcon ?? $icon,'variant' => 'outline','class' => $iconClasses->add('group-data-checked:hidden')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($offIcon ?? $onIcon ?? $icon),'variant' => 'outline','class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconClasses->add('group-data-checked:hidden'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2)): ?>
<?php $attributes = $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2; ?>
<?php unset($__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2)): ?>
<?php $component = $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2; ?>
<?php unset($__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2); ?>
<?php endif; ?>
            <?php elseif ($icon): ?>
                <?php echo e($icon); ?>

            <?php endif; ?>

            <?php if ($slot->isNotEmpty() || $onLabel || $label): ?>
                <?php $onLabel = $slot->isNotEmpty() ? $slot : ($onLabel ?? $label); ?>

                <span class="group-data-checked:hidden"><?php echo e($offLabel ?? $onLabel); ?></span>
                <span class="hidden group-data-checked:block"><?php echo e($onLabel); ?></span>
            <?php endif; ?>
        </ui-switch>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1bac653003a70249c8d1bced240ca490)): ?>
<?php $attributes = $__attributesOriginal1bac653003a70249c8d1bced240ca490; ?>
<?php unset($__attributesOriginal1bac653003a70249c8d1bced240ca490); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1bac653003a70249c8d1bced240ca490)): ?>
<?php $component = $__componentOriginal1bac653003a70249c8d1bced240ca490; ?>
<?php unset($__componentOriginal1bac653003a70249c8d1bced240ca490); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal15d7f93649b27a799ff045ec5bd2eed0)): ?>
<?php $attributes = $__attributesOriginal15d7f93649b27a799ff045ec5bd2eed0; ?>
<?php unset($__attributesOriginal15d7f93649b27a799ff045ec5bd2eed0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal15d7f93649b27a799ff045ec5bd2eed0)): ?>
<?php $component = $__componentOriginal15d7f93649b27a799ff045ec5bd2eed0; ?>
<?php unset($__componentOriginal15d7f93649b27a799ff045ec5bd2eed0); ?>
<?php endif; ?>
<?php /**PATH /Users/rifkinasss/Documents/workspace/Startup/NasLabs/internal-projects/Blog/vendor/livewire/flux/stubs/resources/views/flux/toggle.blade.php ENDPATH**/ ?>