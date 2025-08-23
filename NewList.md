I have a PHP-based file manager. Previously, all logic was forced into index.php, but now I want to separate functionality into modular files for better maintainability and performance.

🔧 Functional Goals
Refactor the project into the following structure:

index.php: only handles routing and UI shell

upload.php: handles file upload POSTs

list.php: returns folder contents as HTML or JSON (based on request)

create_folder.php: handles folder creation

delete.php: handles file/folder deletion

rename.php: handles renaming logic

All logic should respect a query parameter like ?folder=ID, and place files inside /uploads/{folderId}/. Create the directory if it doesn’t exist.

✅ Fix Current Issues
Fix the 404 error when clicking the Upload File button.

Make the upload system work via drag-and-drop or file picker with visual feedback.

Ensure files immediately appear in the current folder after upload.

Prevent page reloads — use AJAX or fetch for actions (upload, create, delete, rename).

🎨 Modernize the UI
Redesign the entire UI to be modern and professional:

Use a clean layout, rounded corners, shadows, and subtle animations.

Convert the folder/file list into a card-based layout with icons.

Replace dropdowns with toggle buttons or pills for file type filters and sort options.

Add dark mode support based on system preference.

Add a modern drag-and-drop upload zone.

Make it fully mobile responsive.

🧩 Additional Requirements
Each module (upload.php, list.php, etc.) must validate inputs securely.

Add success/failure toasts or alerts on all actions.

Show loading indicators during async operations.

Include keyboard accessibility and proper focus states.

Do not modify login.php or logout.php.

Return:

The updated index.php shell

New files: upload.php, list.php, create_folder.php, rename.php, delete.php

Updated HTML, CSS, and JavaScript to support all the above, modularized if possible

Clean comments and no placeholder filler content