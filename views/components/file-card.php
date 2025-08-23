<?php
// Get file data from parent scope
$isFolder = empty($fileExt);
$dataType = $isFolder ? 'folder' : 'file';
$fileIconEmoji = $isFolder ? '📁' : getFileIcon($fileExt);
$fileIconTitle = getIconTitle($fileIconEmoji, $fileExt);
$itemClass = $isFolder ? 'folder-item' : 'file-item';
$link = $isFolder ? 
    "index.php?folder=" . urlencode($fileName) : 
    "file-viewer.php?file=" . urlencode($fileName);

// Set data attributes for drag and drop
$dataAttributes = "data-item-type=\"$dataType\" data-item-name=\"" . strtolower($fileName) . "\"";
if ($isFolder) {
    $dataAttributes .= " data-folder-name=\"$fileName\" data-drop-target=\"true\"";
} else {
    $dataAttributes .= " data-filename=\"$fileName\" data-file-ext=\"" . strtolower($fileExt) . "\"";
}

// Set draggable and drop target attributes
if (isset($isDraggable) && $isDraggable) {
    $dataAttributes .= " draggable=\"true\"";
}
if (isset($isDropTarget) && $isDropTarget) {
    $dataAttributes .= " data-drop-target=\"true\"";
}
?>
<li class="<?php echo $itemClass; ?>" <?php echo $dataAttributes; ?> tabindex="0">
    <div class="item-select-wrapper">
        <input type="checkbox" class="item-checkbox" aria-label="Select <?php echo htmlspecialchars($fileName); ?>">
    </div>
    <a href="<?php echo $link; ?>" class="file-link" data-selectable="true">
        <div class="file-icon-wrapper <?php echo $isFolder ? 'folder-icon-wrapper' : ''; ?>">
            <span class="file-icon" aria-hidden="true" title="<?php echo $fileIconTitle; ?>"><?php echo $fileIconEmoji; ?></span>
        </div>
        <div class="file-info">
            <div class="file-name" title="<?php echo $isFolder ? 'Double-click to rename, drag files here' : 'Double-click to rename, drag to move'; ?>"><?php echo htmlspecialchars($fileName); ?></div>
            <div class="file-type">
                <?php if ($isFolder): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M1 3.5A1.5 1.5 0 0 1 2.5 2h2.764c.958 0 1.76.56 2.311 1.184C7.985 3.648 8.48 4 9 4h4.5A1.5 1.5 0 0 1 15 5.5v.64c.57.265.94.876.856 1.546l-.64 5.124A2.5 2.5 0 0 1 12.733 15H3.266a2.5 2.5 0 0 1-2.481-2.19l-.64-5.124A1.99 1.99 0 0 1 1 6.14V3.5zM2 6h12v-.5a.5.5 0 0 0-.5-.5H9c-.964 0-1.71-.629-2.174-1.154C6.374 3.334 5.82 3 5.264 3H2.5a.5.5 0 0 0-.5.5V6z"/>
                </svg> Directory
                <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zM5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                    <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1z"/>
                </svg> <?php echo strtoupper($fileExt); ?>
                <?php endif; ?>
            </div>
            <?php if (!$isFolder && isset($fileSize)): ?>
            <div class="file-meta">
                <span title="File size"><?php echo $fileSize; ?></span>
                <?php if (isset($fileModTime)): ?>
                <span title="Last modified"><?php echo $fileModTime; ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </a>
</li>
