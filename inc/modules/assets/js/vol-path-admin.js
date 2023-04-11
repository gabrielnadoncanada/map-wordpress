document.getElementById('add-point').addEventListener('click', function () {
    let point = document.createElement('div');
    point.className = 'point';
    point.innerHTML = `
                <h5>Point</h5>
                <label>Lat: <input type="text" name="point_lat[]" /></label>
                <label>Lng: <input type="text" name="point_lng[]" /></label>
                <label>Enable Extra Settings: <input type="checkbox" name="enable_extra_settings[]" /></label>
                <div class="extra-settings" style="display: none;">
                    <label>Title: <input type="text" name="point_title[]" /></label>
                    <label>Description: <input type="text" name="point_description[]" /></label>
                    <h6>Gallery Photos</h6>
                    <div class="gallery-photos"></div>
                    <button type="button" class="add-gallery-photo">Add Photo</button>
                </div>
            `;

    document.querySelector('.points-container').appendChild(point);

    point.querySelector('.add-gallery-photo').addEventListener('click', function () {
        let galleryPhoto = document.createElement('div');
        galleryPhoto.innerHTML = '<label>URL: <input type="text" name="gallery_photos[]" /></label><button type="button" class="upload-gallery-photo">Upload</button>';
        point.querySelector('.gallery-photos').appendChild(galleryPhoto);

        galleryPhoto.querySelector('.upload-gallery-photo').addEventListener('click', function () {
            let inputField = galleryPhoto.querySelector('input[name="gallery_photos[]"]');

            // Open the WordPress media library
            let frame = wp.media({
                title: 'Select or Upload Media',
                button: {
                    text: 'Use this media'
                },
                multiple: false
            });

            frame.on('select', function () {
                // Get the selected image
                let attachment = frame.state().get('selection').first().toJSON();

                // Set the input field value to the image URL
                inputField.value = attachment.url;
            });

            frame.open();
        });
    });

    point.querySelector('input[name="enable_extra_settings[]"]').addEventListener('change', function () {
        let extraSettings = point.querySelector('.extra-settings');
        if (this.checked) {
            extraSettings.style.display = 'block';
        } else {
            extraSettings.style.display = 'none';
        }
    });
});
