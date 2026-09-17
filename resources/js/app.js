window.initMarkdownEditor = async (textarea, wire) => {
    if (!textarea || textarea.dataset.editorReady) return;

    textarea.dataset.editorReady = 'true';
    try {
        const editorModules = await import('ckeditor5');
        await import('ckeditor5/ckeditor5.css');
        const { BlockQuote, Bold, ClassicEditor, CodeBlock, Essentials, Heading, Italic, Link, List, Markdown, Paragraph } = editorModules;
        const editor = await ClassicEditor.create(textarea, {
            licenseKey: 'GPL',
            plugins: [Essentials, Paragraph, Heading, Bold, Italic, Link, List, BlockQuote, CodeBlock, Markdown],
            toolbar: ['undo', 'redo', '|', 'heading', '|', 'bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', '|', 'blockQuote', 'codeBlock'],
        });

        editor.model.document.on('change:data', () => wire.set('content', editor.getData()));
        document.addEventListener('livewire:navigating', () => editor.destroy(), { once: true });
    } catch (error) {
        textarea.dataset.editorReady = '';
        console.error('CKEditor initialization failed.', error);
    }
};
