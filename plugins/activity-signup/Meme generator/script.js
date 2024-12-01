document.addEventListener('DOMContentLoaded', function () {
    var canvas = new fabric.Canvas('c');
    var textColorPicker = document.getElementById('textColorPicker');
    var fontSizeInput = document.getElementById('fontSizeInput');
    var addTextboxButton = document.getElementById('addTextbox');

    // Load and set background image
    fabric.Image.fromURL('image.jpg', function (img) {
        // Debugging output to check actual dimensions fetched
        console.log("Image dimensions: ", img.width, "x", img.height);
    
        // Set canvas dimensions to match image dimensions
        canvas.setWidth(img.width);
        canvas.setHeight(img.height);
        console.log("Canvas dimensions set to: ", canvas.width, "x", canvas.height);
    
        // Set the image as the background
        img.set({
            originX: 'left',
            originY: 'top',
            scaleX: 1,
            scaleY: 1
        });
        canvas.setBackgroundImage(img);
        canvas.renderAll(); // Ensure the canvas re-renders to show the background image
    });
    

    function addNewTextbox() {
        var textbox = new fabric.Textbox('New Text', {
            left: 50,
            top: 50,
            width: 200,
            fontSize: parseInt(fontSizeInput.value, 20),
            fill: textColorPicker.value,
            borderColor: 'red',
            cornerColor: 'green',
            cornerSize: 8,
            transparentCorners: false,
            editable: true
        });
        canvas.add(textbox);
        canvas.setActiveObject(textbox);
        handleSelection(); // Show controls
    }

    // Initialize with one text box
    addNewTextbox();

    // Add button event listener
    addTextboxButton.onclick = addNewTextbox;


    // Function to handle selection of objects
    function handleSelection(clear) {
        if (clear) {
            textColorPicker.style.display = 'none';
            fontSizeInput.style.display = 'none';
        } else {
            textColorPicker.style.display = 'block';
            fontSizeInput.style.display = 'block';
        }
    }

    // Hide controls when no object is selected
    canvas.on('selection:cleared', function () {
        handleSelection(true);
    });

    // Show controls when an object is selected
    canvas.on('selection:created', function (e) {
        handleSelection();
        var activeObject = e.target;
        if (activeObject && activeObject.type === 'textbox') {
            textColorPicker.value = activeObject.fill;
            fontSizeInput.value = activeObject.fontSize;
        }
    });

    canvas.on('selection:updated', function (e) {
        handleSelection();
        var activeObject = e.target;
        if (activeObject && activeObject.type === 'textbox') {
            textColorPicker.value = activeObject.fill;
            fontSizeInput.value = activeObject.fontSize;
        }
    });

    // Update the text color when user changes the color picker value
    textColorPicker.addEventListener('change', function () {
        var activeObject = canvas.getActiveObject();
        if (activeObject && activeObject.type === 'textbox') {
            activeObject.set({ fill: textColorPicker.value });
            canvas.renderAll();
        }
    });

    // Update the font size when user changes the font size value
    fontSizeInput.addEventListener('input', function () {
        var activeObject = canvas.getActiveObject();
        if (activeObject && activeObject.type === 'textbox') {
            activeObject.set({ fontSize: parseInt(fontSizeInput.value, 10) });
            canvas.renderAll();
        }
    });
});
