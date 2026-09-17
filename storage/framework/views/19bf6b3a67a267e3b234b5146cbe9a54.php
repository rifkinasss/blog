

<?php $logoDark ??= $attributes->pluck('logo:dark'); ?>
<?php $iconTrailing ??= $attributes->pluck('icon:trailing'); ?>
<?php $iconVariant ??= $attributes->pluck('icon:variant'); ?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'iconTrailing' => null,
    'iconVariant' => 'micro',
    'name' => null,
    'logo' => null,
    'logoDark' => null,
    'alt' => null,
    'href' => '/',
    'as' => null,
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
    'iconTrailing' => null,
    'iconVariant' => 'micro',
    'name' => null,
    'logo' => null,
    'logoDark' => null,
    'alt' => null,
    'href' => '/',
    'as' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$href = $as === 'button' ? null : $href;

$classes = Flux::classes()
    ->add('h-10 min-w-0 flex items-center px-2 in-data-flux-sidebar-collapsed-desktop:w-10 in-data-flux-sidebar-collapsed-desktop:px-0 in-data-flux-sidebar-collapsed-desktop:justify-center')
    ->add('in-data-flux-sidebar-collapsed-desktop:in-data-flux-sidebar-active:absolute')
    ->add('in-data-flux-sidebar-collapsed-desktop:in-data-flux-sidebar-active:opacity-0')
    ->add($as === 'button' ? 'group select-none rounded-lg hover:bg-zinc-800/5 in-data-open:bg-zinc-800/5 dark:hover:bg-white/15 dark:in-data-open:bg-white/15' : '')
    ;

$textClasses = Flux::classes()
    ->add('min-w-0 text-sm font-medium truncate [:where(&)]:text-zinc-800 dark:[:where(&)]:text-zinc-100')
    ;

$iconClasses = Flux::classes()
    ->add('shrink-0 text-zinc-400 group-hover:text-zinc-800 in-data-open:text-zinc-800 dark:text-white/80 dark:group-hover:text-white dark:in-data-open:text-white')
    ->add('in-data-flux-sidebar-collapsed-desktop:hidden')
    ->add($iconVariant === 'outline' ? 'size-4' : '')
    ;
?>

<?php if ($name): ?>
    <?php if (isset($component)) { $__componentOriginal44af18836315d1ca840cbdae84566f63 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal44af18836315d1ca840cbdae84566f63 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button-or-link-pure','data' => ['as' => $as,'href' => $href,'attributes' => $attributes->class([ $classes, 'gap-2' ]),'dataFluxSidebarBrand' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button-or-link-pure'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($as),'href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($href),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->class([ $classes, 'gap-2' ])),'data-flux-sidebar-brand' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if ($logo instanceof \Illuminate\View\ComponentSlot): ?>
            <div <?php echo e($logo->attributes->class('flex items-center justify-center [:where(&)]:h-6 [:where(&)]:min-w-6 [:where(&)]:rounded-sm overflow-hidden shrink-0')); ?>>
                <?php echo e($logo); ?>

            </div>
        <?php else: ?>
            <div class="flex items-center justify-center h-6 min-w-6 rounded-sm overflow-hidden shrink-0">
                <?php if ($logoDark): ?>
                    <img src="<?php echo e($logo); ?>" alt="<?php echo e($alt); ?>" class="h-6 min-w-6 dark:hidden" />
                    <img src="<?php echo e($logoDark); ?>" alt="<?php echo e($alt); ?>" class="h-6 min-w-6 hidden dark:block" />
                <?php elseif ($logo): ?>
                    <img src="<?php echo e($logo); ?>" alt="<?php echo e($alt); ?>" class="h-6 min-w-6" />
                <?php else: ?>
                    <?php echo e($slot); ?>

                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="<?php echo e($textClasses); ?> in-data-flux-sidebar-collapsed-desktop:hidden"><?php echo e($name); ?></div>

        <?php if (is_string($iconTrailing) && $iconTrailing !== ''): ?>
            <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['icon' => $iconTrailing,'variant' => $iconVariant,'class' => $iconClasses]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconTrailing),'variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconVariant),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconClasses)]); ?>
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
        <?php elseif ($iconTrailing): ?>
            <?php echo e($iconTrailing); ?>

        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal44af18836315d1ca840cbdae84566f63)): ?>
<?php $attributes = $__attributesOriginal44af18836315d1ca840cbdae84566f63; ?>
<?php unset($__attributesOriginal44af18836315d1ca840cbdae84566f63); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal44af18836315d1ca840cbdae84566f63)): ?>
<?php $component = $__componentOriginal44af18836315d1ca840cbdae84566f63; ?>
<?php unset($__componentOriginal44af18836315d1ca840cbdae84566f63); ?>
<?php endif; ?>
<?php else: ?>
    <?php if (isset($component)) { $__componentOriginal44af18836315d1ca840cbdae84566f63 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal44af18836315d1ca840cbdae84566f63 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button-or-link-pure','data' => ['as' => $as,'href' => $href,'attributes' => $attributes->class($classes),'dataFluxSidebarBrand' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button-or-link-pure'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($as),'href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($href),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->class($classes)),'data-flux-sidebar-brand' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if ($logo instanceof \Illuminate\View\ComponentSlot): ?>
            <div <?php echo e($logo->attributes->class('flex items-center justify-center [:where(&)]:h-6 [:where(&)]:min-w-6 [:where(&)]:rounded-sm overflow-hidden shrink-0')); ?>>
                <?php echo e($logo); ?>

            </div>
        <?php else: ?>
            <div class="flex items-center justify-center h-6 rounded-sm overflow-hidden shrink-0">
                <?php if ($logoDark): ?>
                    <img src="<?php echo e($logo); ?>" alt="<?php echo e($alt); ?>" class="h-6 dark:hidden" />
                    <img src="<?php echo e($logoDark); ?>" alt="<?php echo e($alt); ?>" class="h-6 hidden dark:block" />
                <?php elseif ($logo): ?>
                    <img src="<?php echo e($logo); ?>" alt="<?php echo e($alt); ?>" class="h-6" />
                <?php else: ?>
                    <?php echo e($slot); ?>

                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (is_string($iconTrailing) && $iconTrailing !== ''): ?>
            <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['icon' => $iconTrailing,'variant' => $iconVariant,'class' => $iconClasses]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconTrailing),'variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconVariant),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconClasses)]); ?>
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
        <?php elseif ($iconTrailing): ?>
            <?php echo e($iconTrailing); ?>

        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal44af18836315d1ca840cbdae84566f63)): ?>
<?php $attributes = $__attributesOriginal44af18836315d1ca840cbdae84566f63; ?>
<?php unset($__attributesOriginal44af18836315d1ca840cbdae84566f63); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal44af18836315d1ca840cbdae84566f63)): ?>
<?php $component = $__componentOriginal44af18836315d1ca840cbdae84566f63; ?>
<?php unset($__componentOriginal44af18836315d1ca840cbdae84566f63); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH /Users/rifkinasss/Documents/workspace/Startup/NasLabs/internal-projects/Blog/vendor/livewire/flux/stubs/resources/views/flux/sidebar/brand.blade.php ENDPATH**/ ?>