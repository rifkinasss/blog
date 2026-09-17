<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-7xl px-4 md:px-12">
        <section class="border-b border-[#c5c5d7]/30 py-10 md:py-14">
            <div class="flex items-center gap-2 text-[10px] text-[#757686]">
                <a href="<?php echo e(route('home')); ?>" class="hover:text-[#1a35ca]">NasLabs</a>
                <span>/</span>
                <span><?php echo e(__('blog.archive_breadcrumb')); ?></span>
                <span class="rounded bg-[#dfe0ff] px-2 py-1 font-semibold text-[#1a35ca]"><?php echo e(__('blog.archive_published_count', ['count' => $articles->total()])); ?></span>
            </div>
            <div class="mt-8 flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <div>
                    <h1 class="font-display text-4xl font-bold tracking-tight md:text-5xl"><?php echo e(__('blog.archive_heading')); ?></h1>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-[#444655] dark:text-[#c5c5d7]"><?php echo e(__('blog.archive_description')); ?></p>
                </div>
                <span class="font-mono text-[10px] text-[#757686]"><?php echo e(__('blog.archive_public_label')); ?></span>
            </div>
            <form action="<?php echo e(route('articles.index')); ?>" class="mt-8 flex rounded-lg border border-[#c5c5d7]/40 bg-white p-1 shadow-sm dark:bg-[#171d34]">
                <span class="grid w-11 place-items-center text-[#1a35ca]">⌕</span>
                <input name="q" value="<?php echo e(request('q')); ?>" placeholder="<?php echo e(__('blog.archive_search_placeholder')); ?>" class="min-w-0 flex-1 border-0 bg-transparent px-2 py-3 text-sm outline-none">
                <button class="rounded bg-[#1a35ca] px-5 py-2 text-xs font-semibold text-white"><?php echo e(__('blog.archive_search_action')); ?></button>
            </form>
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <a href="<?php echo e(route('articles.index')); ?>" class="rounded-full bg-[#1a35ca] px-3 py-1.5 text-[11px] font-semibold text-white"><?php echo e(__('blog.archive_all')); ?></a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['Engineering', 'Homelab', 'Data', 'Learning']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <a href="<?php echo e(route('categories.show', str($category)->slug())); ?>" class="rounded-full bg-[#eaedff] px-3 py-1.5 text-[11px] text-[#444655] dark:bg-[#202947] dark:text-[#c5c5d7]"><?php echo e($category); ?></a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        <div class="flex items-center justify-between border-b border-[#c5c5d7]/30 py-5">
            <p class="font-mono text-[10px] uppercase tracking-widest text-[#757686]"><?php echo e(__('blog.archive_all')); ?></p>
            <span class="text-xs text-[#757686]"><?php echo e(__('blog.archive_result_count', ['start' => $articles->firstItem() ?? 0, 'end' => $articles->lastItem() ?? 0, 'total' => $articles->total()])); ?></span>
        </div>

        <section class="grid gap-5 py-8 sm:grid-cols-2 lg:grid-cols-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <a href="<?php echo e(route('articles.show', $article)); ?>" class="group overflow-hidden rounded-lg border border-[#c5c5d7]/30 bg-white transition-colors hover:border-[#3b52e2] dark:bg-[#171d34]">
                    <div class="relative">
                        <img src="<?php echo e(asset('storage/'.$article->cover_image)); ?>" alt="" class="h-48 w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
                        <span class="absolute left-3 top-3 rounded bg-white/90 px-2 py-1 text-[9px] font-semibold uppercase tracking-wider text-[#1a35ca]"><?php echo e($article->category?->name ?? __('blog.uncategorized')); ?></span>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center gap-2 text-[10px] text-[#757686]"><span><?php echo e($article->published_at->format('d M Y')); ?></span><span>·</span><span><?php echo e(__('blog.reading_time', ['minutes' => $article->readingTime()])); ?></span></div>
                        <h2 class="mt-3 font-display text-xl font-semibold leading-tight group-hover:text-[#1a35ca]"><?php echo e($article->title); ?></h2>
                        <p class="mt-3 line-clamp-3 text-sm leading-6 text-[#444655] dark:text-[#c5c5d7]"><?php echo e($article->excerpt); ?></p>
                        <div class="mt-5 flex items-center justify-between text-xs"><span class="text-[#757686]">#<?php echo e(str($article->category?->name ?? __('blog.uncategorized'))->slug()); ?></span><span class="font-semibold text-[#1a35ca]"><?php echo e(__('blog.read')); ?> →</span></div>
                    </div>
                </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="col-span-full py-16 text-center text-sm text-[#757686]"><?php echo e(__('blog.no_articles')); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        <div class="flex justify-center border-t border-[#c5c5d7]/30 py-6"><?php echo e($articles->links()); ?></div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.blog', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/rifkinasss/Documents/workspace/Startup/NasLabs/internal-projects/Blog/resources/views/blog/index.blade.php ENDPATH**/ ?>