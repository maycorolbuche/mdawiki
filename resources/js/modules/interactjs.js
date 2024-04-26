// https://www.jsdelivr.com/package/npm/interactjs
// https://interactjs.io/docs/
import interact from 'interactjs';

const initInteract = (fileInput, preview, previewContainer, callback = {}) => {
    fileInput.addEventListener('change', handleUpload);

    interact(preview)
        .draggable({
            listeners: {
                move: handleMove
            }
        })
        .resizable({
            edges: {
                left: true,
                right: true,
                bottom: true,
                top: true
            },
            listeners: {
                move: handleMove
            },
            modifiers: [
                interact.modifiers.restrictSize({
                    min: {
                        width: 200,
                        height: 200
                    },
                }),
            ],
            inertia: true,
        });

    function handleMove(event) {
        const target = event.target;
        let x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
        let y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

        x = Math.min(0, Math.max(-(preview.offsetWidth - previewContainer.offsetWidth), x));
        y = Math.min(0, Math.max(-(preview.offsetHeight - previewContainer.offsetHeight), y));

        target.style.transform = `translate(${x}px, ${y}px)`;
        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);

        updateBase64(preview.querySelector('img'), parseFloat(preview.style.width), parseFloat(preview.style.height), x, y);
    }

    function updateBase64(canvas, width, height, startX, startY) {
        const croppedCanvas = document.createElement('canvas');
        const ctx = croppedCanvas.getContext('2d');
        const croppedWidth = Math.min(width, 200);
        const croppedHeight = Math.min(height, 200);

        croppedCanvas.width = croppedWidth;
        croppedCanvas.height = croppedHeight;

        ctx.drawImage(canvas, Math.max(-startX, 0), Math.max(-startY, 0), croppedWidth, croppedHeight, 0, 0, croppedWidth, croppedHeight);

        setCallback(croppedCanvas.toDataURL('image/jpeg'));
    }

    function handleUpload() {
        const file = fileInput.files[0];
        const reader = new FileReader();

        reader.onload = function (e) {
            const img = new Image();
            img.src = e.target.result;

            img.onload = function () {
                let width = 0;
                let height = 0;

                if (img.width > img.height) {
                    height = 200;
                    width = height / img.height * img.width;
                } else {
                    width = 200;
                    height = width / img.width * img.height;
                }

                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = width;
                canvas.height = height;

                ctx.drawImage(img, 0, 0, width, height);

                const startX = (200 - width) /
                    2; // Calculating the initial position to center the image horizontally
                const startY = (200 - height) /
                    2; // Calculating the initial position to center the image vertically

                preview.style.width = `${width}px`;
                preview.style.height = `${height}px`;
                preview.style.transform = `translate(${startX}px, ${startY}px)`;
                preview.setAttribute('data-x', startX);
                preview.setAttribute('data-y', startY);

                updateBase64(canvas, width, height, startX, startY);

                preview.innerHTML =
                    `<img src="${canvas.toDataURL('image/jpeg')}" width="${width}" height="${height}">`;
            };
        };

        reader.readAsDataURL(file);
    }

    function setCallback(value) {
        if (typeof callback === 'function') {
            callback(value);
        }
    }
};

window.interact = initInteract;
