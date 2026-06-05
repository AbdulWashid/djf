document.addEventListener('livewire:initialized', () => {
    const schemaScript = document.getElementById('structured-data-jsonld');

    Livewire.on('schema-updated', (data) => {
        const payload = data[0] || {};
        console.log(schemaScript, data);

        if (!schemaScript) {
            console.log('return ');
            return;
        }

        const schemas = Array.isArray(payload.schemas) ?
            payload.schemas :
            (payload.schema ? [payload.schema] : []);

        console.log('Structured data updated:', JSON.stringify(schemas));
        schemaScript.textContent = JSON.stringify(schemas);

    });
});

document.addEventListener('livewire:initialized', () => {
    const component = Livewire.find(
        document.querySelector('[wire\\:id]').getAttribute('wire:id')
    );
    function initSelect2() {
        $('#location-select').select2({
            placeholder: 'Select Location',
            allowClear: true
        }).on('change', function(e) {
        component.set('location', $(this).val());

        });
        $('#category-select').select2({
            placeholder: 'Select Category',
            allowClear: true
        }).on('change', function(e) {
        component.set('category', $(this).val());

        });
    }

    initSelect2();

    Livewire.on('url-updated', (data) => {
        history.pushState(null, '', data[0].url);
    });

    Livewire.on('seo-updated', (data) => {
        const payload = data[0] || {};

        if (payload.title) {
            document.title = payload.title;
        }

        if (payload.description) {
            let metaDescription = document.querySelector('meta[name="description"]');

            if (!metaDescription) {
                metaDescription = document.createElement('meta');
                metaDescription.setAttribute('name', 'description');
                document.head.appendChild(metaDescription);
            }

            metaDescription.setAttribute('content', payload.description);
        }
    });

    Livewire.on('reset-select2', () => {
        // clear select2 selections visually and notify Livewire
        $('#location-select').val(null).trigger('change');
        $('#category-select').val(null).trigger('change');
    });
});